<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        sendJson(['success' => false, 'message' => 'Username and password are required']);
    }

    try {
        // Find user by email
        $stmt = $pdo->prepare(
            "SELECT id, email, password_hash FROM users WHERE email = ?"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            sendJson(['success' => false, 'message' => 'Invalid email or password']);
        }

        // ✅ Correct column name
        if (!password_verify($password, $user['password_hash'])) {
            sendJson(['success' => false, 'message' => 'Invalid email or password']);
        }

        // ✅ Correct session values
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];

        sendJson([
            'success' => true,
            'message' => 'Login successful',
            'user_id' => $user['id'],
            'name' => $user['name']
        ]);

    } catch (Exception $e) {
        sendJson(['success' => false, 'message' => 'Login failed: ' . $e->getMessage()]);
    }
} else {
    sendJson(['success' => false, 'message' => 'Invalid request method']);
}
?>
