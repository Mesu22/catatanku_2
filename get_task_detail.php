<?php
require_once 'config/database.php';
session_start();

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Task ID not provided']);
    exit();
}

$task_id = $_GET['id'];

$task_query = "SELECT * FROM tasks WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($task_query);
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    http_response_code(404);
    echo json_encode(['error' => 'Task not found']);
    exit();
}

echo json_encode($task); 