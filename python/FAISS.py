from K_means import KMeans
from math_helper import dot_product


class FAISSIndex:
 
    def __init__(self, k=3):
        self.k = k
        self.centroids:      list = []
        self.inverted_lists: dict = {}   # centroid_idx → [item_ids]

    def build(self, item_ids, vectors):
       
        if not vectors:
            self.centroids      = []
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

        centroid_sims = [
            (cent_idx, dot_product(query_vector, cent))
            for cent_idx, cent in enumerate(self.centroids)
        ]
        centroid_sims.sort(key=lambda x: x[1], reverse=True)
        probed_centroids = [x[0] for x in centroid_sims[:n_probe]]

        candidates = []
        for cent_idx in probed_centroids:
            if cent_idx in self.inverted_lists:
                candidates.extend(self.inverted_lists[cent_idx])
        candidates = list(set(candidates))

        results = []
        for item_id in candidates:
            if item_id in item_vectors:
                sim = dot_product(query_vector, item_vectors[item_id])
                results.append((item_id, sim))

        results.sort(key=lambda x: x[1], reverse=True)
        return results

    def to_json(self):
#Serialize index state to a JSON-safe dict.
        return {
            "k":              self.k,
            "centroids":      self.centroids,
            "inverted_lists": self.inverted_lists
        }

    def from_json(self, data):
#Restore index state from a previously serialized dict.

        self.k           = data.get("k", 3)
        self.centroids   = data.get("centroids", [])
        inv_lists        = data.get("inverted_lists", {})
        self.inverted_lists = {int(k): v for k, v in inv_lists.items()}
