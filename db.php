<?php
// db.php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'docspace';
$username = 'root';
$password = '';

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
}

// Helper function to send JSON response
function sendJson($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Check if user is logged in
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        sendJson(['success' => false, 'message' => 'Please login first']);
    }
    return $_SESSION['user_id'];
}

// Get current user ID from session (for web pages)
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>