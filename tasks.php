<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$response = ['success' => false, 'message' => '', 'data' => null];

// Create Task
if ($_POST['action'] === 'create') {
    try {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $_POST['title'],
            $_POST['description'],
            $_POST['due_date'],
            $_POST['status']
        ]);
        
        $response['success'] = true;
        $response['message'] = 'Task created successfully';
        $response['data'] = ['id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        $response['message'] = 'Error creating task: ' . $e->getMessage();
    }
}

// Read Tasks
if ($_POST['action'] === 'read') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['data'] = $tasks;
    } catch (PDOException $e) {
        $response['message'] = 'Error fetching tasks: ' . $e->getMessage();
    }
}

// Update Task
if ($_POST['action'] === 'update') {
    try {
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ?, status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['due_date'],
            $_POST['status'],
            $_POST['id'],
            $_SESSION['user_id']
        ]);
        
        $response['success'] = true;
        $response['message'] = 'Task updated successfully';
    } catch (PDOException $e) {
        $response['message'] = 'Error updating task: ' . $e->getMessage();
    }
}

// Delete Task
if ($_POST['action'] === 'delete') {
    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['id'], $_SESSION['user_id']]);
        
        $response['success'] = true;
        $response['message'] = 'Task deleted successfully';
    } catch (PDOException $e) {
        $response['message'] = 'Error deleting task: ' . $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?> 