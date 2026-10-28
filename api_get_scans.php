<?php
// api_get_scans.php
header('Content-Type: application/json');
require_once 'db.php';

$result = $conn->query("SELECT url_scanned, risk_score, status, scanned_at FROM scan_logs ORDER BY id DESC LIMIT 5");

$scans = [];
while ($row = $result->fetch_assoc()) {
    $scans[] = $row;
}

echo json_encode($scans);
?>