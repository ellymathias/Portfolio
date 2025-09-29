<?php
/**
 * Admin Dashboard
 * View contact submissions and analytics
 */

require_once '../config/database.php';

// Simple authentication (in production, use proper authentication)
session_start();
$adminPassword = 'admin123'; // Change this to a secure password

if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['password']) && $_POST['password'] === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        // Show login form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login - Portfolio Dashboard</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
                .login-container { max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .form-group { margin-bottom: 20px; }
                label { display: block; margin-bottom: 5px; font-weight: bold; }
                input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
                button { background: #4f8cff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
                button:hover { background: #3a7bff; }
                .error { color: red; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h2>Admin Login</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit">Login</button>
                    <?php if (isset($_POST['password'])): ?>
                        <div class="error">Invalid password</div>
                    <?php endif; ?>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}

// Get analytics data
try {
    $db = Database::getInstance()->getConnection();
    
    // Get summary statistics
    $stats = [];
    
    // Total page views
    $stmt = $db->query("SELECT COUNT(*) as total FROM page_views");
    $stats['total_views'] = $stmt->fetch()['total'];
    
    // Total contact submissions
    $stmt = $db->query("SELECT COUNT(*) as total FROM contact_submissions");
    $stats['total_submissions'] = $stmt->fetch()['total'];
    
    // New submissions (last 7 days)
    $stmt = $db->query("SELECT COUNT(*) as total FROM contact_submissions WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_submissions'] = $stmt->fetch()['total'];
    
    // Recent submissions
    $stmt = $db->query("
        SELECT id, name, email, subject, created_at, status 
        FROM contact_submissions 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $recentSubmissions = $stmt->fetchAll();
    
    // Top pages
    $stmt = $db->query("
        SELECT page_url, COUNT(*) as views 
        FROM page_views 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) 
        GROUP BY page_url 
        ORDER BY views DESC 
        LIMIT 10
    ");
    $topPages = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Portfolio</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #4f8cff; color: white; padding: 20px; }
        .header h1 { margin-bottom: 10px; }
        .logout { float: right; margin-top: -30px; }
        .logout a { color: white; text-decoration: none; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2em; font-weight: bold; color: #4f8cff; }
        .stat-label { color: #666; margin-top: 5px; }
        .section { background: white; margin-bottom: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section-header { padding: 20px; border-bottom: 1px solid #eee; }
        .section-content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: bold; }
        .status-new { color: #007bff; }
        .status-read { color: #28a745; }
        .status-replied { color: #6f42c1; }
        .status-archived { color: #6c757d; }
        .btn { background: #4f8cff; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #3a7bff; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Portfolio Admin Dashboard</h1>
        <div class="logout">
            <a href="?logout=1">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_views'] ?? 0); ?></div>
                <div class="stat-label">Total Page Views</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_submissions'] ?? 0); ?></div>
                <div class="stat-label">Total Contact Submissions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['recent_submissions'] ?? 0); ?></div>
                <div class="stat-label">Submissions (Last 7 Days)</div>
            </div>
        </div>
        
        <!-- Recent Contact Submissions -->
        <div class="section">
            <div class="section-header">
                <h2>Recent Contact Submissions</h2>
            </div>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSubmissions as $submission): ?>
                        <tr>
                            <td><?php echo $submission['id']; ?></td>
                            <td><?php echo htmlspecialchars($submission['name']); ?></td>
                            <td><?php echo htmlspecialchars($submission['email']); ?></td>
                            <td><?php echo htmlspecialchars($submission['subject'] ?: 'No Subject'); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($submission['created_at'])); ?></td>
                            <td><span class="status-<?php echo $submission['status']; ?>"><?php echo ucfirst($submission['status']); ?></span></td>
                            <td>
                                <a href="view.php?id=<?php echo $submission['id']; ?>" class="btn">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Top Pages -->
        <div class="section">
            <div class="section-header">
                <h2>Top Pages (Last 7 Days)</h2>
            </div>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>Page URL</th>
                            <th>Views</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topPages as $page): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($page['page_url']); ?></td>
                            <td><?php echo number_format($page['views']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php
    // Handle logout
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: index.php');
        exit();
    }
    ?>
</body>
</html>
