import os
import sys
import json
import math
import re
from collections import Counter

# Set standard output encoding to UTF-8
sys.stdout.reconfigure(encoding='utf-8')

STORAGE_DIR = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "storage", "app", "vector_db")

# Stopwords list
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

# Category Anchors for Block Projection
CATEGORIES = {
    # Block A: Experience & Requirements (3 dimensions)
    0: {"name": "Experience Level/Seniority/Requirements", "anchors": ["senior", "lead", "principal", "manager", "director", "head", "architect", "expert", "vp", "chief", "executive", "sr", "required", "qualification", "must", "needed", "should", "ability", "skillset", "requisite", "criteria", "eligibility", "qualification", "minimum"]},
    1: {"name": "Experience Years", "anchors": ["years", "year", "experience", "exp", "practiced", "working", "career", "history", "tenure", "background"]},
    2: {"name": "Experience Juniority", "anchors": ["junior", "intern", "entry", "trainee", "associate", "beginner", "fresher", "co-op", "jr", "student", "apprentice"]},
    
    # Block B: Job Profile & Description (3 dimensions)
    3: {"name": "Job Profile Domain (Description Context)", "anchors": ["developer", "engineer", "programmer", "coder", "software", "development", "application", "systems", "architect", "web", "design", "develop", "maintain", "test", "build", "code", "program", "integrate", "implement", "optimize"]},
    4: {"name": "Job Profile Role Type", "anchors": ["web", "frontend", "backend", "fullstack", "stack", "mobile", "ios", "android", "designer", "ui", "ux", "interface", "app", "dashboard", "graphics", "artist", "illustrator"]},
    5: {"name": "Job Profile Context (Duties & Responsibilities)", "anchors": ["devops", "cloud", "aws", "docker", "kubernetes", "linux", "systems", "network", "security", "database", "sql", "postgresql", "mysql", "data", "ml", "ai", "machine", "learning", "qa", "test", "testing", "analytics", "science", "nlp", "pipelines", "automation", "infrastructure", "deployment", "ci", "cd"]},
    
    # Block C: Projects & Portfolio (1 dimension)
    6: {"name": "Projects & Portfolio", "anchors": ["project", "portfolio", "github", "build", "implemented", "created", "designed", "achieved", "developed", "led", "contributed", "repo", "repository", "side-project", "gitlab", "bitbucket", "behance", "dribbble"]},
    
    # Block D: Location (1 dimension)
    7: {"name": "Location", "anchors": ["remote", "hybrid", "onsite", "office", "home", "flexible", "travel", "kathmandu", "lalitpur", "pokhara", "nepal", "city", "country"]},
    
    # Block E: Skills (1 dimension)
    8: {"name": "Skills (Tech Stack)", "anchors": ["python", "php", "javascript", "react", "laravel", "sql", "css", "html", "docker", "django", "postgresql", "node", "java", "c#", "c++", "ruby", "rails", "git", "bash", "linux", "aws", "gcp", "azure", "tailwind", "rest", "api", "apis", "vue", "angular", "typescript", "nextjs", "next.js", "mongodb", "mysql", "nosql", "sass", "bootstrap", "jquery", "graphql"]},
    
    # Block F: Education (1 dimension)
    9: {"name": "Education", "anchors": ["degree", "bachelor", "master", "phd", "university", "college", "diploma", "graduate", "study", "education", "bsc", "msc", "tu", "science", "computer", "engineering", "academic", "certified", "certification", "diploma"]}
}


class TextPreprocessor:
    @staticmethod
    def preprocess(text):
        if not text:
            return []
        text = text.lower()
        # Remove punctuation but keep alphanumeric and spaces
        text = re.sub(r'[^a-z0-9\s]', ' ', text)
        words = text.split()
        # Remove stopwords
        filtered_words = [w for w in words if w not in STOPWORDS and len(w) > 1]
        return filtered_words

    @staticmethod
    def extract_years(text):
        # Extract years of experience from text if present
        if not text:
            return 0
        text = text.lower()
        # Find patterns like "3 years", "5+ years", "1 year"
        matches = re.findall(r'(\d+)\s*(?:year|yr)', text)
        if matches:
            return max(int(m) for m in matches)
        # Check single digits in context of experience
        matches_digits = re.findall(r'\b(\d+)\b', text)
        if matches_digits:
            # If there's a number followed closely by experience keyword
            for m in matches_digits:
                val = int(m)
                if 0 < val <= 20 and ("exp" in text or "experience" in text):
                    return val
        return 0

class TFIDFVectorizer:
    def __init__(self):
        self.vocab = {}
        self.idf = {}

    def fit(self, documents):
        # Fit vocabulary and IDF on a list of texts
        doc_count = len(documents)
        if doc_count == 0:
            return
        
        # Build vocabulary
        all_words = []
        doc_words_set = []
        for doc in documents:
            words = TextPreprocessor.preprocess(doc)
            all_words.extend(words)
            doc_words_set.append(set(words))

        unique_words = sorted(list(set(all_words)))
        self.vocab = {word: idx for idx, word in enumerate(unique_words)}

        # Compute IDF
        self.idf = {}
        for word, idx in self.vocab.items():
            containing_docs = sum(1 for doc_set in doc_words_set if word in doc_set)
            # Standard IDF: log(1 + N / (1 + df))
            self.idf[word] = math.log(1.0 + doc_count / (1.0 + containing_docs))

    def transform(self, text):
        # Transform a single text into a TF-IDF vector matching self.vocab
        words = TextPreprocessor.preprocess(text)
        if not words or not self.vocab:
            return [0.0] * len(self.vocab) if self.vocab else []

        word_counts = Counter(words)
        total_words = len(words)

        vector = [0.0] * len(self.vocab)
        for word, count in word_counts.items():
            if word in self.vocab:
                tf = count / total_words
                idf = self.idf[word]
                vector[self.vocab[word]] = tf * idf
        return vector

    def to_json(self):
        return {
            "vocab": self.vocab,
            "idf": self.idf
        }

    def from_json(self, data):
        self.vocab = data.get("vocab", {})
        self.idf = data.get("idf", {})

class DenseEmbeddingGenerator:
    @staticmethod
    def generate(text, tfidf_vector, vectorizer):
        # Generate 10D embedding from TF-IDF vector using category weights
        emb = [0.0] * 10
        if not vectorizer.vocab or not tfidf_vector:
            return emb

        # Map word weights to categories
        for word, idx in vectorizer.vocab.items():
            val = tfidf_vector[idx]
            if val <= 0:
                continue

            for cat_id, cat_info in CATEGORIES.items():
                if word in cat_info["anchors"]:
                    emb[cat_id] += val

        # Handle numeric years of experience for dimension 1 (Experience Years)
        years = TextPreprocessor.extract_years(text)
        if years > 0:
            # Normalize to range [0, 1] assuming max 15 years
            emb[1] += min(1.0, years / 15.0)

        # Normalize the vector to unit L2 norm
        norm = math.sqrt(sum(x * x for x in emb))
        if norm > 1e-9:
            emb = [x / norm for x in emb]
        else:
            # Fallback if no matching categories found: distribute equally
            emb = [1.0 / math.sqrt(10.0)] * 10

        return emb

# Math helpers
def dot_product(a, b):
    return sum(x * y for x, y in zip(a, b))

def magnitude(a):
    return math.sqrt(sum(x * x for x in a))

def cosine_similarity(a, b):
    mag_a = magnitude(a)
    mag_b = magnitude(b)
    if mag_a < 1e-9 or mag_b < 1e-9:
        return 0.0
    return dot_product(a, b) / (mag_a * mag_b)

def dist_l2(a, b):
    return math.sqrt(sum((x - y) ** 2 for x, y in zip(a, b)))

class KMeans:
    @staticmethod
    def cluster(vectors, k, max_iters=50):
        if not vectors:
            return [], {}
        if len(vectors) <= k:
            # Each item gets its own centroid
            return [v for v in vectors], {i: [i] for i in range(len(vectors))}

        # Deterministic centroid initialization: take spread-out indices
        centroids = []
        step = len(vectors) // k
        for i in range(k):
            centroids.append(vectors[i * step])

        for iteration in range(max_iters):
            # Assign to nearest centroid (using cosine distance = 1 - similarity)
            clusters = {i: [] for i in range(k)}
            for vec_idx, vec in enumerate(vectors):
                best_sim = -2.0
                best_idx = 0
                for cent_idx, cent in enumerate(centroids):
                    sim = dot_product(vec, cent)
                    if sim > best_sim:
                        best_sim = sim
                        best_idx = cent_idx
                clusters[best_idx].append(vec_idx)

            # Update centroids
            new_centroids = []
            changed = False
            for cent_idx in range(k):
                members = clusters[cent_idx]
                if not members:
                    new_centroids.append(centroids[cent_idx])
                    continue

                mean_vec = [0.0] * 10
                for idx in members:
                    for d in range(10):
                        mean_vec[d] += vectors[idx][d]
                for d in range(10):
                    mean_vec[d] /= len(members)

                # Normalize mean
                norm = math.sqrt(sum(x * x for x in mean_vec))
                if norm > 1e-9:
                    mean_vec = [x / norm for x in mean_vec]
                else:
                    mean_vec = centroids[cent_idx]

                new_centroids.append(mean_vec)
                if dist_l2(mean_vec, centroids[cent_idx]) > 1e-6:
                    changed = True

            centroids = new_centroids
            if not changed:
                break

        return centroids, clusters

class FAISSIndex:
    def __init__(self, k=3):
        self.k = k
        self.centroids = []
        self.inverted_lists = {} # maps centroid_idx to list of item IDs

    def build(self, item_ids, vectors):
        if not vectors:
            self.centroids = []
            self.inverted_lists = {}
            return
        
        actual_k = min(self.k, len(vectors))
        self.centroids, clusters = KMeans.cluster(vectors, actual_k)

        self.inverted_lists = {i: [] for i in range(actual_k)}
        for cent_idx, member_indices in clusters.items():
            for idx in member_indices:
                self.inverted_lists[cent_idx].append(item_ids[idx])

    def search(self, query_vector, item_vectors, n_probe=2):
        if not self.centroids or not item_vectors:
            return []

        # Find closest centroids
        centroid_similarities = []
        for cent_idx, cent in enumerate(self.centroids):
            sim = dot_product(query_vector, cent)
            centroid_similarities.append((cent_idx, sim))

        # Sort centroids by similarity descending
        centroid_similarities.sort(key=lambda x: x[1], reverse=True)
        probed_centroids = [x[0] for x in centroid_similarities[:n_probe]]

        # Gather candidates
        candidates = []
        for cent_idx in probed_centroids:
            if cent_idx in self.inverted_lists:
                candidates.extend(self.inverted_lists[cent_idx])

        # De-duplicate candidates
        candidates = list(set(candidates))

        # Calculate exact similarity
        results = []
        for item_id in candidates:
            if item_id in item_vectors:
                sim = dot_product(query_vector, item_vectors[item_id])
                results.append((item_id, sim))

        # Sort by similarity descending
        results.sort(key=lambda x: x[1], reverse=True)
        return results

    def to_json(self):
        return {
            "k": self.k,
            "centroids": self.centroids,
            "inverted_lists": self.inverted_lists
        }

    def from_json(self, data):
        self.k = data.get("k", 3)
        self.centroids = data.get("centroids", [])
        # JSON keys are always strings, convert keys back to integers
        inv_lists = data.get("inverted_lists", {})
        self.inverted_lists = {int(k): v for k, v in inv_lists.items()}

class VectorDatabase:
    def __init__(self):
        os.makedirs(STORAGE_DIR, exist_ok=True)
        self.users_file = os.path.join(STORAGE_DIR, "users.json")
        self.jobs_file = os.path.join(STORAGE_DIR, "jobs.json")
        self.index_file = os.path.join(STORAGE_DIR, "index.json")
        self.vocab_file = os.path.join(STORAGE_DIR, "vocab.json")

        self.users = self._load_file(self.users_file)
        self.jobs = self._load_file(self.jobs_file)
        self.vocab_data = self._load_file(self.vocab_file)

    def _load_file(self, path):
        if os.path.exists(path):
            try:
                with open(path, "r", encoding="utf-8") as f:
                    return json.load(f)
            except Exception:
                return {}
        return {}

    def _save_file(self, path, data):
        with open(path, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=2, ensure_ascii=False)

    def save_user(self, user_id, text, vector):
        # Save user with block structure
        user_id = str(user_id)
        self.users[user_id] = {
            "id": user_id,
            "text": text,
            "vector": vector,
            "blocks": {
                "experience": vector[0:3],
                "job_profile": vector[3:6],
                "project": [vector[6]],
                "location": [vector[7]],
                "skills": [vector[8]],
                "education": [vector[9]]
            }
        }
        self._save_file(self.users_file, self.users)

    def save_job(self, job_id, text, vector):
        # Save job with block structure
        job_id = str(job_id)
        self.jobs[job_id] = {
            "id": job_id,
            "text": text,
            "vector": vector,
            "blocks": {
                "experience": vector[0:3],
                "job_profile": vector[3:6],
                "project": [vector[6]],
                "location": [vector[7]],
                "skills": [vector[8]],
                "education": [vector[9]]
            }
        }
        self._save_file(self.jobs_file, self.jobs)

    def get_user_vector(self, user_id):
        user_id = str(user_id)
        if user_id in self.users:
            return self.users[user_id]["vector"]
        return None

    def get_job_vector(self, job_id):
        job_id = str(job_id)
        if job_id in self.jobs:
            return self.jobs[job_id]["vector"]
        return None

    def build_and_save_index(self):
        # 1. Fit TF-IDF on all texts combined
        all_texts = []
        for u in self.users.values():
            all_texts.append(u["text"])
        for j in self.jobs.values():
            all_texts.append(j["text"])

        vectorizer = TFIDFVectorizer()
        vectorizer.fit(all_texts)
        self.vocab_data = vectorizer.to_json()
        self._save_file(self.vocab_file, self.vocab_data)

        # 2. Re-embed all items with the newly fitted vocabulary
        user_vectors = {}
        for uid, udata in self.users.items():
            tfidf = vectorizer.transform(udata["text"])
            vec = DenseEmbeddingGenerator.generate(udata["text"], tfidf, vectorizer)
            self.save_user(uid, udata["text"], vec)
            user_vectors[uid] = vec

        job_vectors = {}
        for jid, jdata in self.jobs.items():
            tfidf = vectorizer.transform(jdata["text"])
            vec = DenseEmbeddingGenerator.generate(jdata["text"], tfidf, vectorizer)
            self.save_job(jid, jdata["text"], vec)
            job_vectors[jid] = vec

        # 3. Build FAISS IVF indexes
        # Seeker Index (clusters user vectors)
        seeker_ids = list(user_vectors.keys())
        seeker_vecs = list(user_vectors.values())
        seeker_index = FAISSIndex(k=3)
        seeker_index.build(seeker_ids, seeker_vecs)

        # Job Index (clusters job vectors)
        job_ids = list(job_vectors.keys())
        job_vecs = list(job_vectors.values())
        job_index = FAISSIndex(k=3)
        job_index.build(job_ids, job_vecs)

        # Save index data
        index_data = {
            "seeker_index": seeker_index.to_json(),
            "job_index": job_index.to_json()
        }
        self._save_file(self.index_file, index_data)

    def load_index(self):
        index_data = self._load_file(self.index_file)
        seeker_index = FAISSIndex()
        job_index = FAISSIndex()

        if "seeker_index" in index_data:
            seeker_index.from_json(index_data["seeker_index"])
        if "job_index" in index_data:
            job_index.from_json(index_data["job_index"])

        return seeker_index, job_index

    def load_vectorizer(self):
        vectorizer = TFIDFVectorizer()
        if self.vocab_data:
            vectorizer.from_json(self.vocab_data)
        return vectorizer

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No arguments provided"}))
        return

    db = VectorDatabase()
    cmd = sys.argv[1]

    try:
        if cmd == "--embed-user":
            # Arguments: --embed-user --id <user_id> --text "<profile_text>"
            user_id = sys.argv[sys.argv.index("--id") + 1]
            text = sys.argv[sys.argv.index("--text") + 1]
            
            vectorizer = db.load_vectorizer()
            # If vocabulary is empty, fit on this single text temporarily
            if not vectorizer.vocab:
                vectorizer.fit([text])
            
            tfidf = vectorizer.transform(text)
            vector = DenseEmbeddingGenerator.generate(text, tfidf, vectorizer)
            db.save_user(user_id, text, vector)
            print(json.dumps({"success": True, "vector": vector}))

        elif cmd == "--embed-job":
            # Arguments: --embed-job --id <job_id> --text "<job_text>"
            job_id = sys.argv[sys.argv.index("--id") + 1]
            text = sys.argv[sys.argv.index("--text") + 1]
            
            vectorizer = db.load_vectorizer()
            if not vectorizer.vocab:
                vectorizer.fit([text])
                
            tfidf = vectorizer.transform(text)
            vector = DenseEmbeddingGenerator.generate(text, tfidf, vectorizer)
            db.save_job(job_id, text, vector)
            print(json.dumps({"success": True, "vector": vector}))

        elif cmd == "--index":
            db.build_and_save_index()
            print(json.dumps({"success": True, "message": "Index rebuilt successfully"}))

        elif cmd == "--search-jobs":
            # Arguments: --search-jobs --id <user_id>
            user_id = sys.argv[sys.argv.index("--id") + 1]
            q_vec = db.get_user_vector(user_id)
            if not q_vec:
                # Fallback: if user is not in database, get profile text and embed
                print(json.dumps([]))
                return

            _, job_index = db.load_index()
            job_vectors = {jid: jdata["vector"] for jid, jdata in db.jobs.items()}
            
            results = job_index.search(q_vec, job_vectors, n_probe=2)
            # Map similarity score to percentage (0 - 100)
            formatted = [{"job_id": int(jid), "score": max(0, min(100, int(sim * 100)))} for jid, sim in results]
            print(json.dumps(formatted))

        elif cmd == "--search-applicants":
            # Arguments: --search-applicants --id <job_id>
            job_id = sys.argv[sys.argv.index("--id") + 1]
            q_vec = db.get_job_vector(job_id)
            if not q_vec:
                print(json.dumps([]))
                return

            seeker_index, _ = db.load_index()
            seeker_vectors = {uid: udata["vector"] for uid, udata in db.users.items()}
            
            results = seeker_index.search(q_vec, seeker_vectors, n_probe=2)
            formatted = [{"user_id": int(uid), "score": max(0, min(100, int(sim * 100)))} for uid, sim in results]
            print(json.dumps(formatted))

        elif cmd == "--search-cv":
            # Arguments: --search-cv --text "<cv_text>"
            text = sys.argv[sys.argv.index("--text") + 1]
            vectorizer = db.load_vectorizer()
            
            if not vectorizer.vocab:
                # If no vocabulary, fit on jobs
                all_job_texts = [j["text"] for j in db.jobs.values()]
                vectorizer.fit(all_job_texts + [text])
                
            tfidf = vectorizer.transform(text)
            q_vec = DenseEmbeddingGenerator.generate(text, tfidf, vectorizer)

            _, job_index = db.load_index()
            job_vectors = {jid: jdata["vector"] for jid, jdata in db.jobs.items()}
            
            results = job_index.search(q_vec, job_vectors, n_probe=2)
            formatted = [{"job_id": int(jid), "score": max(0, min(100, int(sim * 100)))} for jid, sim in results]
            print(json.dumps(formatted))

        else:
            print(json.dumps({"error": f"Unknown command {cmd}"}))

    except Exception as e:
        import traceback
        print(json.dumps({"error": str(e), "traceback": traceback.format_exc()}))

if __name__ == "__main__":
    main()
