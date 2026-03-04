<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email) || empty($password)) {
        sendJson(['success' => false, 'message' => 'Email and password are required']);
    }

    if (strlen($password) < 6) {
        sendJson(['success' => false, 'message' => 'Password must be at least 6 characters']);
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            sendJson(['success' => false, 'message' => 'Email already exists']);
        }

        // Hash password and insert user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO users (email, name, password_hash) VALUES (?, ?, ?)"
        );
        $stmt->execute([$email, $name, $hashedPassword]);

        sendJson(['success' => true, 'message' => 'Registration successful']);

    } catch (Exception $e) {
        sendJson(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
} else {
    sendJson(['success' => false, 'message' => 'Invalid request method']);
}
?>
