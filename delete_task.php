<?php
session_start();
require_once 'config/database.php';

// Periksa apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Ambil data JSON dari request body
$data = json_decode(file_get_contents('php://input'), true);

// Validasi ID tugas
if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit;
}

try {
    // Siapkan query delete dengan user_id untuk keamanan
    $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    
    // Execute query
    $result = $stmt->execute([
        $data['id'],
        $_SESSION['user_id']
    ]);

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Task not found or not authorized to delete'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
