<?php
/**
 * Contact Form API Endpoint
 * Handles contact form submissions with security and validation
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

// Rate limiting
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!checkRateLimit($clientIP, 5, 300)) { // 5 requests per 5 minutes
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']);
    logSecurityEvent('rate_limit_exceeded', 'Contact form rate limit exceeded', ['ip' => $clientIP]);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $requiredFields = ['name', 'email', 'message'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Sanitize and validate input
    $name = sanitizeInput($input['name']);
    $email = sanitizeInput($input['email']);
    $subject = isset($input['subject']) ? sanitizeInput($input['subject']) : '';
    $message = sanitizeInput($input['message']);
    
    // Validate email
    if (!validateEmail($email)) {
        throw new Exception('Invalid email address');
    }
    
    // Validate name length
    if (strlen($name) > 100) {
        throw new Exception('Name is too long (max 100 characters)');
    }
    
    // Validate message length
    if (strlen($message) > 5000) {
        throw new Exception('Message is too long (max 5000 characters)');
    }
    
    // Handle file upload if present
    $resumeFilename = null;
    $resumePath = null;
    
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['resume'];
        
        // Validate file size
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File size exceeds 5MB limit');
        }
        
        // Validate file type
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ALLOWED_FILE_TYPES)) {
            throw new Exception('Invalid file type. Only PDF, DOC, and DOCX files are allowed');
        }
        
        // Generate unique filename
        $resumeFilename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $resumePath = UPLOAD_DIR . $resumeFilename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $resumePath)) {
            throw new Exception('Failed to upload file');
        }
        
        logSecurityEvent('file_upload', 'Resume file uploaded', [
            'filename' => $resumeFilename,
            'size' => $file['size'],
            'type' => $file['type']
        ]);
    }
    
    // Get database connection
    $db = Database::getInstance()->getConnection();
    
    // Insert contact submission
    $stmt = $db->prepare("
        INSERT INTO contact_submissions 
        (name, email, subject, message, resume_filename, resume_path, ip_address, user_agent, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $name,
        $email,
        $subject,
        $message,
        $resumeFilename,
        $resumePath,
        $clientIP,
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
    
    $submissionId = $db->lastInsertId();
    
    // Log successful submission
    logSecurityEvent('form_submission', 'Contact form submitted successfully', [
        'submission_id' => $submissionId,
        'email' => $email,
        'subject' => $subject
    ]);
    
    // Send email notification (optional)
    if (function_exists('sendEmailNotification')) {
        sendEmailNotification($name, $email, $subject, $message, $resumePath);
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! I\'ll get back to you within 24 hours.',
        'submission_id' => $submissionId
    ]);
    
} catch (Exception $e) {
    // Log error
    logSecurityEvent('form_error', 'Contact form error: ' . $e->getMessage(), [
        'input' => $input ?? null
    ]);
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Send email notification to admin
 */
function sendEmailNotification($name, $email, $subject, $message, $resumePath = null) {
    try {
        // Email content
        $adminEmail = ADMIN_EMAIL;
        $emailSubject = "New Contact Form Submission - " . ($subject ?: 'No Subject');
        
        $emailBody = "
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Subject:</strong> " . ($subject ?: 'No Subject') . "</p>
        <p><strong>Message:</strong></p>
        <p>" . nl2br($message) . "</p>
        <p><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</p>
        ";
        
        if ($resumePath && file_exists($resumePath)) {
            $emailBody .= "<p><strong>Resume:</strong> Attached</p>";
        }
        
        // Email headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . APP_NAME . ' <noreply@' . parse_url(APP_URL, PHP_URL_HOST) . '>',
            'Reply-To: ' . $email,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Send email
        if (mail($adminEmail, $emailSubject, $emailBody, implode("\r\n", $headers))) {
            logSecurityEvent('email_sent', 'Contact form notification email sent', [
                'to' => $adminEmail,
                'from' => $email
            ]);
        } else {
            logSecurityEvent('email_failed', 'Failed to send contact form notification email');
        }
        
    } catch (Exception $e) {
        logSecurityEvent('email_error', 'Email notification error: ' . $e->getMessage());
    }
}
?>
