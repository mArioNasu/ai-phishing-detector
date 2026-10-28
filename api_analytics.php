<?php
// api_analytics.php
header('Content-Type: application/json');
require_once 'db.php';

// Fetch breakdown by status
$status_counts = ['Safe' => 0, 'Suspicious' => 0, 'Malicious' => 0];
$result = $conn->query("SELECT status, COUNT(*) as count FROM scan_logs GROUP BY status");

while ($row = $result->fetch_assoc()) {
    $status_counts[$row['status']] = (int)$row['count'];
}

// Fetch total count and average risk score
$summary = $conn->query("SELECT COUNT(*) as total, AVG(risk_score) as avg_risk FROM scan_logs")->fetch_assoc();

echo json_encode([
    'total_scans' => (int)($summary['total'] ?? 0),
    'avg_risk' => round((float)($summary['avg_risk'] ?? 0), 2),
    'status_counts' => $status_counts
]);
?>