<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// API Configuration
$API_BASE = 'https://random-d.uk/api/v2';

// Get request parameters
$action = $_GET['action'] ?? '';

function fetchAPI($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return $response;
    }
    
    return json_encode(['error' => 'API request failed', 'code' => $httpCode]);
}

// Handle different actions
switch ($action) {
    case 'random':
        $type = $_GET['type'] ?? 'jpg';
        $type = strtoupper($type);
        $url = "$API_BASE/random?type=$type";
        echo fetchAPI($url);
        break;
        
    case 'list':
        $url = "$API_BASE/list";
        echo fetchAPI($url);
        break;
        
    case 'http':
        $code = $_GET['code'] ?? '404';
        // For HTTP status code images, return the direct image URL
        echo json_encode([
            'url' => "https://random-d.uk/api/http/$code",
            'code' => $code
        ]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
