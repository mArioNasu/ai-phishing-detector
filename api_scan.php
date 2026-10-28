<?php
// api_scan.php
header('Content-Type: application/json');
require_once 'db.php';

$input = json_decode(file_get_contents("php://input"), true);
$url = $input['url'] ?? '';

if (empty($url)) {
    echo json_encode(['error' => 'URL is required']);
    exit();
}

// 1. Send URL to Python ML Microservice via cURL
$ch = curl_init('http://127.0.0.1:8000/predict');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $url]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 3); // 3 second fallback timeout

$python_response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($python_response) {
    $ml_data = json_decode($python_response, true);
    $risk_score = $ml_data['risk_score'];
    $status = $ml_data['status'];
    
    $threats = [];
    foreach ($ml_data['features'] as $key => $val) {
        if ($val > 0) {
            $threats[] = "ML Feature Detected: $key = $val";
        }
    }
} else {
    // Fallback if Python API is offline
    $risk_score = 10.0;
    $status = 'Safe';
    $threats = ['Python ML Engine offline - Used basic fallback'];
}

// 2. Log Scan to MySQL
$threats_json = json_encode($threats);
$stmt = $conn->prepare("INSERT INTO scan_logs (url_scanned, risk_score, status, detected_threats) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sdss", $url, $risk_score, $status, $threats_json);
$stmt->execute();

// 3. Return JSON to Dashboard
echo json_encode([
    'url' => $url,
    'risk_score' => $risk_score,
    'status' => $status,
    'threats' => $threats
]);
?>