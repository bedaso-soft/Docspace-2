<?php
// callback.php — PURE PHP GOOGLE OAUTH BACKEND

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/google_config.php';

if (!isset($_GET['code'])) {
    http_response_code(400);
    die('Authorization code missing.');
}

$code = $_GET['code'];


// --------------------------------------------------
// 1. Exchange AUTH CODE → ACCESS TOKEN
// --------------------------------------------------
$tokenUrl = 'https://oauth2.googleapis.com/token';

$postData = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
curl_close($ch);

$token = json_decode($response, true);

if (!isset($token['access_token'])) {
    error_log('Token error: ' . $response);
    http_response_code(400);
    die('Failed to get access token.');
}

$accessToken = $token['access_token'];


// --------------------------------------------------
// 2. FETCH USER PROFILE
// --------------------------------------------------
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

$ch = curl_init($userInfoUrl);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken
    ],
    CURLOPT_RETURNTRANSFER => true
]);

$userResponse = curl_exec($ch);
curl_close($ch);

$user = json_decode($userResponse, true);

if (!isset($user['id'], $user['email'])) {
    error_log('Userinfo error: ' . $userResponse);
    http_response_code(400);
    die('Failed to retrieve user info.');
}

$google_id = $user['id'];
$email     = $user['email'];
$name      = $user['name'] ?? '';


// --------------------------------------------------
// 3. STORE / UPDATE USER IN DATABASE
// --------------------------------------------------
$pdo->beginTransaction();

$stmt = $pdo->prepare("
    SELECT id FROM users 
    WHERE google_id = :gid OR email = :email 
    LIMIT 1
");
$stmt->execute([
    ':gid' => $google_id,
    ':email' => $email
]);

$existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
$tokenJson = json_encode($token);

if ($existingUser) {
    $stmt = $pdo->prepare("
        UPDATE users 
        SET google_id = :gid,
            google_token = :token,
            name = COALESCE(name, :name)
        WHERE id = :id
    ");
    $stmt->execute([
        ':gid' => $google_id,
        ':token' => $tokenJson,
        ':name' => $name,
        ':id' => $existingUser['id']
    ]);
    $userId = $existingUser['id'];
} else {
    $stmt = $pdo->prepare("
        INSERT INTO users (email, google_id, name, google_token)
        VALUES (:email, :gid, :name, :token)
    ");
    $stmt->execute([
        ':email' => $email,
        ':gid' => $google_id,
        ':name' => $name,
        ':token' => $tokenJson
    ]);
    $userId = $pdo->lastInsertId();
}

$pdo->commit();


// --------------------------------------------------
// 4. LOGIN USER
// --------------------------------------------------
$_SESSION['user_id'] = $userId;
$_SESSION['email']   = $email;


// --------------------------------------------------
// 5. REDIRECT
// --------------------------------------------------
header('Location: editor.html');
exit;
