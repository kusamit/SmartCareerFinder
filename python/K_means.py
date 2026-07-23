import math

from math_helper import dot_product, dist_l2, l2_normalize


class KMeans:

    @staticmethod
    def cluster(vectors, k, max_iters=50):
     
        if not vectors:
            return [], {}

        # Edge case: fewer vectors than requested clusters
        if len(vectors) <= k:
            centroids = [v for v in vectors]
            clusters  = {i: [i] for i in range(len(vectors))}
            return centroids, clusters

        # Step 1: Deterministic initialization — evenly spaced indices
        step = len(vectors) // k
        centroids = [vectors[i * step] for i in range(k)]

        clusters = {}
        for iteration in range(max_iters):

            # Step 2: Assignment — assign each vector to closest centroid
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

            # Step 3: Update — recompute centroids as normalized mean
            new_centroids = []
            changed = False
            for cent_idx in range(k):
                members = clusters[cent_idx]
                if not members:
                    # Empty cluster: keep old centroid unchanged
                    new_centroids.append(centroids[cent_idx])
                    continue

                # Compute element-wise mean across all member vectors
                mean_vec = [0.0] * 10
                for idx in members:
                    for d in range(10):
                        mean_vec[d] += vectors[idx][d]
                for d in range(10):
                    mean_vec[d] /= len(members)

                # L2-normalize the new centroid
                new_cent = l2_normalize(mean_vec)

                # Track convergence
                if dist_l2(new_cent, centroids[cent_idx]) > 1e-6:
                    changed = True

                new_centroids.append(new_cent)

            centroids = new_centroids

            # Step 4: Early stop if all centroids converged
            if not changed:
                break

        return centroids, clusters
