<?php
session_start();
require_once 'config/database.php';

// Periksa apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Terima data JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    // Persiapkan query update
    $sql = "UPDATE tasks 
            SET title = ?, 
                description = ?, 
                status = ?, 
                priority = ?,
                due_date = ?
            WHERE id = ? AND user_id = ?";
    
    $stmt = $pdo->prepare($sql);
    
    // Execute query dengan parameter
    $result = $stmt->execute([
        $data['title'],
        $data['description'],
        $data['status'],
        $data['priority'],
        $data['date'],
        $data['id'],
        $_SESSION['user_id']
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Task updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update task'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
