<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Username atau email sudah terdaftar!";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                
                $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
                header("Location: login.php");
                exit();
            }
        } catch(PDOException $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CatatanKu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: url('img/desain/background.png');
            background-size: cover;
            background-position: center;
            overflow: hidden;
        }

        .register-wrapper {
            display: flex;
            align-items: center;
            width: 900px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px 0 0 15px;
            text-align: center;
            width: 380px;
            position: relative;
            z-index: 10;
        }

        .image-container {
            flex: 1;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .image-container img {
            width: 100%;
            max-width: 400px;
            height: auto;
            object-fit: cover;
            border-radius: 10px;
        }

        .container img {
            width: 80px;
            margin-bottom: 1.5rem;
        }

        .container h2 {
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-size: 1.8rem;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color: #3498db;
            outline: none;
        }

        .btn {
            width: 100%;
            background-color: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 1rem;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .login-link {
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #7f8c8d;
        }

        .login-link a {
            text-decoration: none;
            color: #3498db;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="container">
            <img src="img/logo/logo mesa.png" alt="CatatanKu Logo">
            <h2>Daftar Akun Baru</h2>
            <?php if (isset($error)): ?>
                <div style="color: red; margin-bottom: 10px;"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="register.php" method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
                <button class="btn" type="submit">Daftar</button>
            </form>
            <div class="register">
                Sudah Punya Akun? <a href="login.php">Login</a>
            </div>
        </div>
        <div class="image-container">
            <img src="img/desain/ach3 1.png" alt="Register Illustration">
        </div>
    </div>
</body>
</html>
