<?php
header('Content-Type: application/json');
require_once 'db.php';

$user_id = checkLogin();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
   case 'GET':
    $document_id = $_GET['id'] ?? null;
    $category_id = $_GET['category_id'] ?? null;

    // CASE 1: Get specific document (needs both ID and category_id)
    if ($document_id && $category_id) {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM documents 
            WHERE id = ? AND category_id = ? AND user_id = ?
        ");
        $stmt->execute([$document_id, $category_id, $user_id]);
        $document = $stmt->fetch();

        if ($document) {
            sendJson(['success' => true, 'document' => $document]);
        } else {
            sendJson(['success' => false, 'message' => 'Document not found in the specified category.']);
        }
    }
    // CASE 2: Get all documents for a category (only category_id)
    elseif ($category_id) {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM documents 
            WHERE category_id = ? AND user_id = ?
            ORDER BY title ASC
        ");
        $stmt->execute([$category_id, $user_id]);
        $documents = $stmt->fetchAll();
        
        sendJson(['success' => true, 'documents' => $documents]);
    }
    // CASE 3: Invalid request
    else {
        sendJson(['success' => false, 'message' => 'Document ID or Category ID is required.']);
    }
    break;

    case 'POST':
        // Create new document
        $data = json_decode(file_get_contents('php://input'), true);

        $title = trim($data['title'] ?? 'Untitled');
        $content = $data['content'] ?? '';
        $category_id = $data['category_id'] ?? null;

        if (!$category_id) {
            sendJson(['success' => false, 'message' => 'No active category is found.']);
        }

        $stmt = $pdo->prepare("
            INSERT INTO documents (user_id, category_id, title, content, updated_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $user_id,
            $category_id,
            $title,
            $content,
        ]);

        $document_id = $pdo->lastInsertId();

        // Get the created document
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt->execute([$document_id]);
        $document = $stmt->fetch();

        sendJson([
            'success' => true, 
            'message' => 'Document created',
            'document' => $document
        ]);
        break;

    case 'PUT':
        // Update document
        $data = json_decode(file_get_contents('php://input'), true);
        $document_id = $data['id'] ?? null;
        $category_id = $data['category_id'] ?? null;

        $title = trim($data['title'] ?? '');
        $content = $data['content'] ?? '';

        if (empty($document_id) || empty($category_id)) {
            sendJson(['success' => false, 'message' => 'No active document or category is found.']);
        }

        $stmt = $pdo->prepare("
            UPDATE documents 
            SET title = ?, content = ?, updated_at = NOW() 
            WHERE id = ? AND category_id = ? AND user_id = ?
        ");
        
        $stmt->execute([$title, $content, $document_id, $category_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            sendJson(['success' => true, 'message' => 'Document updated']);
        } else {
            sendJson(['success' => false, 'message' => 'Failed to update document']);
        }
        break;

    case 'DELETE':
        // Delete document
        $document_id = $_GET['id'] ?? null;
        $category_id = $_GET['category_id'] ?? null;

        if (empty($document_id) || empty($category_id)) {
            sendJson(['success' => false, 'message' => 'No active document or category is found.']);
        }

        $stmt = $pdo->prepare("
            DELETE FROM documents 
            WHERE id = ? AND category_id = ? AND user_id = ?
        ");
        $stmt->execute([$document_id, $category_id, $user_id]);

        sendJson(['success' => true, 'message' => 'Document deleted']);
        break;

    default:
        sendJson(['success' => false, 'message' => 'Invalid request method']);
}
?>