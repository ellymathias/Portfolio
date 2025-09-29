<?php
/**
 * Page View Tracking API
 * Tracks page views and user interactions
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once '../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    if (empty($input['page_url'])) {
        throw new Exception('page_url is required');
    }
    
    // Sanitize input
    $pageUrl = sanitizeInput($input['page_url']);
    $pageTitle = isset($input['page_title']) ? sanitizeInput($input['page_title']) : null;
    $referrer = isset($input['referrer']) ? sanitizeInput($input['referrer']) : null;
    $sessionId = isset($input['session_id']) ? sanitizeInput($input['session_id']) : null;
    $viewDuration = isset($input['view_duration']) ? intval($input['view_duration']) : 0;
    
    // Get client information
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Get database connection
    $db = Database::getInstance()->getConnection();
    
    // Insert page view
    $stmt = $db->prepare("
        INSERT INTO page_views 
        (page_url, page_title, referrer, ip_address, user_agent, session_id, view_duration, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $pageUrl,
        $pageTitle,
        $referrer,
        $ipAddress,
        $userAgent,
        $sessionId,
        $viewDuration
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Page view tracked'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
