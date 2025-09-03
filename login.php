<?php
// File: hokiraja/login.php (REVISI FINAL: Tambahan Session Login Success)

require_once 'includes/db_connect.php';

// Variabel untuk pesan error
$error_message = '';

// Logika saat form di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Ambil data user dari database
    $stmt = $conn->prepare("SELECT id, username, password, status FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verifikasi password yang di-hash
        if (password_verify($password, $user['password'])) {
            // Cek apakah akun aktif
            if ($user['status'] === 'active') {
                // Password benar dan akun aktif, buat session
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Update waktu terakhir terlihat (untuk status online)
                $update_stmt = $conn->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();

                // === PENAMBAHAN KODE: Set session untuk SweetAlert Login Success ===
                $_SESSION['login_success'] = true;
                $_SESSION['login_username'] = $user['username'];
                // =================================================================

                // Redirect ke halaman beranda
                header("Location: beranda");
                exit();
            } else {
                // Akun tidak aktif
                $error_message = "Akun Anda saat ini tidak aktif. Silakan hubungi support.";
            }
        } else {
            // Password salah
            $error_message = "Username atau password yang Anda masukkan salah.";
        }
    } else {
        // Username tidak ditemukan
        $error_message = "Username atau password yang Anda masukkan salah.";
    }
    $stmt->close();
}

require_once 'includes/header.php';

?>
<main class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="page-header text-center mb-4">
                <h1 class="page-title">Login Member</h1>
                <p class="text-white-50">Masuk ke akun Anda untuk mulai bermain.</p>
            </div>

            <div class="card bg-dark text-white border-secondary">
                <div class="card-body p-4">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form action="login" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mb-3">
                            <a href="#" class="form-text text-warning text-decoration-none">Lupa Password?</a>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning btn-lg fw-bold">Login</button>
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-white-50">Belum punya akun? <a href="daftar" class="text-warning text-decoration-none">Daftar di sini</a></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Memanggil footer di akhir
require_once 'includes/footer.php';
if (isset($conn)) {
    $conn->close();
}
?>