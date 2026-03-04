<?php
require_once 'db.php';

$user_id = checkLogin(); // Ensure the user is logged in
$method = $_SERVER['REQUEST_METHOD'];

$data = json_decode(file_get_contents('php://input'), true); // decode JSON body

switch ($method) {

    case 'GET':
        // Get all categories for the user
        $stmt = $pdo->prepare("
            SELECT * 
            FROM categories 
            WHERE user_id = ?
            ORDER BY name ASC
        ");
        $stmt->execute([$user_id]);
        $categories = $stmt->fetchAll();

        sendJson(['success' => true, 'categories' => $categories]);
        break;

    case 'POST':
        $action = $data['action'] ?? '';

        if ($action === 'update') {
            // Update existing category
            $cat_id = $data['id'] ?? null;
            $name = trim($data['name'] ?? '');

            if (!$cat_id || $name === '') {
                sendJson(['success' => false, 'message' => 'Invalid category data.']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$name, $cat_id, $user_id]);

            sendJson(['success' => true, 'message' => 'Category updated successfully.']);
            exit;
        }

        // Create a new category
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            sendJson(['success' => false, 'message' => 'Category name is required.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO categories (user_id, name) VALUES (?, ?)");
            $stmt->execute([$user_id, $name]);

            $category_id = $pdo->lastInsertId();

            sendJson([
                'success' => true,
                'message' => 'Category created successfully.',
                'category_id' => $category_id,
            ]);
        } catch (Exception $e) {
            sendJson(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        sendJson(['success' => false, 'message' => 'Invalid request method.']);
}
?>
