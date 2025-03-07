<?php
require_once 'task_operations.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'create':
                $title = $_POST['title'];
                $description = $_POST['description'];
                $due_date = $_POST['due_date'];
                $status = $_POST['status'];
                
                if (createTask($title, $description, $due_date, $status)) {
                    echo json_encode(['success' => true, 'message' => 'Task created successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create task']);
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                $due_date = $_POST['due_date'];
                $status = $_POST['status'];
                
                if (updateTask($id, $title, $description, $due_date, $status)) {
                    echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update task']);
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                
                if (deleteTask($id)) {
                    echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
                }
                break;
        }
    }
}
?> 