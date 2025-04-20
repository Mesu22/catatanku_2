<?php
require_once 'config/database.php';

function getTasks($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            id,
            user_id,
            title,
            description,
            date,
            priority,
            status,
            created_at,
            completed_at,
            status_overdue,
            overdue_at
        FROM tasks 
        WHERE user_id = ?
        ORDER BY date ASC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addTask($user_id, $task) {
    global $pdo;
    try {
        $sql = "INSERT INTO tasks (user_id, title, description, date, priority, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $user_id,
            $task['title'],
            $task['description'],
            $task['date'],
            $task['priority'],
            $task['status']
        ]);
    } catch (PDOException $e) {
        error_log("Error adding task: " . $e->getMessage());
        return false;
    }
}

function updateTask($task_id, $user_id, $task) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE tasks 
        SET 
            title = ?, 
            description = ?, 
            due_date = ?, 
            priority = ?, 
            status = ?,
            completed_at = CASE 
                WHEN status = 'completed' AND (SELECT status FROM tasks WHERE id = ?) != 'completed'
                THEN NOW()
                ELSE completed_at
            END
        WHERE id = ? AND user_id = ?
    ");
    return $stmt->execute([
        $task['title'],
        $task['description'],
        $task['date'],
        $task['priority'],
        $task['status'],
        $task_id,
        $task_id,
        $user_id
    ]);
}

function deleteTask($task_id, $user_id) {
    global $pdo;
    try {
        $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$task_id, $user_id]);
    } catch (PDOException $e) {
        error_log("Error deleting task: " . $e->getMessage());
        return false;
    }
}
?> 