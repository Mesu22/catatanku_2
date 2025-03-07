<?php
require_once '../config/database.php';

// Create Task
function createTask($title, $description, $due_date, $status) {
    global $pdo;
    $sql = "INSERT INTO tasks (title, description, due_date, status) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$title, $description, $due_date, $status]);
}

// Read Tasks
function getAllTasks() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Update Task
function updateTask($id, $title, $description, $due_date, $status) {
    global $pdo;
    $sql = "UPDATE tasks SET title = ?, description = ?, due_date = ?, status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$title, $description, $due_date, $status, $id]);
}

// Delete Task
function deleteTask($id) {
    global $pdo;
    $sql = "DELETE FROM tasks WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}

// Get Single Task
function getTask($id) {
    global $pdo;
    $sql = "SELECT * FROM tasks WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?> 