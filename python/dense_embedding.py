from config import CATEGORIES
from tf_idf import TextPreprocessor
from math_helper import l2_normalize

#Dense Algorithm
class DenseEmbeddingGenerator:
    @staticmethod
    def generate(text, tfidf_vector, vectorizer):
        emb = [0.0] * 10

        if not vectorizer.vocab or not tfidf_vector:
            return emb

        # Step 2: Accumulate TF-IDF weights into category dimensions
        for word, idx in vectorizer.vocab.items():
            val = tfidf_vector[idx]
            if val <= 0:
                continue
            for cat_id, cat_info in CATEGORIES.items():
                if word in cat_info["anchors"]:
                    emb[cat_id] += val

        # Step 3: Add experience year contribution to Dimension 1
        # Formula: min(1.0, years / 15.0)  — assumes 15 years as the maximum scale
        years = TextPreprocessor.extract_years(text)
        if years > 0:
            emb[1] += min(1.0, years / 15.0)

        # Step 4: L2-normalize to unit length
        # If norm ≈ 0 (no category anchors matched), l2_normalize returns
        # a uniform fallback vector of 1/sqrt(10) per dimension.
        emb = l2_normalize(emb)

        return emb
