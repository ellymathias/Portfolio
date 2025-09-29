<?php
/**
 * View Contact Submission Details
 */

require_once '../config/database.php';

// Check authentication
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

$submissionId = intval($_GET['id'] ?? 0);

if (!$submissionId) {
    header('Location: index.php');
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get submission details
    $stmt = $db->prepare("
        SELECT * FROM contact_submissions WHERE id = ?
    ");
    $stmt->execute([$submissionId]);
    $submission = $stmt->fetch();
    
    if (!$submission) {
        header('Location: index.php');
        exit();
    }
    
    // Update status to 'read' if it's new
    if ($submission['status'] === 'new') {
        $stmt = $db->prepare("UPDATE contact_submissions SET status = 'read' WHERE id = ?");
        $stmt->execute([$submissionId]);
        $submission['status'] = 'read';
    }
    
    // Handle status update
    if ($_POST['action'] === 'update_status') {
        $newStatus = $_POST['status'];
        $stmt = $db->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $submissionId]);
        $submission['status'] = $newStatus;
    }
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submission - Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #4f8cff; color: white; padding: 20px; }
        .header h1 { margin-bottom: 10px; }
        .back-link { float: right; margin-top: -30px; }
        .back-link a { color: white; text-decoration: none; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .submission-card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .submission-header { padding: 20px; border-bottom: 1px solid #eee; }
        .submission-content { padding: 20px; }
        .field { margin-bottom: 20px; }
        .field-label { font-weight: bold; color: #333; margin-bottom: 5px; }
        .field-value { color: #666; }
        .message-content { background: #f8f9fa; padding: 15px; border-radius: 4px; white-space: pre-wrap; }
        .status-form { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { background: #4f8cff; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #3a7bff; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .resume-link { color: #4f8cff; text-decoration: none; }
        .resume-link:hover { text-decoration: underline; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Contact Submission Details</h1>
        <div class="back-link">
            <a href="index.php">← Back to Dashboard</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="submission-card">
            <div class="submission-header">
                <h2>Submission #<?php echo $submission['id']; ?></h2>
                <p>Received: <?php echo date('F j, Y \a\t g:i A', strtotime($submission['created_at'])); ?></p>
            </div>
            
            <div class="submission-content">
                <div class="field">
                    <div class="field-label">Name:</div>
                    <div class="field-value"><?php echo htmlspecialchars($submission['name']); ?></div>
                </div>
                
                <div class="field">
                    <div class="field-label">Email:</div>
                    <div class="field-value">
                        <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>">
                            <?php echo htmlspecialchars($submission['email']); ?>
                        </a>
                    </div>
                </div>
                
                <?php if ($submission['subject']): ?>
                <div class="field">
                    <div class="field-label">Subject:</div>
                    <div class="field-value"><?php echo htmlspecialchars($submission['subject']); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="field">
                    <div class="field-label">Message:</div>
                    <div class="message-content"><?php echo htmlspecialchars($submission['message']); ?></div>
                </div>
                
                <?php if ($submission['resume_filename']): ?>
                <div class="field">
                    <div class="field-label">Resume:</div>
                    <div class="field-value">
                        <a href="../uploads/<?php echo htmlspecialchars($submission['resume_filename']); ?>" 
                           class="resume-link" target="_blank">
                            <?php echo htmlspecialchars($submission['resume_filename']); ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="field">
                    <div class="field-label">IP Address:</div>
                    <div class="field-value"><?php echo htmlspecialchars($submission['ip_address']); ?></div>
                </div>
                
                <div class="field">
                    <div class="field-label">User Agent:</div>
                    <div class="field-value"><?php echo htmlspecialchars($submission['user_agent']); ?></div>
                </div>
                
                <!-- Status Update Form -->
                <div class="status-form">
                    <h3>Update Status</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <div class="form-group">
                            <label for="status">Current Status:</label>
                            <select name="status" id="status">
                                <option value="new" <?php echo $submission['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="read" <?php echo $submission['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="replied" <?php echo $submission['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                <option value="archived" <?php echo $submission['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                        <button type="submit" class="btn">Update Status</button>
                    </form>
                </div>
                
                <!-- Quick Actions -->
                <div style="margin-top: 20px;">
                    <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>?subject=Re: <?php echo urlencode($submission['subject'] ?: 'Your Message'); ?>" 
                       class="btn">Reply via Email</a>
                    <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
