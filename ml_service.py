from fastapi import FastAPI
from pydantic import BaseModel
import re
import math
import joblib
import pandas as pd
import os

app = FastAPI()

# Load trained Random Forest model
MODEL_PATH = "phishing_model.pkl"
if os.path.exists(MODEL_PATH):
    model = joblib.load(MODEL_PATH)
    print("🤖 Scikit-Learn Model loaded successfully!")
else:
    model = None
    print("⚠️ Model file not found. Run train_model.py first.")

class ScanRequest(BaseModel):
    url: str

def calculate_entropy(text: str) -> float:
    if not text:
        return 0.0
    prob = [float(text.count(c)) / len(text) for c in set(text)]
    return -sum([p * math.log2(p) for p in prob])

def extract_features(url: str):
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
    features_dict = extract_features(data.url)
    
    if model:
        # Convert dictionary to DataFrame matching training feature columns
        df_features = pd.DataFrame([features_dict])
        
        # Get class probability (% likelihood of being phishing / Label 1)
        phishing_prob = model.predict_proba(df_features)[0][1]
        final_score = round(phishing_prob * 100, 2)
    else:
        final_score = 50.0

    status = "Malicious" if final_score >= 60 else ("Suspicious" if final_score >= 35 else "Safe")

    return {
        "url": data.url,
        "risk_score": final_score,
        "status": status,
        "features": features_dict
    }