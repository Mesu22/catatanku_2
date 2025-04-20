<?php
session_start();
require_once 'config/database.php';

// Suppress deprecated warnings
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

try {
    require_once 'vendor/autoload.php';

    // Get the raw POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    // Debug logging
    error_log('Received data: ' . print_r($data, true));

    if (!isset($data->credential)) {
        error_log('No credential provided in request');
        echo json_encode(['success' => false, 'message' => 'No credential provided']);
        exit;
    }

    // Initialize Google Client
    $client = new Google_Client();
    $client->setClientId('155224684798-v31obq4k8i3g171ure9c9ql9o145td37.apps.googleusercontent.com');

    try {
        // Verify the token
        $payload = $client->verifyIdToken($data->credential);
        
        if ($payload) {
            error_log('Token verified successfully. Payload: ' . print_r($payload, true));
            
            $google_id = $payload['sub'];
            $email = $payload['email'];
            $username = $payload['name']; // Changed from name to username

            try {
                // Check if user exists
                $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
                $stmt->execute([$google_id, $email]);
                $user = $stmt->fetch();

                if (!$user) {
                    // Create new user
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, google_id) VALUES (?, ?, ?)");
                    if (!$stmt->execute([$username, $email, $google_id])) {
                        error_log('Database Error during insert: ' . print_r($stmt->errorInfo(), true));
                        throw new Exception('Failed to create new user');
                    }
                    $user_id = $pdo->lastInsertId();
                } else {
                    $user_id = $user['id'];
                    // Update existing user
                    $stmt = $pdo->prepare("UPDATE users SET google_id = ?, email = ? WHERE id = ?");
                    if (!$stmt->execute([$google_id, $email, $user_id])) {
                        error_log('Database Error during update: ' . print_r($stmt->errorInfo(), true));
                        throw new Exception('Failed to update user');
                    }
                }

                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                echo json_encode(['success' => true]);
                exit;

            } catch (PDOException $e) {
                error_log('Database Error: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error occurred']);
                exit;
            }
        } else {
            error_log('Token verification failed');
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
    } catch (Exception $e) {
        error_log('Token Verification Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Token verification failed']);
        exit;
    }
} catch (Exception $e) {
    error_log('General Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Authentication error']);
    exit;
}
?>
