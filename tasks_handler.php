<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    $pdo->query('SELECT 1');

    switch ($action) {
        case 'read':
            try {
                $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC");
                $stmt->execute([$user_id]);
                $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Debug log
                error_log("Tasks fetched for user $user_id: " . print_r($tasks, true));
                
                echo json_encode([
                    'success' => true, 
                    'data' => $tasks,
                    'count' => count($tasks)
                ]);
            } catch (PDOException $e) {
                error_log("Error fetching tasks: " . $e->getMessage());
                echo json_encode([
                    'success' => false, 
                    'message' => 'Database error: ' . $e->getMessage()
                ]);
            }
            break;

        case 'create':
            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, priority, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['user_id'],
                $_POST['title'],
                $_POST['description'],
                $_POST['due_date'],
                $_POST['priority'],
                $_POST['status']
            ]);
            echo json_encode(['success' => true, 'message' => 'Task created successfully']);
            break;

        case 'update':
            $stmt = $pdo->prepare("UPDATE tasks SET 
                title = ?, 
                description = ?, 
                due_date = ?, 
                priority = ?, 
                status = ?, 
                updated_at = NOW() 
                WHERE id = ? AND user_id = ?");
            
            $result = $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['due_date'],
                $_POST['priority'],
                $_POST['status'],
                $_POST['id'],
                $user_id
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update task']);
            }
            break;

        case 'delete':
            try {
                $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
                $result = $stmt->execute([$_POST['id'], $user_id]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Task not found or unauthorized']);
                }
            } catch (PDOException $e) {
                error_log("Delete error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            break;

        case 'update_status':
            $stmt = $pdo->prepare("UPDATE tasks SET 
                status = ?, 
                updated_at = NOW(),
                overdue_at = CASE WHEN ? = 'overdue' THEN NOW() ELSE overdue_at END
                WHERE id = ?");
            $stmt->execute([
                $_POST['status'],
                $_POST['status'],
                $_POST['id']
            ]);
            
            // Ambil data task yang diupdate
            $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $updatedTask = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Task status updated successfully',
                'data' => $updatedTask
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Error in tasks_handler: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 