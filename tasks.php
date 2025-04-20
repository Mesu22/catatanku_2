<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

// Read tasks
if ($action === 'read') {
    $sql = "SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $tasks]);
    exit;
}

// Create task
if ($action === 'create') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority']; // Pastikan menerima prioritas
    $status = $_POST['status'];
    
    // Tambahkan prioritas ke SQL
    $sql = "INSERT INTO tasks (title, description, due_date, priority, status, user_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$title, $description, $due_date, $priority, $status, $user_id])) {
        echo json_encode(['success' => true, 'message' => 'Task created']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create task']);
    }
    exit;
}

// Update task
if ($action === 'update') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority']; // Pastikan menerima prioritas
    $status = $_POST['status'];
    
    // Tambahkan prioritas ke SQL update
    $sql = "UPDATE tasks SET title = ?, description = ?, due_date = ?, priority = ?, status = ?, updated_at = NOW() 
            WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$title, $description, $due_date, $priority, $status, $id, $user_id])) {
        echo json_encode(['success' => true, 'message' => 'Task updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update task']);
    }
    exit;
}

// Delete task
if ($action === 'delete') {
    $id = $_POST['id'];
    
    $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id, $user_id])) {
        echo json_encode(['success' => true, 'message' => 'Task deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?> 