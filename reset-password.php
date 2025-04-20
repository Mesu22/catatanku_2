<?php
require_once 'config/database.php';

if (!isset($_GET['email'])) {
    header("Location: forgot-password.php");
    exit();
}

$email = base64_decode($_GET['email']);

// Aktifkan mode debug untuk development
$debug = true;

if (isset($_POST['submit'])) {
    $reset_code = trim($_POST['reset_code']); // Hapus spasi
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Debug: Tampilkan nilai yang disubmit
    if ($debug) {
        error_log("Submitted reset code: " . $reset_code);
        error_log("Email: " . $email);
    }
    
    // Validasi input
    if ($new_password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        try {
            // Ambil data token terlebih dahulu untuk debugging
            $check_query = "SELECT reset_token, reset_expired FROM users WHERE email = ?";
            $check_stmt = $pdo->prepare($check_query);
            $check_stmt->execute([$email]);
            $token_info = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Debug: Tampilkan nilai dari database
            if ($debug) {
                error_log("Stored token: " . $token_info['reset_token']);
                error_log("Token expired: " . $token_info['reset_expired']);
                error_log("Current time: " . date('Y-m-d H:i:s'));
            }

            // Cek kode reset dan expired time
            if ($token_info && $token_info['reset_token'] === $reset_code) {
                if ($token_info['reset_expired'] > date('Y-m-d H:i:s')) {
                    // Token valid dan belum expired
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users 
                                   SET password = ?, 
                                       reset_token = NULL, 
                                       reset_expired = NULL 
                                   WHERE email = ?";
                    $update_stmt = $pdo->prepare($update_query);
                    
                    if ($update_stmt->execute([$hashed_password, $email])) {
                        $success = "Password berhasil diubah! Silakan login dengan password baru Anda.";
                        session_start();
                        $_SESSION['success_message'] = "Password berhasil diubah! Silakan login dengan password baru Anda.";
                        header("refresh:3;url=login.php");
                    } else {
                        $error = "Gagal mengubah password! Silakan coba lagi.";
                    }
                } else {
                    $error = "Kode reset sudah kadaluarsa! Silakan request kode baru.";
                }
            } else {
                $error = "Kode reset tidak sesuai! Kode yang Anda masukkan: " . htmlspecialchars($reset_code);
                if ($debug) {
                    $error .= "<br>Kode di database: " . htmlspecialchars($token_info['reset_token']);
                }
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
            error_log("Reset password error: " . $e->getMessage());
        }
    }
}

// Tampilkan informasi debug di halaman
if ($debug) {
    echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px; position: fixed; top: 0; right: 0; z-index: 9999;'>";
    echo "<strong>Debug Info:</strong><br>";
    echo "Email: " . htmlspecialchars($email) . "<br>";
    if (isset($reset_code)) {
        echo "Input Reset Code: " . htmlspecialchars($reset_code) . "<br>";
    }
    if (isset($token_info)) {
        echo "DB Reset Token: " . htmlspecialchars($token_info['reset_token']) . "<br>";
        echo "DB Expiry: " . htmlspecialchars($token_info['reset_expired']) . "<br>";
        echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
    }
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CatatanKu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: url('img/desain/background.png');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            width: 60px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 1.8em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #0056b3;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .alert-danger {
            background-color: #ffe6e6;
            color: #dc3545;
            border: 1px solid #dc3545;
        }

        .alert-success {
            background-color: #e6ffe6;
            color: #28a745;
            border: 1px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="img/logo/logo mesa.png" alt="CatatanKu Logo">
        </div>
        <h1>Reset Password</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="reset_code">Kode Reset</label>
                <input type="text" id="reset_code" name="reset_code" required 
                       placeholder="Masukkan kode 6 digit" maxlength="6" pattern="\d{6}">
            </div>
            
            <div class="form-group">
                <label for="new_password">Password Baru</label>
                <input type="password" id="new_password" name="new_password" required 
                       placeholder="Masukkan password baru">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Konfirmasi password baru">
            </div>
            
            <button type="submit" name="submit">Reset Password</button>
        </form>
    </div>

    <script>
        const resetCode = document.getElementById('reset_code');
        
        // Validasi kode reset saat input
        resetCode.addEventListener('input', function(e) {
            // Hapus karakter non-digit
            this.value = this.value.replace(/\D/g, '');
            
            // Batasi panjang ke 6 digit
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
            
            // Tampilkan feedback langsung
            if (this.value.length === 6) {
                this.style.borderColor = '#28a745';
            } else {
                this.style.borderColor = '#dc3545';
            }
        });

        // Validasi sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const code = resetCode.value.trim();
            
            if (code.length !== 6 || !/^\d{6}$/.test(code)) {
                e.preventDefault();
                alert('Kode reset harus berupa 6 digit angka!');
                resetCode.focus();
                return false;
            }
        });
    </script>
</body>
</html>
