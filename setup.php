<?php
/**
 * Portfolio Setup Script
 * Run this script to set up the database and check configuration
 */

echo "<h1>Portfolio Setup</h1>";

// Check if database configuration exists
if (!file_exists('config/database.php')) {
    echo "<p style='color: red;'>Error: config/database.php not found. Please create it first.</p>";
    exit();
}

require_once 'config/database.php';

echo "<h2>Configuration Check</h2>";

// Check database connection
try {
    $db = Database::getInstance()->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in config/database.php</p>";
    exit();
}

// Check if tables exist
$tables = ['contact_submissions', 'analytics_events', 'page_views', 'security_logs'];
$missingTables = [];

foreach ($tables as $table) {
    try {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            $missingTables[] = $table;
        } else {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        }
    } catch (Exception $e) {
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    echo "<h3>Missing Tables</h3>";
    echo "<p style='color: orange;'>The following tables are missing:</p>";
    echo "<ul>";
    foreach ($missingTables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    echo "<p>Please run the SQL script in database/schema.sql to create the missing tables.</p>";
}

// Check upload directory
if (!file_exists(UPLOAD_DIR)) {
    if (mkdir(UPLOAD_DIR, 0755, true)) {
        echo "<p style='color: green;'>✓ Upload directory created: " . UPLOAD_DIR . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create upload directory: " . UPLOAD_DIR . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Upload directory exists: " . UPLOAD_DIR . "</p>";
}

// Check if upload directory is writable
if (is_writable(UPLOAD_DIR)) {
    echo "<p style='color: green;'>✓ Upload directory is writable</p>";
} else {
    echo "<p style='color: red;'>✗ Upload directory is not writable</p>";
}

// Test API endpoints
echo "<h2>API Endpoints Test</h2>";

$endpoints = [
    'api/contact.php' => 'Contact Form API',
    'api/analytics.php' => 'Analytics API',
    'api/track.php' => 'Page Tracking API'
];

foreach ($endpoints as $endpoint => $name) {
    if (file_exists($endpoint)) {
        echo "<p style='color: green;'>✓ $name ($endpoint)</p>";
    } else {
        echo "<p style='color: red;'>✗ $name ($endpoint) not found</p>";
    }
}

// Check admin dashboard
if (file_exists('admin/index.php')) {
    echo "<p style='color: green;'>✓ Admin dashboard (admin/index.php)</p>";
} else {
    echo "<p style='color: red;'>✗ Admin dashboard not found</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Update your database credentials in config/database.php</li>";
echo "<li>Run the SQL script in database/schema.sql to create tables</li>";
echo "<li>Update email settings in config/database.php for contact form notifications</li>";
echo "<li>Change the admin password in admin/index.php</li>";
echo "<li>Test the contact form and admin dashboard</li>";
echo "</ol>";

echo "<h2>Admin Access</h2>";
echo "<p>Admin dashboard: <a href='admin/index.php'>admin/index.php</a></p>";
echo "<p>Default password: admin123 (change this!)</p>";

echo "<h2>Security Recommendations</h2>";
echo "<ul>";
echo "<li>Change the admin password immediately</li>";
echo "<li>Use HTTPS in production</li>";
echo "<li>Regularly backup your database</li>";
echo "<li>Monitor the security_logs table for suspicious activity</li>";
echo "<li>Keep your PHP and MySQL versions updated</li>";
echo "</ul>";
?>
