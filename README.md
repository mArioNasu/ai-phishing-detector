# 🛡️ AI Phishing & URL Threat Detector

An end-to-end cybersecurity web application that detects phishing links and zero-day malicious URLs in real-time. Built using a microservice architecture with PHP, Python FastAPI, MySQL, and Tailwind CSS.

## 🏗️ System Architecture

- **Frontend & Dashboard:** PHP 8.x, Tailwind CSS (via CDN), JavaScript (Fetch API AJAX)
- **Database:** MySQL (stores scan logs, threat signals, and user history)
- **AI Microservice:** Python 3.x, FastAPI, Uvicorn (runs lexical feature extraction & entropy calculations)

## 🚀 Key Features

- **Real-Time Threat Scoring:** Analyzes URL length, `@` symbol redirection, IP host usage, subdomains, and character entropy.
- **Microservice Integration:** PHP communicates with the Python ML service via cURL over HTTP.
- **Scan History Logging:** Automatically records scan results and detected threat signals in MySQL.
- **Asynchronous UI:** Dynamic threat reports render without page reloads.

## 🛠️ Local Setup Guide

### 1. Database Setup
1. Open **phpMyAdmin** or **HeidiSQL**.
2. Create a database named `phishing_db`.
3. Import the SQL schema (`schema.sql`).

### 2. Start Python AI Microservice
```bash
# Install dependencies
pip install fastapi uvicorn scikit-learn pandas numpy joblib

# Run Uvicorn server
uvicorn ml_service:app --reload --port 8000