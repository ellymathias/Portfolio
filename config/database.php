<?php
/**
 * Database Configuration
 * Portfolio Backend - Database Connection Settings
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root'); // Change this to your MySQL username
define('DB_PASS', ''); // Change this to your MySQL password
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'Elia Mathias Portfolio');
define('APP_URL', 'http://localhost/Portfolio'); // Update with your actual URL
define('APP_EMAIL', 'esonenga@gmail.com');
define('ADMIN_EMAIL', 'esonenga@gmail.com'); // Email to receive contact form submissions

// Security settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx']);

// Email settings (for SMTP)
define('SMTP_HOST', 'smtp.gmail.com'); // Update with your SMTP server
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Update with your email
define('SMTP_PASSWORD', 'your-app-password'); // Update with your app password
define('SMTP_ENCRYPTION', 'tls');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');

// Rate limiting (requests per minute)
define('RATE_LIMIT_REQUESTS', 10);
define('RATE_LIMIT_WINDOW', 60); // seconds

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

/**
 * Database connection class
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function __clone() {
        throw new Exception("Cannot clone a singleton.");
    }
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize a singleton.");
    }
}

/**
 * Utility functions
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function logSecurityEvent($eventType, $description, $additionalData = null) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO security_logs (event_type, description, ip_address, user_agent, additional_data, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $eventType,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $additionalData ? json_encode($additionalData) : null
        ]);
    } catch (Exception $e) {
        // Log error but don't expose it to user
        error_log("Security log error: " . $e->getMessage());
    }
}

function checkRateLimit($identifier, $limit = RATE_LIMIT_REQUESTS, $window = RATE_LIMIT_WINDOW) {
    $db = Database::getInstance()->getConnection();
    
    // Clean old entries
    $db->prepare("DELETE FROM analytics_events WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)")
       ->execute([$window]);
    
    // Count recent requests
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM analytics_events 
        WHERE event_type = 'rate_limit_check' 
        AND event_label = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");
    $stmt->execute([$identifier, $window]);
    $result = $stmt->fetch();
    
    if ($result['count'] >= $limit) {
        return false;
    }
    
    // Log this request
    $stmt = $db->prepare("
        INSERT INTO analytics_events (event_type, event_category, event_label, ip_address, user_agent, created_at) 
        VALUES ('rate_limit_check', 'security', ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $identifier,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
    
    return true;
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
?>
