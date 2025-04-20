<?php
session_start();

// Refresh user data from database
require_once 'config/database.php';
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userData) {
    // Update session dengan data terbaru dari database
    $_SESSION['username'] = $userData['username'];
    $_SESSION['email'] = $userData['email'];
    
    // Debug log
    error_log("Session initialized with username: " . $userData['username']);
}

// Debug session
error_log("Current session username: " . ($_SESSION['username'] ?? 'not set'));
error_log("Current session user_id: " . ($_SESSION['user_id'] ?? 'not set'));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check theme preference
if (!isset($_SESSION['theme_preference'])) {
    require_once 'config/database.php';
    $stmt = $pdo->prepare("SELECT theme_preference FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $theme = $stmt->fetchColumn();
    $_SESSION['theme_preference'] = $theme ?: 'light';
}

// Handle theme update
if (isset($_POST['update_theme'])) {
    $new_theme = $_POST['theme'];
    require_once 'config/database.php';
    
    $stmt = $pdo->prepare("UPDATE users SET theme_preference = ? WHERE id = ?");
    if ($stmt->execute([$new_theme, $_SESSION['user_id']])) {
        $_SESSION['theme_preference'] = $new_theme;
        $_SESSION['success'] = "Theme updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating theme";
    }
    header("Location: settings.php");
    exit();
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    require_once 'config/database.php';
    
    try {
        // Cek apakah username sudah ada (kecuali untuk user yang sedang login)
        if (!empty($username)) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $_SESSION['user_id']]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "Username sudah digunakan";
                header("Location: settings.php");
                exit();
            }
        }
        
        // Persiapkan query dan parameter
        $sql = "UPDATE users SET";
        $params = [];
        $updates = [];
        
        // Hanya update field yang diisi
        if (!empty($username)) {
            $updates[] = " username = ?";
            $params[] = $username;
        }
        
        if (!empty($email)) {
            $updates[] = " email = ?";
            $params[] = $email;
        }
        
        // Jika tidak ada yang diupdate
        if (empty($updates)) {
            $_SESSION['error'] = "Tidak ada perubahan yang dilakukan";
            header("Location: settings.php");
            exit();
        }
        
        $sql .= implode(',', $updates);
        $sql .= " WHERE id = ?";
        $params[] = $_SESSION['user_id'];
        
        // Execute update
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($params)) {
            // Refresh data from database after update
            $refreshStmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
            $refreshStmt->execute([$_SESSION['user_id']]);
            $refreshData = $refreshStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($refreshData) {
                $_SESSION['username'] = $refreshData['username'];
                $_SESSION['email'] = $refreshData['email'];
                error_log("Session refreshed after update. Username: " . $refreshData['username']);
            }
            
            $_SESSION['success'] = "Profile berhasil diperbarui!";
        } else {
            $_SESSION['error'] = "Gagal memperbarui profil";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        error_log("Database error in profile update: " . $e->getMessage());
    }
    
    header("Location: settings.php");
    exit();
}


// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    require_once 'config/database.php';
    
    // Validasi input
    if (empty($current_password)) {
        $_SESSION['error'] = "Current password is required";
        header("Location: settings.php");
        exit();
    }
    
    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "New password and confirmation are required";
        header("Location: settings.php");
        exit();
    }
    
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New password and confirmation do not match";
        header("Location: settings.php");
        exit();
    }
    
    try {
        // Verifikasi password saat ini
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $stored_password = $stmt->fetchColumn();
        
        if (password_verify($current_password, $stored_password)) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                $_SESSION['success'] = "Password updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating password";
            }
        } else {
            $_SESSION['error'] = "Current password is incorrect";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
    
    header("Location: settings.php");
    exit();
}

// Di bagian atas file, setelah session_start()
error_log("=== DEBUG SESSION ===");
error_log("Session ID: " . session_id());
error_log("User ID: " . ($_SESSION['user_id'] ?? 'not set'));
error_log("Username: " . ($_SESSION['username'] ?? 'not set'));
error_log("===================");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CatatanKu</title>
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/sidebar/sidebarbaru.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4F46E5;
            --secondary-color: #6366F1;
            --accent-color: #818CF8;
            --success-color: #10B981;
            --error-color: #EF4444;
            --bg-light: #F9FAFB;
            --bg-dark: #1F2937;
            --card-light: #FFFFFF;
            --card-dark: #374151;
            --text-light: #1F2937;
            --text-dark: #F9FAFB;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: var(--bg-light);
            color: var(--text-light);
        }

        body.dark {
            background: var(--bg-dark);
            color: var(--text-dark);
        }

        .content {
            margin-left: 90px;
            padding: 2rem;
            min-height: 100vh;
        }

        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .settings-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .settings-header h1 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .settings-card {
            background: var(--card-light);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        body.dark .settings-card {
            background: var(--card-dark);
        }

        .settings-card:hover {
            transform: translateY(-5px);
        }

        .settings-title {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .theme-options {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .theme-option {
            flex: 1;
            padding: 1rem;
            border-radius: 12px;
            border: 2px solid var(--primary-color);
            background: transparent;
            color: var(--text-light);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        body.dark .theme-option {
            color: var(--text-dark);
        }

        .theme-option:hover {
            background: var(--primary-color);
            color: white;
        }

        .theme-option.active {
            background: var(--primary-color);
            color: white;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border-radius: 8px;
            border: 2px solid #E5E7EB;
            background: transparent;
            color: inherit;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .btn {
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .notification {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
        }

        .notification.success {
            background: var(--success-color);
            color: white;
        }

        .notification.error {
            background: var(--error-color);
            color: white;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }

            .settings-grid {
                grid-template-columns: 1fr;
            }
        }

        .optional {
            color: #6B7280;
            font-size: 0.8em;
            font-weight: normal;
            margin-left: 4px;
        }

        .required {
            color: #EF4444;
            font-size: 0.8em;
            margin-left: 4px;
        }
    </style>
</head>
<body class="<?php echo isset($_SESSION['theme_preference']) ? $_SESSION['theme_preference'] : 'light'; ?>">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="settings-container">
            <div class="settings-header">
                <h1>Account Settings</h1>
                <p>Manage your account preferences and profile information</p>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="settings-grid">
                <!-- Profile Information Card -->
                <div class="settings-card">
                    <h2 class="settings-title">
                        <i class="fas fa-user-circle"></i>
                        Profile Information
                    </h2>
                    
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-user"></i> Username
                                <span class="optional">(optional)</span>
                            </label>
                            <?php
                            // Debug log sebelum menampilkan username
                            error_log("Current username value: " . ($_SESSION['username'] ?? 'not set'));
                            ?>
                            <input type="text" id="username" name="username" 
                                   value="<?php 
                                        // Ambil username dari database setiap kali form ditampilkan
                                        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                                        $stmt->execute([$_SESSION['user_id']]);
                                        $currentUsername = $stmt->fetchColumn();
                                        echo htmlspecialchars($currentUsername); 
                                   ?>"
                                   placeholder="Enter new username">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email
                                <span class="optional">(optional)</span>
                            </label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>"
                                   placeholder="Enter new email">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>

                <!-- Password Change Card -->
                <div class="settings-card">
                    <h2 class="settings-title">
                        <i class="fas fa-key"></i>
                        Change Password
                    </h2>
                    
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label for="current_password">
                                <i class="fas fa-lock"></i> Current Password
                                <span class="required">*</span>
                            </label>
                            <input type="password" id="current_password" name="current_password" required
                                   placeholder="Enter current password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">
                                <i class="fas fa-key"></i> New Password
                                <span class="required">*</span>
                            </label>
                            <input type="password" id="new_password" name="new_password" required
                                   placeholder="Enter new password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-check"></i> Confirm New Password
                                <span class="required">*</span>
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   placeholder="Confirm new password">
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="public/js/theme.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('mainSidebar');
        const content = document.querySelector('.content');
        
        // Cek local storage untuk status sidebar
        const sidebarExpanded = localStorage.getItem('sidebarExpanded') === 'true';
        if (sidebarExpanded) {
            sidebar.classList.add('expanded');
            content.classList.add('shifted');
        }
        
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('expanded');
            content.classList.toggle('shifted');
            
            // Simpan status sidebar ke local storage
            localStorage.setItem('sidebarExpanded', sidebar.classList.contains('expanded'));
        });
        
        // Tambahkan event listener untuk responsive design
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 768) {
                content.classList.remove('shifted');
            } else if (sidebarExpanded) {
                content.classList.add('shifted');
            }
        });
    });
    </script>
</body>
</html>