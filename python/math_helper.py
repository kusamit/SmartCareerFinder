import math


def dot_product(a, b):
#Return the dot product of two equal-length numeric lists.
    return sum(x * y for x, y in zip(a, b))


def magnitude(a):
#Return the Euclidean (L2) norm of vector a.
    return math.sqrt(sum(x * x for x in a))


def cosine_similarity(a, b):

    mag_a = magnitude(a)
    mag_b = magnitude(b)
    if mag_a < 1e-9 or mag_b < 1e-9:
        return 0.0
    return dot_product(a, b) / (mag_a * mag_b)


def dist_l2(a, b):

    return math.sqrt(sum((x - y) ** 2 for x, y in zip(a, b)))


def l2_normalize(v):
    """
    L2-normalise a vector to unit length.

    If the norm is effectively zero (no meaningful content detected), returns
    a zero vector instead of a uniform unit vector.  The uniform fallback was
    the root cause of FAISS scores of 70/70 on completely unrelated profiles:
    two zero vectors both mapped to [1/√10, ...] whose dot product is 1.0.
    A zero vector correctly yields cosine similarity = 0.0 (no match).
    """
    norm = math.sqrt(sum(x * x for x in v))
    if norm > 1e-9:
        return [x / norm for x in v]
    # Return zero vector — no category anchors matched, so no semantic similarity
    return [0.0] * len(v)
