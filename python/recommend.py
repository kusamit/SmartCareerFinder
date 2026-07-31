import os
import sys
import json
import math
import re
from collections import Counter

# Set standard output encoding to UTF-8 (required for Windows shell_exec)
sys.stdout.reconfigure(encoding='utf-8')

# Categories definition
CATEGORIES = {
    "Frontend Development": [
        "html5", "css3", "html", "css", "javascript", "javascript (es6+)", "es6", 
        "react.js", "react", "vue", "vue.js", "angular", "tailwind", "tailwind css", 
        "bootstrap", "typescript", "basic typescript", "nextjs", "next.js", 
        "basic next.js", "sass", "jquery", "redux", "redux toolkit", 
        "redux toolkit / zustand", "zustand", "material ui", 
        "material ui / shadcn ui", "shadcn ui", "react query", 
        "react query / tanstack query", "tanstack query"
    ],
    "Backend Development": [
        "php", "laravel", "laravel framework", "mysql", "postgresql", "node.js", 
        "node", "express.js", "express", "mongodb", "restful api", "rest api", 
        "restful apis", "rest apis", "api", "apis", "api development", 
        "database", "sql", "nosql", "redis", "c#", "net core", "java", "spring boot", 
        "python backend", "django", "flask", "fastapi"
    ],
    "DevOps & Infrastructure": [
        "docker", "kubernetes", "git", "github", "gitlab", "ci/cd", "ci / cd", 
        "jenkins", "aws", "amazon web services", "gcp", "google cloud", "azure", 
        "linux", "bash", "nginx", "apache", "terraform", "ansible"
    ],
    "Data Science & Machine Learning": [
        "python", "pandas", "numpy", "matplotlib", "seaborn", "scikit-learn", 
        "tensorflow", "pytorch", "keras", "machine learning", "data science", 
        "data analysis", "nlp", "statistics", "tableau", "power bi", "excel", "sheets"
    ],
    "Design & UX": [
        "ui/ux", "ui", "ux", "ui design", "ux design", "ui/ux design", "figma", 
        "adobe xd", "sketch", "invision", "zeplin", "wireframing", "prototyping", 
        "user research", "usability testing", "interaction design", "design thinking", 
        "wordpress", "webflow"
    ],
    "Graphics Design": [
        "graphic design", "graphics design", "photoshop", "adobe photoshop", 
        "illustrator", "adobe illustrator", "indesign", "adobe indesign", "coreldraw", 
        "canva", "logo design", "brand identity", "branding", "typography", 
        "color theory", "print design", "banner design", "poster design", 
        "flyer design", "packaging design", "motion graphics", "after effects", 
        "adobe after effects"
    ],
    "Video Editing & Production": [
        "video editing", "video production", "videography", "premiere pro", 
        "adobe premiere", "adobe premiere pro", "final cut pro", "davinci resolve", 
        "capcut", "filmora", "color grading", "color correction", "video color grading", 
        "cinematography", "storytelling", "youtube content creation", "reels editing", 
        "short video editing", "animation", "2d animation", "3d animation", "blender"
    ],
    "Digital Marketing": [
        "digital marketing", "marketing", "online marketing", "growth hacking", 
        "performance marketing", "affiliate marketing", "influencer marketing", 
        "content marketing", "content strategy", "content creation", "content writing", 
        "copywriting", "seo", "search engine optimization", "on-page seo", "off-page seo", 
        "technical seo", "local seo", "sem", "search engine marketing", "google ads", 
        "google adwords", "ppc", "pay-per-click", "facebook ads", "meta ads", 
        "instagram ads", "tiktok ads", "social media", "social media marketing", 
        "social media management", "community management", "email marketing", 
        "email campaigns", "newsletter", "mailchimp", "hubspot", "marketing automation", 
        "crm", "google analytics", "analytics", "conversion rate optimization", "cro", 
        "a/b testing", "funnel marketing", "lead generation", "brand management", 
        "public relations", "pr"
    ],
    "Agile & Project Management": [
        "agile/scrum methodology", "agile", "scrum", "agile/scrum", 
        "project management", "communication skills", "problem solving", 
        "attention to detail"
    ]
}

COURSES = {
    "Frontend Development": "Complete Front-end Development Course",
    "Backend Development": "Advanced Backend Engineering Path",
    "DevOps & Infrastructure": "DevOps, Git & CI/CD Masterclass",
    "Data Science & Machine Learning": "Data Science & AI/ML Bootcamp",
    "Design & UX": "UI/UX Design & Prototyping Masterclass",
    "Graphics Design": "Professional Graphics Design with Adobe Suite & Canva",
    "Video Editing & Production": "Video Editing & Content Production Bootcamp",
    "Digital Marketing": "Complete Digital Marketing & Growth Strategy Course",
    "Agile & Project Management": "Agile, Scrum & Leadership Certification"
}

# Resolve STORAGE_DIR to load IDF weights
STORAGE_DIR = os.path.join(
    os.path.dirname(os.path.dirname(os.path.abspath(__file__))),
    "storage", "app", "vector_db"
)
vocab_file = os.path.join(STORAGE_DIR, "vocab.json")

idf_weights = {}
if os.path.exists(vocab_file):
    try:
        with open(vocab_file, "r", encoding="utf-8") as f:
            data = json.load(f)
            idf_weights = data.get("idf", {})
    except Exception:
        pass

def get_char_grams(text, n=3):
    text = text.lower().strip()
    text = re.sub(r'[^a-z0-9\s]', '', text)
    if len(text) < n:
        return [text]
    return [text[i:i+n] for i in range(len(text) - n + 1)]

def get_word_freq(text):
    text = text.lower().strip()
    text = re.sub(r'[^a-z0-9\s]', ' ', text)
    words = text.split()
    return Counter(words)

def dict_cosine_similarity(dict_a, dict_b, idf_map=None):
    dot_product = 0.0
    for k, v in dict_a.items():
        if k in dict_b:
            weight = idf_map.get(k, 1.0) if idf_map else 1.0
            dot_product += (v * weight) * (dict_b[k] * weight)
            
    mag_a = math.sqrt(sum((v * (idf_map.get(k, 1.0) if idf_map else 1.0)) ** 2 for k, v in dict_a.items()))
    mag_b = math.sqrt(sum((v * (idf_map.get(k, 1.0) if idf_map else 1.0)) ** 2 for k, v in dict_b.items()))
    
    if mag_a < 1e-9 or mag_b < 1e-9:
        return 0.0
    return dot_product / (mag_a * mag_b)

def compute_similarity(skill, anchor):
    # Word-level TF-IDF similarity
    word_freq_s = get_word_freq(skill)
    word_freq_a = get_word_freq(anchor)
    word_sim = dict_cosine_similarity(word_freq_s, word_freq_a, idf_weights)
    
    # Char-level 3-gram similarity
    char_grams_s = Counter(get_char_grams(skill))
    char_grams_a = Counter(get_char_grams(anchor))
    char_sim = dict_cosine_similarity(char_grams_s, char_grams_a)
    
    # Hybrid score: word overlap (40%) + char sequence similarity (60%)
    return 0.4 * word_sim + 0.6 * char_sim

def main():
    if len(sys.argv) < 2:
        print(json.dumps([]))
        return
        
    skills_arg = sys.argv[1]
    if skills_arg.strip().startswith('[') and skills_arg.strip().endswith(']'):
        try:
            unmatched_skills = json.loads(skills_arg)
        except Exception:
            unmatched_skills = sys.argv[1:]
    else:
        unmatched_skills = sys.argv[1:]
        
    categorized = {}
    
    for skill in unmatched_skills:
        best_cat = "Other Professional Skills"
        best_score = 0.0
        
        for cat_name, anchors in CATEGORIES.items():
            for anchor in anchors:
                score = compute_similarity(skill, anchor)
                if score > best_score:
                    best_score = score
                    best_cat = cat_name
                    
        # Similarity threshold: if best match is extremely weak, classify as general skill
        if best_score < 0.15:
            best_cat = "Other Professional Skills"
            
        if best_cat not in categorized:
            categorized[best_cat] = []
        categorized[best_cat].append(skill)
        
    result = []
    for cat_name, skills in categorized.items():
        skills_str = ", ".join(skills)
        course = COURSES.get(cat_name, f"Specialized Professional Skill Building")
        course = f"{course} (covers: {skills_str})"
            
        result.append({
            "category": cat_name,
            "skills": skills,
            "course": course
        })
        
    print(json.dumps(result))

if __name__ == "__main__":
    main()
