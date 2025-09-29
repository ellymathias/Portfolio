<?php
/**
 * Analytics API Endpoint
 * Handles analytics tracking and data collection
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle analytics event tracking
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON input');
        }
        
        // Validate required fields
        if (empty($input['event_type'])) {
            throw new Exception('event_type is required');
        }
        
        // Sanitize input
        $eventType = sanitizeInput($input['event_type']);
        $eventCategory = isset($input['event_category']) ? sanitizeInput($input['event_category']) : null;
        $eventLabel = isset($input['event_label']) ? sanitizeInput($input['event_label']) : null;
        $eventValue = isset($input['event_value']) ? sanitizeInput($input['event_value']) : null;
        $pageUrl = isset($input['page_url']) ? sanitizeInput($input['page_url']) : null;
        $referrer = isset($input['referrer']) ? sanitizeInput($input['referrer']) : null;
        $sessionId = isset($input['session_id']) ? sanitizeInput($input['session_id']) : null;
        
        // Get client information
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Insert analytics event
        $stmt = $db->prepare("
            INSERT INTO analytics_events 
            (event_type, event_category, event_label, event_value, page_url, referrer, ip_address, user_agent, session_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $eventType,
            $eventCategory,
            $eventLabel,
            $eventValue,
            $pageUrl,
            $referrer,
            $ipAddress,
            $userAgent,
            $sessionId
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Analytics event recorded'
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Handle analytics data retrieval (for admin dashboard)
        $action = $_GET['action'] ?? 'summary';
        
        switch ($action) {
            case 'summary':
                // Get analytics summary
                $summary = [];
                
                // Total page views
                $stmt = $db->query("SELECT COUNT(*) as total FROM page_views");
                $summary['total_page_views'] = $stmt->fetch()['total'];
                
                // Total contact submissions
                $stmt = $db->query("SELECT COUNT(*) as total FROM contact_submissions");
                $summary['total_contact_submissions'] = $stmt->fetch()['total'];
                
                // Recent events
                $stmt = $db->query("
                    SELECT event_type, COUNT(*) as count 
                    FROM analytics_events 
                    WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) 
                    GROUP BY event_type 
                    ORDER BY count DESC
                ");
                $summary['recent_events'] = $stmt->fetchAll();
                
                // Top pages
                $stmt = $db->query("
                    SELECT page_url, COUNT(*) as views 
                    FROM page_views 
                    WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) 
                    GROUP BY page_url 
                    ORDER BY views DESC 
                    LIMIT 10
                ");
                $summary['top_pages'] = $stmt->fetchAll();
                
                echo json_encode([
                    'success' => true,
                    'data' => $summary
                ]);
                break;
                
            case 'contact_submissions':
                // Get contact submissions (for admin)
                $page = max(1, intval($_GET['page'] ?? 1));
                $limit = min(50, max(10, intval($_GET['limit'] ?? 20)));
                $offset = ($page - 1) * $limit;
                
                $stmt = $db->prepare("
                    SELECT id, name, email, subject, message, created_at, status 
                    FROM contact_submissions 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute([$limit, $offset]);
                $submissions = $stmt->fetchAll();
                
                // Get total count
                $stmt = $db->query("SELECT COUNT(*) as total FROM contact_submissions");
                $total = $stmt->fetch()['total'];
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'submissions' => $submissions,
                        'pagination' => [
                            'page' => $page,
                            'limit' => $limit,
                            'total' => $total,
                            'pages' => ceil($total / $limit)
                        ]
                    ]
                ]);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
