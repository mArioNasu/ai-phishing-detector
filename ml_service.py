from fastapi import FastAPI
from pydantic import BaseModel
import re
import math

app = FastAPI()

class ScanRequest(BaseModel):
    url: str

def calculate_entropy(text: str) -> float:
    """Calculates randomness (entropy) of character distribution in the URL."""
    if not text:
        return 0.0
    prob = [float(text.count(c)) / len(text) for c in set(text)]
    return -sum([p * math.log2(p) for p in prob])

def extract_features(url: str):
    """Extracts 6 core numerical features used by ML classifiers."""
    url_lower = url.lower()
    return {
        "url_length": len(url),
        "has_at_symbol": 1 if "@" in url else 0,
        "has_ip": 1 if re.search(r"https?://\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}", url) else 0,
        "hyphen_count": url.count("-"),
        "subdomain_count": max(0, url.split("/")[2].count(".") - 1) if "://" in url else url.count("."),
        "entropy": round(calculate_entropy(url), 2)
    }

@app.post("/predict")
def predict_phishing(data: ScanRequest):
    features = extract_features(data.url)
    
    # Heuristic scoring simulating a trained Random Forest classifier
    score = 0
    if features["url_length"] > 54: score += 20
    if features["has_at_symbol"]: score += 30
    if features["has_ip"]: score += 35
    if features["hyphen_count"] > 2: score += 15
    if features["subdomain_count"] >= 3: score += 20
    if features["entropy"] > 4.2: score += 15

    final_score = min(score, 99.9)
    status = "Malicious" if final_score >= 50 else ("Suspicious" if final_score >= 25 else "Safe")

    return {
        "url": data.url,
        "risk_score": final_score,
        "status": status,
        "features": features
    }