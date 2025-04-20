<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: beranda.php");
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    } catch(PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CatatanKu</title>
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

        .login-wrapper {
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
            outline: none;
            border-color: #007bff;
        }

        .btn {
            width: 100%;
            background: linear-gradient(45deg, #007bff, #00a6ff);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 15px;
            font-size: 1rem;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        .register {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
            font-size: 0.95rem;
            color: #666;
        }

        .register a {
            text-decoration: none;
            color: #007bff;
            font-weight: 600;
        }

        .google-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 2px solid #e0e0e0;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 1.5rem;
            font-size: 1rem;
        }

        .google-btn img {
            width: 20px;
            height: 20px;
            margin: 0 12px 0 -12px;
            object-fit: contain;
            vertical-align: middle;
        }

        .google-signin-container {
            margin-top: 20px;
            padding: 10px 0;
            border-top: 1px solid #e0e0e0;
            position: relative;
        }

        .google-signin-container::before {
            content: 'atau';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 10px;
            color: #666;
            font-size: 0.9rem;
        }

        .g_id_signin {
            display: flex !important;
            justify-content: center;
            margin-top: 10px;
        }
    </style>
    <meta name="google-signin-client_id" content="155224684798-v31obq4k8i3g171ure9c9ql9o145td37.apps.googleusercontent.com">
    <script src="https://accounts.google.com/gsi/client" async></script>
    <script>
    function handleCredentialResponse(response) {
        console.log("Received response:", response); // Debug log
        
        fetch('google_auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                credential: response.credential
            })
        })
        .then(response => {
            console.log("Server response status:", response.status); // Debug log
            return response.json();
        })
        .then(data => {
            console.log("Parsed data:", data); // Debug log
            if (data.success) {
                window.location.href = 'beranda.php';
            } else {
                alert('Login gagal: ' + (data.message || 'Terjadi kesalahan'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat login');
        });
    }
    </script>
</head>
<body>
    <div class="login-wrapper">
        <div class="container">
            <img src="img/logo/logo mesa.png" alt="CatatanKu Logo">
            <h2>Selamat Datang Di CatatanKu</h2>
            <?php if (isset($error)): ?>
                <div style="color: red; margin-bottom: 10px;"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button class="btn" type="submit">Login</button>
            </form>
            <div class="google-signin-container">
                <div id="g_id_onload"
                     data-client_id="155224684798-v31obq4k8i3g171ure9c9ql9o145td37.apps.googleusercontent.com"
                     data-context="signin"
                     data-ux_mode="popup"
                     data-callback="handleCredentialResponse"
                     data-auto_select="false"
                     data-itp_support="true">
                </div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-size="large"
                     data-theme="outline"
                     data-text="sign_in_with"
                     data-shape="rectangular"
                     data-logo_alignment="left">
                </div>
            </div>
            <div class="register">
                Belum Punya Akun? <a href="register.php">Daftar Akun</a><br>
                <a href="forgot-password.php">Lupa Password?</a>
            </div>
        </div>
        <div class="image-container">
            <img src="img/desain/ach3 1.png" alt="Login Illustration">
        </div>
    </div>
</body>
</html>
