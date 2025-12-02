<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

// INISIALISASI VARIABEL ERROR
$error = '';

// Kalau sudah login, langsung ke dashboard
if (isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek ke database (plain text untuk tugas lokal)
    $sql = "SELECT * FROM user 
            WHERE username = '$username' 
              AND password = '$password'
            LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        // Simpan data penting ke session
        $_SESSION['id_user']  = $row['id_user'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role']     = $row['role'];

        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - SMPS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-bar">
        <div class="top-bar-title">
            SMPS – Sistem Monitoring Pengelolaan Sampah
        </div>
        <div class="top-bar-right">
            <span class="text-muted">Silakan login untuk melanjutkan</span>
        </div>
    </div>

    <div class="container">
        <div class="login-wrapper">
            <div class="card">
                <h1 class="login-title">Selamat Datang</h1>
                <p class="login-subtitle">
                    Pantau pengelolaan sampah secara lebih ramah lingkungan dan teratur.
                </p>

                <?php if ($error != '') { ?>
                    <div class="error-box">
                        <?php echo $error; ?>
                    </div>
                <?php } ?>

                <form method="POST" style="margin-top:8px;">
                    <p>
                        <label>Username</label>
                        <input type="text" name="username" placeholder="Masukkan username" required>
                    </p>
                    <p>
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Masukkan password" required>
                    </p>
                    <button type="submit" name="login">
                        Masuk
                    </button>
                </form>

                <div class="info-akun">
                    <strong>Akun contoh untuk pengujian:</strong><br>
                    ADMIN → <code>admin / admin123</code><br>
                    PETUGAS → <code>petugas1 / petugas123</code>
                </div>
            </div>
        </div>
    </div>
</body>
</html>