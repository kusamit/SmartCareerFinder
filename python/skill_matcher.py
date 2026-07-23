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
    # Data Science / Analytics / ML
    "machine learning", "data science", "data analysis", "pandas", "numpy",
    "tensorflow", "pytorch", "nlp", "deep learning", "scikit-learn", "keras",
    "tableau", "power bi", "excel", "sheets", "matplotlib", "seaborn",
    # DevOps & Tools
    "devops", "kubernetes", "ci/cd", "jenkins", "ansible", "terraform",
    "vagrant", "nginx", "apache",
    # Design, Marketing & Others
    "digital marketing", "marketing", "seo", "sem", "social media",
    "content writing", "photoshop", "illustrator", "figma", "ui/ux", "ui",
    "ux", "graphic design", "wordpress", "github", "gitlab",
    "communication skills", "problem solving", "statistics",
    "attention to detail"
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
        """Standardize skill aliases to canonical names."""
        if ks == "apis":
            return "api"
        if ks in ("github", "gitlab"):
            return "git"
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

            for ks in KNOWN_SKILLS:
                if match_term(ks, seg_clean):
                    std_name = SkillExperienceMatcher._normalize_skill_name(ks)
                    parsed[std_name] = max(parsed.get(std_name, 0), years)

        return parsed

    @staticmethod
    def calculate_match(user_text, job_text):
       #calculate match between user and job text based on skills and experience
        seeker_skills = SkillExperienceMatcher.parse_skill_experiences(user_text)
        job_skills    = SkillExperienceMatcher.parse_skill_requirements(job_text)

        if not job_skills:
            return 1.0

        overall_years = TextPreprocessor.extract_years(user_text)

        matched_score = 0.0
        total_weight  = 0.0

        for skill, req_years in job_skills.items():
            weight       = 1.0
            total_weight += weight

            # Find seeker's matching skill (exact or boundary match)
            matched_seeker_skill = None
            for ss in seeker_skills.keys():
                if ss == skill or match_term(ss, skill) or match_term(skill, ss):
                    matched_seeker_skill = ss
                    break

            if matched_seeker_skill:
                seeker_years = seeker_skills[matched_seeker_skill]
                # Use overall experience as fallback if skill-specific years unknown
                if seeker_years == 0 and overall_years > 0:
                    seeker_years = overall_years

                if req_years == 0:
                    matched_score += weight                                  # No year requirement
                elif seeker_years >= req_years:
                    matched_score += weight                                  # Full score
                elif seeker_years > 0:
                    matched_score += weight * (seeker_years / req_years)    # Partial score
                else:
                    matched_score += weight * 0.5                           # Skill matched, no years
            # Else: skill not found → score += 0.0

        return matched_score / total_weight if total_weight > 0 else 1.0
