import os
import sys
import json

# Set standard output encoding to UTF-8 (required for Windows shell_exec)
sys.stdout.reconfigure(encoding='utf-8')

from config          import STORAGE_DIR
from tf_idf          import TFIDFVectorizer, TextPreprocessor
from dense_embedding import DenseEmbeddingGenerator
from FAISS           import FAISSIndex
from skill_matcher   import SkillExperienceMatcher, match_term


# Vector Database — Persistent JSON Storage Layer

class VectorDatabase:

    def __init__(self):
        os.makedirs(STORAGE_DIR, exist_ok=True)
        self.users_file = os.path.join(STORAGE_DIR, "users.json")
        self.jobs_file  = os.path.join(STORAGE_DIR, "jobs.json")
        self.index_file = os.path.join(STORAGE_DIR, "index.json")
        self.vocab_file = os.path.join(STORAGE_DIR, "vocab.json")

        self.users      = self._load_file(self.users_file)
        self.jobs       = self._load_file(self.jobs_file)
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
        """Persist a seeker's profile text and 10D vector with block metadata."""
        user_id = str(user_id)
        self.users[user_id] = {
            "id":     user_id,
            "text":   text,
            "vector": vector,
            "blocks": {
                "experience": vector[0:3],
                "job_profile": vector[3:6],
                "project":    [vector[6]],
                "location":   [vector[7]],
                "skills":     [vector[8]],
                "education":  [vector[9]]
            }
        }
        self._save_file(self.users_file, self.users)

    def save_job(self, job_id, text, vector):
        """Persist a job posting's text and 10D vector with block metadata."""
        job_id = str(job_id)
        self.jobs[job_id] = {
            "id":     job_id,
            "text":   text,
            "vector": vector,
            "blocks": {
                "experience": vector[0:3],
                "job_profile": vector[3:6],
                "project":    [vector[6]],
                "location":   [vector[7]],
                "skills":     [vector[8]],
                "education":  [vector[9]]
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

        all_texts = (
            [u["text"] for u in self.users.values()] +
            [j["text"] for j in self.jobs.values()]
        )
        vectorizer = TFIDFVectorizer()
        vectorizer.fit(all_texts)
        self.vocab_data = vectorizer.to_json()
        self._save_file(self.vocab_file, self.vocab_data)

        user_vectors = {}
        for uid, udata in self.users.items():
            tfidf = vectorizer.transform(udata["text"])
            vec   = DenseEmbeddingGenerator.generate(udata["text"], tfidf, vectorizer)
            self.save_user(uid, udata["text"], vec)
            user_vectors[uid] = vec

        job_vectors = {}
        for jid, jdata in self.jobs.items():
            tfidf = vectorizer.transform(jdata["text"])
            vec   = DenseEmbeddingGenerator.generate(jdata["text"], tfidf, vectorizer)
            self.save_job(jid, jdata["text"], vec)
            job_vectors[jid] = vec

        seeker_index = FAISSIndex(k=3)
        seeker_index.build(list(user_vectors.keys()), list(user_vectors.values()))

        job_index = FAISSIndex(k=3)
        job_index.build(list(job_vectors.keys()), list(job_vectors.values()))

        self._save_file(self.index_file, {
            "seeker_index": seeker_index.to_json(),
            "job_index":    job_index.to_json()
        })

    def load_index(self):
        """Load and return (seeker_index, job_index) FAISSIndex instances."""
        index_data   = self._load_file(self.index_file)
        seeker_index = FAISSIndex()
        job_index    = FAISSIndex()

        if "seeker_index" in index_data:
            seeker_index.from_json(index_data["seeker_index"])
        if "job_index" in index_data:
            job_index.from_json(index_data["job_index"])

        return seeker_index, job_index

    def load_vectorizer(self):
        """Load and return the fitted TFIDFVectorizer from vocab.json."""
        vectorizer = TFIDFVectorizer()
        if self.vocab_data:
            vectorizer.from_json(self.vocab_data)
        return vectorizer


# Hybrid Scoring Helper

def _hybrid_score(base_sim, user_text, job_text):
    """Compute hybrid match score combining FAISS vector similarity with skill-experience matching.

    Formula: 40% from FAISS base + 60% from skill match.
    The base vector similarity is preserved as a signal — jobs that are
    semantically very different from the seeker (e.g., IT Support vs PHP Dev)
    will naturally score lower regardless of any skill overlap.
    """
    base_score       = max(0, min(100, int(base_sim * 100)))
    skills_match_pct = int(SkillExperienceMatcher.calculate_match(user_text, job_text) * 100)
    boosted_base     = base_score + (100 - base_score) * (skills_match_pct / 100.0)
    final_score      = int(boosted_base * 0.40 + skills_match_pct * 0.60)
    return max(0, min(100, final_score))


# CLI Entry Point

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No arguments provided"}))
        return

    db  = VectorDatabase()
    cmd = sys.argv[1]

    try:
        if cmd == "--embed-user":
            user_id = sys.argv[sys.argv.index("--id")   + 1]
            text    = sys.argv[sys.argv.index("--text") + 1]

            vectorizer = db.load_vectorizer()
            if not vectorizer.vocab:
                vectorizer.fit([text])

            tfidf  = vectorizer.transform(text)
            vector = DenseEmbeddingGenerator.generate(text, tfidf, vectorizer)
            db.save_user(user_id, text, vector)
            print(json.dumps({"success": True, "vector": vector}))

        elif cmd == "--embed-job":
            job_id = sys.argv[sys.argv.index("--id")   + 1]
            text   = sys.argv[sys.argv.index("--text") + 1]

            vectorizer = db.load_vectorizer()
            if not vectorizer.vocab:
                vectorizer.fit([text])

            tfidf  = vectorizer.transform(text)
            vector = DenseEmbeddingGenerator.generate(text, tfidf, vectorizer)
            db.save_job(job_id, text, vector)
            print(json.dumps({"success": True, "vector": vector}))

        elif cmd == "--index":
            db.build_and_save_index()
            print(json.dumps({"success": True, "message": "Index rebuilt successfully"}))

        elif cmd == "--search-jobs":
            user_id = sys.argv[sys.argv.index("--id") + 1]
            q_vec   = db.get_user_vector(user_id)
            if not q_vec:
                print(json.dumps([]))
                return

            _, job_index = db.load_index()
            job_vectors  = {jid: jdata["vector"] for jid, jdata in db.jobs.items()}
            results      = job_index.search(q_vec, job_vectors, n_probe=10)

            user_text = db.users.get(str(user_id), {}).get("text", "")
            formatted = []
            for jid, sim in results:
                job_text = db.jobs.get(str(jid), {}).get("text", "")
                formatted.append({
                    "job_id": int(jid),
                    "score":  _hybrid_score(sim, user_text, job_text)
                })
            print(json.dumps(formatted))

        elif cmd == "--search-applicants":
            job_id = sys.argv[sys.argv.index("--id") + 1]
            q_vec  = db.get_job_vector(job_id)
            if not q_vec:
                print(json.dumps([]))
                return

            seeker_index, _ = db.load_index()
            seeker_vectors  = {uid: udata["vector"] for uid, udata in db.users.items()}
            results         = seeker_index.search(q_vec, seeker_vectors, n_probe=10)

            job_text  = db.jobs.get(str(job_id), {}).get("text", "")
            formatted = []
            for uid, sim in results:
                user_text = db.users.get(str(uid), {}).get("text", "")
                formatted.append({
                    "user_id": int(uid),
                    "score":   _hybrid_score(sim, user_text, job_text)
                })
            print(json.dumps(formatted))

        elif cmd == "--search-cv":
            text       = sys.argv[sys.argv.index("--text") + 1]
            vectorizer = db.load_vectorizer()

            if not vectorizer.vocab:
                all_job_texts = [j["text"] for j in db.jobs.values()]
                vectorizer.fit(all_job_texts + [text])

            tfidf  = vectorizer.transform(text)
            q_vec  = DenseEmbeddingGenerator.generate(text, tfidf, vectorizer)

            _, job_index = db.load_index()
            job_vectors  = {jid: jdata["vector"] for jid, jdata in db.jobs.items()}
            results      = job_index.search(q_vec, job_vectors, n_probe=10)

            formatted = []
            for jid, sim in results:
                job_text = db.jobs.get(str(jid), {}).get("text", "")
                formatted.append({
                    "job_id": int(jid),
                    "score":  _hybrid_score(sim, text, job_text)
                })
            print(json.dumps(formatted))

        else:
            print(json.dumps({"error": f"Unknown command {cmd}"}))

    except Exception as e:
        import traceback
        print(json.dumps({"error": str(e), "traceback": traceback.format_exc()}))


if __name__ == "__main__":
    main()
