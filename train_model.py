import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score
import joblib

# 1. Synthesize training data mimicking Kaggle UCI Phishing URL datasets
np.random.seed(42)
num_samples = 2000

# Generating realistic feature distributions:
# [url_length, has_at_symbol, has_ip, hyphen_count, subdomain_count, entropy]

# Legitimate sites (Label 0)
legit_len = np.random.randint(15, 45, num_samples // 2)
legit_at = np.zeros(num_samples // 2)
legit_ip = np.zeros(num_samples // 2)
legit_hyphen = np.random.choice([0, 1], size=num_samples // 2, p=[0.8, 0.2])
legit_sub = np.random.choice([0, 1], size=num_samples // 2, p=[0.85, 0.15])
legit_entropy = np.random.uniform(2.5, 3.8, num_samples // 2)
legit_labels = np.zeros(num_samples // 2)

# Phishing sites (Label 1)
phish_len = np.random.randint(50, 120, num_samples // 2)
phish_at = np.random.choice([0, 1], size=num_samples // 2, p=[0.6, 0.4])
phish_ip = np.random.choice([0, 1], size=num_samples // 2, p=[0.7, 0.3])
phish_hyphen = np.random.randint(2, 8, num_samples // 2)
phish_sub = np.random.randint(2, 5, num_samples // 2)
phish_entropy = np.random.uniform(3.9, 5.2, num_samples // 2)
phish_labels = np.ones(num_samples // 2)

# Combine into DataFrame
X = pd.DataFrame({
    'url_length': np.concatenate([legit_len, phish_len]),
    'has_at_symbol': np.concatenate([legit_at, phish_at]),
    'has_ip': np.concatenate([legit_ip, phish_ip]),
    'hyphen_count': np.concatenate([legit_hyphen, phish_hyphen]),
    'subdomain_count': np.concatenate([legit_sub, phish_sub]),
    'entropy': np.concatenate([legit_entropy, phish_entropy])
})
y = np.concatenate([legit_labels, phish_labels])

# 2. Train/Test Split
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# 3. Train Random Forest Classifier
clf = RandomForestClassifier(n_estimators=100, random_state=42)
clf.fit(X_train, y_train)

# Evaluate model
y_pred = clf.predict(X_test)
acc = accuracy_score(y_test, y_pred)
print(f"✅ Model trained successfully with {acc * 100:.2f}% test accuracy!")

# 4. Save trained model to disk
joblib.dump(clf, 'phishing_model.pkl')
print("📦 Saved trained model as 'phishing_model.pkl'")