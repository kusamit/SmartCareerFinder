"""
Comprehensive Matching Pipeline Test
Tests: skill extraction, experience parsing, match scoring, FAISS search
"""
import sys
import os
sys.stdout.reconfigure(encoding='utf-8')

# Add the script directory to path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from match import (
    SkillExperienceMatcher, match_term, TextPreprocessor,
    VectorDatabase, TFIDFVectorizer, DenseEmbeddingGenerator
)

PASS = "\u2705"
FAIL = "\u274c"
WARN = "\u26a0\ufe0f"

def check(label, condition, expected=None, actual=None):
    icon = PASS if condition else FAIL
    print(f"  {icon} {label}")
    if not condition and expected is not None:
        print(f"       Expected : {expected}")
        print(f"       Got      : {actual}")
    return condition

results = []

print("\n1. BOUNDARY-SAFE match_term()")

cases = [
    # (term, text, expected, label)
    ("git",        "I use git for version control",       True,  "git in 'git for version control'"),
    ("git",        "My github.com profile",               False, "git MUST NOT match github.com"),
    ("sql",        "I know sql databases",                True,  "sql in 'sql databases'"),
    ("sql",        "Expert in postgresql and mysql",      False, "sql MUST NOT match postgresql or mysql alone"),
    ("python",     "python 3 years experience",           True,  "python in experience text"),
    ("react",      "React.js developer",                  True,  "react in 'React.js developer'"),
    ("node",       "node.js backend",                     True,  "node in 'node.js backend'"),
    ("api",        "REST API development",                 True,  "api in 'REST API development'"),
    ("ui",         "ui/ux designer",                      True,  "ui in 'ui/ux designer'"),
]

for term, text, expected, label in cases:
    result = match_term(term, text)
    ok = check(label, result == expected, expected, result)
    results.append(ok)

print("\n2. SKILL EXPERIENCE PARSING")

# Seeker profile: individual skill experience  
seeker_text = "PHP 2 years, Python 3 years, React 1 year, JavaScript 4 years"
parsed = SkillExperienceMatcher.parse_skill_experiences(seeker_text)
print(f"  Input : '{seeker_text}'")
print(f"  Parsed: {parsed}")

results.append(check("PHP = 2 years", parsed.get("php", 0) == 2, 2, parsed.get("php", 0)))
results.append(check("Python = 3 years", parsed.get("python", 0) == 3, 3, parsed.get("python", 0)))
results.append(check("React = 1 year", parsed.get("react", 0) == 1, 1, parsed.get("react", 0)))
results.append(check("JavaScript = 4 years", parsed.get("javascript", 0) == 4, 4, parsed.get("javascript", 0)))

# Seeker with HTML (from Quill editor)
html_text = "<ul><li>Python - 3 years</li><li>Docker 2 years</li><li>React.js 1 year</li></ul>"
parsed_html = SkillExperienceMatcher.parse_skill_experiences(html_text)
print(f"\n  Input (HTML): '{html_text}'")
print(f"  Parsed: {parsed_html}")
results.append(check("Python from HTML = 3", parsed_html.get("python", 0) == 3, 3, parsed_html.get("python", 0)))
results.append(check("Docker from HTML = 2", parsed_html.get("docker", 0) == 2, 2, parsed_html.get("docker", 0)))

print("\n3. JOB REQUIREMENTS PARSING")

job_req = "Requirements: Python 2+ years, React 1 year experience, Docker knowledge required"
parsed_req = SkillExperienceMatcher.parse_skill_requirements(job_req)
print(f"  Input : '{job_req}'")
print(f"  Parsed: {parsed_req}")
results.append(check("Job requires Python >= 2 yrs", parsed_req.get("python", 0) >= 2, ">=2", parsed_req.get("python", 0)))
results.append(check("Job requires React >= 1 yr", parsed_req.get("react", 0) >= 1, ">=1", parsed_req.get("react", 0)))

print("\n4. CALCULATE_MATCH SCENARIOS")

# Test A: Perfect match
user_a = "Python 3 years, React 2 years, Docker 1 year"
job_a  = "Python 2 years experience required, React 1 year, Docker knowledge"
score_a = SkillExperienceMatcher.calculate_match(user_a, job_a)
print(f"  A) Perfect match - user exceeds all requirements")
print(f"     User: {user_a}")
print(f"     Job : {job_a}")
print(f"     Score: {score_a:.2f} (expected ~1.0)")
results.append(check("Perfect match >= 0.8", score_a >= 0.8, ">=0.8", round(score_a, 2)))

# Test B: Zero match - completely different skills
user_b = "PHP 5 years, Laravel 4 years, MySQL 3 years"
job_b  = "Python 3 years, TensorFlow 2 years, Machine Learning experience"
score_b = SkillExperienceMatcher.calculate_match(user_b, job_b)
print(f"\n  B) Zero/poor match - completely different skills")
print(f"     User: {user_b}")
print(f"     Job : {job_b}")
print(f"     Score: {score_b:.2f} (expected < 0.3)")
results.append(check("Zero match < 0.3", score_b < 0.3, "<0.3", round(score_b, 2)))

# Test C: Partial match
user_c = "Python 2 years, PHP 3 years, JavaScript 2 years"
job_c  = "Python 2 years, React 2 years, Node.js 1 year"
score_c = SkillExperienceMatcher.calculate_match(user_c, job_c)
print(f"\n  C) Partial match (1 of 3 skills match clearly)")
print(f"     User: {user_c}")
print(f"     Job : {job_c}")
print(f"     Score: {score_c:.2f} (expected 0.25–0.55)")
results.append(check("Partial match between 0.2 and 0.6", 0.2 <= score_c <= 0.6, "0.2–0.6", round(score_c, 2)))

# Test D: Experience under-meet
user_d = "Python 1 year"
job_d  = "Python 3 years experience required"
score_d = SkillExperienceMatcher.calculate_match(user_d, job_d)
print(f"\n  D) Experience under-meet (1yr vs 3yr required)")
print(f"     User: {user_d}")
print(f"     Job : {job_d}")
print(f"     Score: {score_d:.2f} (expected ~0.33)")
results.append(check("Under-exp partial credit ~0.33", 0.2 <= score_d <= 0.45, "~0.33", round(score_d, 2)))

# Test E: Critical test - Ram Prasad scenario (zero Python match)
user_e = "PHP 5 years, CSS 3 years, HTML 4 years, Bootstrap 2 years"
job_e  = "Python 3 years, Pandas 2 years, Machine Learning, Data Science"
score_e = SkillExperienceMatcher.calculate_match(user_e, job_e)
print(f"\n  E) Ram Prasad scenario: PHP developer vs Python Data Scientist job")
print(f"     User: {user_e}")
print(f"     Job : {job_e}")
print(f"     Score: {score_e:.2f} (MUST be < 0.2, previously was broken ~0.57)")
results.append(check("Ram Prasad zero match < 0.2", score_e < 0.2, "<0.2", round(score_e, 2)))

print("\n5. VECTOR DATABASE")

db = VectorDatabase()
print(f"  Users in DB : {len(db.users)}")
print(f"  Jobs in DB  : {len(db.jobs)}")
print(f"  Vocab loaded: {'yes' if db.vocab_data else 'no'}")

results.append(check("DB has users", len(db.users) > 0, ">0", len(db.users)))
results.append(check("DB has jobs", len(db.jobs) > 0, ">0", len(db.jobs)))
results.append(check("Vocab is loaded", bool(db.vocab_data), True, bool(db.vocab_data)))

print("\n6. FAISS SEARCH SANITY")

if db.users and db.jobs:
    # Pick first user and search for jobs
    sample_user_id = list(db.users.keys())[0]
    q_vec = db.get_user_vector(sample_user_id)
    _, job_index = db.load_index()
    job_vectors = {jid: jdata["vector"] for jid, jdata in db.jobs.items()}
    
    if q_vec and job_vectors:
        raw_results = job_index.search(q_vec, job_vectors, n_probe=10)
        print(f"  User ID: {sample_user_id}")
        print(f"  FAISS returned {len(raw_results)} raw results")
        
        # Score them
        user_text = db.users.get(str(sample_user_id), {}).get("text", "")
        scored_results = []
        for jid, sim in raw_results:
            job_data = db.jobs.get(str(jid), {})
            job_text = job_data.get("text", "")
            base_score = max(0, min(100, int(sim * 100)))
            skills_pct = int(SkillExperienceMatcher.calculate_match(user_text, job_text) * 100)
            boosted_base = base_score + (100 - base_score) * (skills_pct / 100.0)
            final = int(boosted_base * 0.40 + skills_pct * 0.60)
            scored_results.append((jid, base_score, skills_pct, final))
        
        scored_results.sort(key=lambda x: x[3], reverse=True)
        print(f"\n  {'JobID':<8} {'Base':>6} {'Skills':>8} {'Final':>7}")
        print(f"  {'-'*35}")
        for jid, base, skills, final in scored_results:
            job_title = db.jobs.get(str(jid), {}).get("text", "")[:40]
            print(f"  {jid:<8} {base:>5}%  {skills:>6}%  {final:>6}%   {job_title}...")
        
        results.append(check("FAISS search returned results", len(raw_results) > 0))
        
        # Check: scores are between 0 and 100
        all_valid = all(0 <= r[3] <= 100 for r in scored_results)
        results.append(check("All scores 0–100 range", all_valid))
    else:
        print(f"  {WARN} Could not get vectors for search")
else:
    print(f"  {WARN} Database is empty, skipping search test")

print("\nSUMMARY")
total = len(results)
passed = sum(results)
failed = total - passed
print(f"  Total  : {total}")
print(f"  {PASS} Passed : {passed}")
print(f"  {FAIL} Failed : {failed}")

if failed == 0:
    print("\n  ✅ ALL TESTS PASSED — Pipeline is clean and correct!")
else:
    print(f"\n  ❌ {failed} TEST(S) FAILED — Review issues above.")

sys.exit(0 if failed == 0 else 1)
