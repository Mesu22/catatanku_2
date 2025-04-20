<?php
session_start();
require_once 'config/database.php';

// Pastikan request menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Ambil data JSON dari request body
$data = json_decode(file_get_contents('php://input'), true);

// Validasi data yang diperlukan
if (empty($data['title']) || empty($data['date'])) {
    echo json_encode(['success' => false, 'message' => 'Title and date are required']);
    exit;
}

try {
    // Siapkan query untuk insert
    $sql = "INSERT INTO tasks (user_id, title, description, due_date, status, priority, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    
    // Execute query dengan data yang diterima
    $result = $stmt->execute([
        $_SESSION['user_id'],
        $data['title'],
        $data['description'] ?? '',
        $data['date'],
        $data['status'] ?? 'not_started',
        $data['priority'] ?? 'medium'
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'task_id' => $pdo->lastInsertId(),
            'message' => 'Task added successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add task'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
