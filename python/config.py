"""
config.py
=========
Shared constants and configuration used across all NLP modules.

Contains
--------
STORAGE_DIR  : Absolute path to the vector database JSON files
STOPWORDS    : English stopword set used by TextPreprocessor
CATEGORIES   : 10-dimensional anchor word mapping used by DenseEmbeddingGenerator

Dependencies: os, sys (stdlib)
"""

import os
import sys

# Storage path
# Resolves to: <project_root>/storage/app/vector_db/
STORAGE_DIR = os.path.join(
    os.path.dirname(os.path.dirname(os.path.abspath(__file__))),
    "storage", "app", "vector_db"
)

# Stopword list
STOPWORDS = set([
    "a", "about", "above", "after", "again", "against", "all", "am", "an", "and", "any", "are", "aren't", "as", "at",
    "be", "because", "been", "before", "being", "below", "between", "both", "but", "by", "can't", "cannot", "could",
    "couldn't", "did", "didn't", "do", "does", "doesn't", "doing", "don't", "down", "during", "each", "few", "for",
    "from", "further", "had", "hadn't", "has", "hasn't", "have", "haven't", "having", "he", "he'd", "he'll", "he's",
    "her", "here", "here's", "hers", "herself", "him", "himself", "his", "how", "how's", "i", "i'd", "i'll", "i'm",
    "i've", "if", "in", "into", "is", "isn't", "it", "it's", "its", "itself", "let's", "me", "more", "most", "mustn't",
    "my", "myself", "no", "nor", "not", "of", "off", "on", "once", "only", "or", "other", "ought", "our", "ours",
    "ourselves", "out", "over", "own", "same", "shan't", "she", "she'd", "she'll", "she's", "should", "shouldn't",
    "so", "some", "such", "than", "that", "that's", "the", "their", "theirs", "them", "themselves", "then", "there",
    "there's", "these", "they", "they'd", "they'll", "they're", "they've", "this", "those", "through", "to", "too",
    "under", "until", "up", "very", "was", "wasn't", "we", "we'd", "we'll", "we're", "we've", "were", "weren't",
    "what", "what's", "when", "when's", "where", "where's", "which", "while", "who", "who's", "whom", "why", "why's",
    "with", "won't", "would", "wouldn't", "you", "you'd", "you'll", "you're", "you've", "your", "yours", "yourself",
    "yourselves"
])

# Category anchor definitions
# NOTE: Dimensions 6 (Projects/Portfolio) and 7 (Location) are intentionally
# excluded from FAISS — they are scored separately as bonus points in the
# PHP composite scorer (+10 pts each if matched), so embedding them here
# would cause double-counting in the final match score.
CATEGORIES = {
    # Block A: Experience & Requirements (3 dimensions)
    0: {
        "name": "Experience Level/Seniority/Requirements",
        "anchors": [
            "senior", "lead", "principal", "manager", "director", "head",
            "architect", "expert", "vp", "chief", "executive", "sr",
            "required", "qualification", "must", "needed", "should",
            "ability", "skillset", "requisite", "criteria", "eligibility",
            "minimum"
        ]
    },
    1: {
        "name": "Experience Years",
        "anchors": [
            "years", "year", "experience", "exp", "practiced", "working",
            "career", "history", "tenure", "background"
        ]
    },
    2: {
        "name": "Experience Juniority",
        "anchors": [
            "junior", "intern", "entry", "trainee", "associate", "beginner",
            "fresher", "co-op", "jr", "student", "apprentice"
        ]
    },

    # Block B: Job Profile & Description (3 dimensions)
    3: {
        "name": "Job Profile Domain (Description Context)",
        "anchors": [
            "developer", "engineer", "programmer", "coder", "software",
            "development", "application", "systems", "architect", "web",
            "design", "develop", "maintain", "test", "build", "code",
            "program", "integrate", "implement", "optimize"
        ]
    },
    4: {
        "name": "Job Profile Role Type",
        "anchors": [
            "web", "frontend", "backend", "fullstack", "stack", "mobile",
            "ios", "android", "designer", "ui", "ux", "interface", "app",
            "dashboard", "graphics", "artist", "illustrator"
        ]
    },
    5: {
        "name": "Job Profile Context (Duties & Responsibilities)",
        "anchors": [
            "devops", "cloud", "aws", "docker", "kubernetes", "linux",
            "systems", "network", "security", "database", "sql", "postgresql",
            "mysql", "data", "ml", "ai", "machine", "learning", "qa", "test",
            "testing", "analytics", "science", "nlp", "pipelines", "automation",
            "infrastructure", "deployment", "ci", "cd"
        ]
    },

    # Dimensions 6 & 7 are reserved (zero-filled) — scored separately by PHP bonus points:
    #   6 → Projects & Portfolio  (PHP: +10 pts if CV/GitHub/portfolio present)
    #   7 → Location              (PHP: +10 pts if location matches)

    # Block E: Skills (1 dimension)
    8: {
        "name": "Skills (Tech Stack)",
        "anchors": [
            "python", "php", "javascript", "react", "laravel", "sql", "css",
            "html", "docker", "django", "postgresql", "node", "java", "c#",
            "c++", "ruby", "rails", "git", "bash", "linux", "aws", "gcp",
            "azure", "tailwind", "rest", "api", "apis", "vue", "angular",
            "typescript", "nextjs", "next.js", "mongodb", "mysql", "nosql",
            "sass", "bootstrap", "jquery", "graphql"
        ]
    },

    # Block F: Education (1 dimension)
    9: {
        "name": "Education",
        "anchors": [
            "degree", "bachelor", "master", "phd", "university", "college",
            "diploma", "graduate", "study", "education", "bsc", "msc", "tu",
            "science", "computer", "engineering", "academic", "certified",
            "certification"
        ]
    }
}
