<?php
// File: admin/index.php (VERSI AMAN)

// Mulai session
session_start();

// Jika admin sudah login, redirect ke dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard");
    exit();
}

// Sertakan file koneksi database
require_once '../includes/db_connect.php';

$error_message = '';

// Cek jika form telah di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Ambil data admin dari database
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        // ==========================================================
        // Verifikasi password MENGGUNAKAN FUNGSI AMAN (password_verify)
        // ==========================================================
        if (password_verify($password, $admin['password'])) {
            // Password benar, buat session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            // Redirect ke halaman dashboard
            header("Location: dashboard");
            exit();
        } else {
            // Password salah
            $error_message = "Username atau password salah.";
        }
    } else {
        // Username tidak ditemukan
        $error_message = "Username atau password salah.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - HOKIRAJA</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="card login-card shadow-sm">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Admin Panel Login</h3>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>