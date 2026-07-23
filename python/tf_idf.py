import math
import re
from collections import Counter

from config import STOPWORDS


# Text Preprocessor

class TextPreprocessor:

    @staticmethod
    def preprocess(text):

        if not text:
            return []
        text = text.lower()
        text = re.sub(r'[^a-z0-9\s]', ' ', text)
        words = text.split()
        return [w for w in words if w not in STOPWORDS and len(w) > 1]

    @staticmethod
    def extract_years(text):
        
        if not text:
            return 0
        text = text.lower()
        matches = re.findall(r'(\d+)\+?\s*(?:year|yr)', text)
        if matches:
            return max(int(m) for m in matches)
        # Secondary check: bare digit near 'experience' or 'exp'
        matches_digits = re.findall(r'\b(\d+)\b', text)
        if matches_digits:
            for m in matches_digits:
                val = int(m)
                if 0 < val <= 20 and ("exp" in text or "experience" in text):
                    return val
        return 0


# TF-IDF Vectorizer

class TFIDFVectorizer:

    def __init__(self):
        self.vocab: dict = {}
        self.idf:   dict = {}

    def fit(self, documents):
  
        #Build vocabulary index and IDF weights from a corpus of documents.
        doc_count = len(documents)
        if doc_count == 0:
            return

        all_words = []
        doc_words_set = []
        for doc in documents:
            words = TextPreprocessor.preprocess(doc)
            all_words.extend(words)
            doc_words_set.append(set(words))

        unique_words = sorted(list(set(all_words)))
        self.vocab = {word: idx for idx, word in enumerate(unique_words)}

        self.idf = {}
        for word in self.vocab:
            df = sum(1 for doc_set in doc_words_set if word in doc_set)
            # Smoothed IDF formula: log(1 + N / (1 + df))
            self.idf[word] = math.log(1.0 + doc_count / (1.0 + df))

    def transform(self, text):
        #Convert a single text document into a TF-IDF weighted vector.

        words = TextPreprocessor.preprocess(text)
        if not words or not self.vocab:
            return [0.0] * len(self.vocab) if self.vocab else []

        word_counts = Counter(words)
        total_words = len(words)

        vector = [0.0] * len(self.vocab)
        for word, count in word_counts.items():
            if word in self.vocab:
                tf  = count / total_words
                idf = self.idf.get(word, 0.0)
                vector[self.vocab[word]] = tf * idf
        return vector

    def to_json(self):
        #Serialize the fitted vectorizer state to a JSON-safe dict.
        return {"vocab": self.vocab, "idf": self.idf}

    def from_json(self, data):
    #Restore a previously serialized vectorizer from a dict.
        self.vocab = data.get("vocab", {})
        self.idf   = data.get("idf",   {})
