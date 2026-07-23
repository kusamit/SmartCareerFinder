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

    norm = math.sqrt(sum(x * x for x in v))
    if norm > 1e-9:
        return [x / norm for x in v]
    # Fallback: uniform distribution across all dimensions
    dim = len(v)
    return [1.0 / math.sqrt(float(dim))] * dim if dim > 0 else v
