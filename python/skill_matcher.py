import re

from config import STOPWORDS
from tf_idf import TextPreprocessor


# Shared known-skills list

KNOWN_SKILLS = sorted(list(set([
    # Core Tech Stack
    "python", "php", "javascript", "react", "laravel", "sql", "css", "html",
    "docker", "django", "postgresql", "node", "java", "c#", "c++", "ruby",
    "rails", "git", "bash", "linux", "aws", "gcp", "azure", "tailwind",
    "rest", "api", "apis", "vue", "angular", "typescript", "nextjs", "next.js",
    "mongodb", "mysql", "nosql", "sass", "bootstrap", "jquery", "graphql",
    "html5", "css3", "react.js", "vue.js", "node.js", "express", "express.js",
    "flask", "fastapi", "spring", "spring boot", "redis", "firebase",
    # Data Science / Analytics / ML
    "machine learning", "data science", "data analysis", "pandas", "numpy",
    "tensorflow", "pytorch", "nlp", "deep learning", "scikit-learn", "keras",
    "tableau", "power bi", "excel", "sheets", "matplotlib", "seaborn",
    "statistics",
    # DevOps & Tools
    "devops", "kubernetes", "ci/cd", "jenkins", "ansible", "terraform",
    "vagrant", "nginx", "apache",
    # UI/UX & Design
    "figma", "adobe xd", "sketch", "invision", "zeplin",
    "ui design", "ux design", "ui/ux", "ui/ux design", "ui", "ux",
    "wireframing", "prototyping", "user research", "ux research", "ui research",
    "usability testing", "interaction design", "design thinking",
    "information architecture", "user journey mapping", "personas",
    "design systems", "responsive design", "mobile app design",
    "website design", "dashboard design", "user experience design",
    "web design", "app design", "product design",
    # Graphics / Creative
    "photoshop", "illustrator", "graphic design", "adobe illustrator",
    "adobe photoshop", "canva", "coreldraw", "indesign",
    # Digital Marketing
    "digital marketing", "marketing", "seo", "sem", "social media",
    "content writing", "content creation", "email marketing",
    "google ads", "social media marketing",
    # Other
    "wordpress", "github", "gitlab",
    "communication skills", "problem solving",
    "attention to detail", "teamwork", "leadership",
    "agile", "scrum", "agile/scrum", "project management",
    "jira", "confluence",
])), key=len, reverse=True)  # Longest-first to prevent substring false matches


# Word Boundary Matcher

def match_term(term, text):

    escaped = re.escape(term)
    pattern = (
        r'(?:^|[\s,.;:()/\-\[\]{}*])'
        + escaped +
        r'(?:$|[\s,.;:()/\-\[\]{}*])'
    )
    return bool(re.search(pattern, text, re.IGNORECASE))


# Skill Experience Matcher

class SkillExperienceMatcher:

    @staticmethod
    def _split_to_segments(text):
        """Split HTML/rich text into individual skill segments."""
        text_clean = re.sub(r'<[^>]*>', '\n', text)
        segments = []
        for line in text_clean.split('\n'):
            for part in re.split(r'[;,]', line):
                part = part.strip()
                if part:
                    segments.append(part)
        return segments

    @staticmethod
    def _normalize_skill_name(ks):
        """Standardize skill aliases and morphological variations to canonical names."""
        ks = ks.lower().strip()
        if ks in ("apis", "rest api", "rest apis", "restful api"):
            return "api"
        if ks in ("github", "gitlab"):
            return "git"
        if ks in ("graphics designer", "graphics designing", "graphic designer", "graphic designing", "graphics design"):
            return "graphic design"
        if ks in ("ui research", "ux research", "ui/ux research"):
            return "user research"
        if ks in ("ui design", "ux design", "ui/ux", "ui/ux design", "user interface design", "user experience design"):
            return "ui/ux design"
        return ks

    @staticmethod
    def parse_skill_experiences(text):

        if not text:
            return {}

        segments = SkillExperienceMatcher._split_to_segments(text)
        skill_exps = {}

        for seg in segments:
            seg_lower = seg.lower()

            # Extract year value from segment
            year_match = re.search(r'(\d+)\+?\s*(?:years?|yrs?|y\b)', seg_lower)
            if year_match:
                years = int(year_match.group(1))
                seg_clean = seg_lower[:year_match.start()] + " " + seg_lower[year_match.end():]
            else:
                years = 0
                seg_clean = seg_lower

            # Remove common noise text
            seg_clean = re.sub(r'professional skills:?', '', seg_clean)
            seg_clean = re.sub(r'[•●▪\-*:]', ' ', seg_clean).strip()

            found_any = False
            for ks in KNOWN_SKILLS:
                if match_term(ks, seg_clean):
                    std_name = SkillExperienceMatcher._normalize_skill_name(ks)
                    skill_exps[std_name] = max(skill_exps.get(std_name, 0), years)
                    found_any = True

            # Fallback: if years found but no skill matched, store raw token
            if not found_any and years > 0:
                for p in re.split(r'\band\b|\bor\b|&', seg_clean):
                    p = p.strip()
                    if p and len(p) > 1 and p not in STOPWORDS:
                        skill_exps[p] = max(skill_exps.get(p, 0), years)

        return skill_exps

    @staticmethod
    def parse_skill_requirements(text):
        """Parse required skills from job text.

        Only maps a segment to a KNOWN_SKILL if the remaining words in the
        segment (after removing the skill name) are NOT qualifier/inhibitor
        words.  This prevents 'API Testing' from generating a requirement
        for the skill 'api', and 'Database Support' from generating
        'database'.  Legitimate single-word entries like 'PHP', 'MySQL',
        'Git' and multi-word exact skills like 'REST API' are unaffected.
        """
        # Words that, when appended to a skill name, indicate the segment
        # describes a TASK/DOMAIN rather than a required TECHNOLOGY SKILL.
        SKILL_INHIBITING_WORDS = {
            'testing', 'support', 'basics', 'basic',
            'management', 'administration', 'analysis',
            'service', 'services',
        }

        if not text:
            return {}

        segments = SkillExperienceMatcher._split_to_segments(text)

        # Global year fallback for the whole job posting
        global_years = TextPreprocessor.extract_years(text)

        parsed = {}
        for seg in segments:
            seg_lower = seg.lower()

            year_match = re.search(r'(\d+)\+?\s*(?:years?|yrs?|y\b)', seg_lower)
            if year_match:
                years = int(year_match.group(1))
                seg_clean = seg_lower[:year_match.start()] + " " + seg_lower[year_match.end():]
            else:
                years = 0
                seg_clean = seg_lower

            seg_clean = re.sub(r'[•●▪\-*:]', ' ', seg_clean).strip()

            seg_words = set(seg_clean.split())

            for ks in KNOWN_SKILLS:
                if match_term(ks, seg_clean):
                    # Check whether the extra words (beyond the skill name)
                    # are qualifier/inhibitor words.
                    ks_words = set(ks.split())
                    extra_words = seg_words - ks_words
                    if extra_words & SKILL_INHIBITING_WORDS:
                        # e.g. "api testing" -> extra={"testing"} inhibited
                        continue
                    std_name = SkillExperienceMatcher._normalize_skill_name(ks)
                    parsed[std_name] = max(parsed.get(std_name, 0), years)

        return parsed

    @staticmethod
    def _extract_raw_tokens(text):
        """
        Extract skill-like tokens from raw text by splitting on comma / newline / semicolon.
        Strips HTML, bullet characters and noise. Keeps only phrases that are
        1-6 words long (2-60 chars) — long sentences are discarded as non-skill text.
        """
        text = re.sub(r'<[^>]*>', ' ', text)
        text = re.sub(r'[\u2022\u25cf\u25aa]', '\n', text)
        tokens = []
        for part in re.split(r'[,\n;]+', text):
            part = re.sub(r'^[\s\-*:•●▪\d.]+', '', part)
            part = re.sub(r'[\-*:]+$', '', part)
            part = re.sub(r'\s+', ' ', part).strip().lower()
            word_count = len(part.split())
            if part and 2 <= len(part) <= 60 and 1 <= word_count <= 6:
                tokens.append(part)
        return tokens

    @staticmethod
    def _raw_skill_match_ratio(user_text, job_text):
        """
        Compute the actual fraction of job-required skill tokens the seeker has,
        using strict phrase matching (no KNOWN_SKILLS dependency).
        Returns matched / total so a 1/13 match correctly yields ~0.077 not 0.5.
        """
        job_tokens = SkillExperienceMatcher._extract_raw_tokens(job_text)

        TITLE_ENDINGS = {
            'engineer', 'developer', 'designer', 'executive', 'manager',
            'analyst', 'specialist', 'technician', 'assistant', 'intern',
            'administrator', 'officer', 'coordinator', 'consultant', 'lead',
        }
        while job_tokens:
            first = job_tokens[0].split()
            # Only strip long job title headers (3+ words ending in role title, e.g. "Senior Backend Developer")
            if len(first) >= 3 and first[-1] in TITLE_ENDINGS:
                job_tokens = job_tokens[1:]
            else:
                break

        if not job_tokens:
            return 0.0

        user_tokens = list(SkillExperienceMatcher._extract_raw_tokens(user_text))

        def _stem_phrase(phrase):
            words = phrase.split()
            return ' '.join(re.sub(r'(?:ings?|ers?|es|s)$', '', w) for w in words)

        matched = 0
        for jt in job_tokens:
            found = False
            jt_stem = _stem_phrase(jt)
            for ut in user_tokens:
                ut_stem = _stem_phrase(ut)
                if jt == ut or jt_stem == ut_stem:
                    found = True
                elif jt in ut or jt_stem in ut_stem:
                    found = True
                elif (ut in jt or ut_stem in jt_stem) and len(ut) >= 5:
                    found = True
                if found:
                    break
            if found:
                matched += 1

        return matched / len(job_tokens)

    @staticmethod
    def calculate_match(user_text, job_text):
        """Calculate skill match score (0.0-1.0) between seeker and job text.

        Primary path  : KNOWN_SKILLS-based weighted matching (handles skills
                        with explicit experience-year requirements).
        Fallback path : Raw token ratio — used when the job uses non-standard
                        skill names not in KNOWN_SKILLS (e.g. 'Software
                        Implementation', 'Client Support'). Returns the actual
                        matched / total ratio instead of the old neutral 0.5,
                        so a 1/13 match correctly yields ~0.077 not 0.5.
        """
        seeker_skills = SkillExperienceMatcher.parse_skill_experiences(user_text)
        job_skills    = SkillExperienceMatcher.parse_skill_requirements(job_text)

        if not job_skills:
            # No KNOWN_SKILLS found in job text.
            # Use raw token ratio so the score reflects actual skill overlap.
            return SkillExperienceMatcher._raw_skill_match_ratio(user_text, job_text)

        overall_years = TextPreprocessor.extract_years(user_text)

        matched_score = 0.0
        total_weight  = 0.0

        for skill, req_years in job_skills.items():
            weight       = 1.0
            total_weight += weight

            matched_seeker_skill = None
            for ss in seeker_skills.keys():
                if ss == skill or match_term(ss, skill) or match_term(skill, ss):
                    matched_seeker_skill = ss
                    break

            if matched_seeker_skill:
                seeker_years = seeker_skills[matched_seeker_skill]
                if seeker_years == 0 and overall_years > 0:
                    seeker_years = overall_years

                if req_years == 0:
                    matched_score += weight
                elif seeker_years >= req_years:
                    matched_score += weight
                elif seeker_years > 0:
                    matched_score += weight * (seeker_years / req_years)
                else:
                    matched_score += weight * 0.5
            # Else: skill not found -> score += 0.0

        # Calculate raw token ratio and count
        raw_ratio = SkillExperienceMatcher._raw_skill_match_ratio(user_text, job_text)
        job_tokens = SkillExperienceMatcher._extract_raw_tokens(job_text)
        raw_count = len(job_tokens)

        if not job_skills or total_weight == 0:
            return raw_ratio

        known_ratio = matched_score / total_weight

        # Weighted blend of KNOWN_SKILLS ratio and raw token ratio
        if raw_count > 0:
            return (known_ratio * total_weight + raw_ratio * raw_count) / (total_weight + raw_count)

        return known_ratio

