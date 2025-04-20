<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - CatatanKu</title>
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

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #007bff;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
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
        <h1>Lupa Password</h1>
        
        <?php
        if(isset($_POST['submit'])) {
            if(!file_exists('config/database.php')) {
                echo "<div class='alert alert-danger'>Error: File database.php tidak ditemukan</div>";
            } else {
                require_once 'config/database.php';
                
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                
                // Cek apakah email ada di database
                $query = "SELECT * FROM users WHERE email = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$email]);
                $result = $stmt->fetch();
                
                if($result) {
                    try {
                        // Generate kode reset 6 digit
                        $reset_code = sprintf("%06d", mt_rand(1, 999999));
                        $expired = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                        
                        // Simpan kode ke database
                        $query = "UPDATE users SET reset_token = ?, reset_expired = ? WHERE email = ?";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute([$reset_code, $expired, $email]);
                        
                        // Tampilkan pesan sukses dan kode reset
                        echo "<div class='alert alert-success'>Kode reset password telah dikirim: " . $reset_code . "</div>";
                        
                        // Redirect setelah 3 detik
                        $encrypted_email = base64_encode($email);
                        echo "<script>
                            setTimeout(function() {
                                window.location.href = 'reset-password.php?email=" . $encrypted_email . "';
                            }, 3000);
                        </script>";
                        
                    } catch (Exception $e) {
                        echo "<div class='alert alert-danger'>Terjadi kesalahan: " . htmlspecialchars($e->getMessage()) . "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Email tidak ditemukan</div>";
                }
            }
        }
        ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       placeholder="Masukkan email Anda">
            </div>
            
            <button type="submit" name="submit">Kirim Kode Reset Password</button>
        </form>

        <div class="back-link">
            <a href="login.php">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>
