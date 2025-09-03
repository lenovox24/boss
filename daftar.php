<?php
// File: hokiraja/daftar.php (REVISI FINAL FULL - Pendaftaran & SweetAlert)

// Memanggil header di awal untuk memulai session dan koneksi
// Penjaga halaman (jika sudah login, redirect ke beranda.php) akan ditangani di header.php
require_once 'includes/header.php';

// === LOGIKA UNTUK MENAMPILKAN SWEETALERT SETELAH PENDAFTARAN BERHASIL ===
if (isset($_SESSION['registration_success'])) {
    $success_username = $_SESSION['success_username'];

    // Hapus session agar tidak muncul lagi saat refresh
    unset($_SESSION['registration_success']);
    unset($_SESSION['success_username']);

    // Tampilkan skrip JavaScript untuk SweetAlert
    // Kemudian REDIRECT ke beranda.php SETELAH ALERT
    echo "
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Pendaftaran Berhasil!',
                html: 'Selamat datang, <strong style=\"color: #ff006e;\">" . htmlspecialchars($success_username) . "</strong>!<br>Anda akan diarahkan ke halaman utama.',
                icon: 'success',
                confirmButtonText: 'Lanjutkan',
                timer: 3500,
                timerProgressBar: true,
                background: '#212529', // Warna background gelap
                color: '#fff', // Warna teks putih
                confirmButtonColor: '#ff006e', // Warna tombol sesuai tema
                allowOutsideClick: false, // Tidak bisa klik di luar untuk menutup
                allowEscapeKey: false,   // Tidak bisa tekan Esc untuk menutup
            }).then((result) => {
                // Redirect ke beranda setelah alert ditutup (baik oleh timer atau tombol)
                window.location.href = 'beranda';
            });
        });
    </script>";
    exit(); // Penting: hentikan eksekusi skrip setelah mencetak SweetAlert dan akan ada redirect JS
}


// Ambil metode pembayaran
$payment_methods_result = $conn->query("SELECT method_code, method_name, method_type, min_length, max_length FROM payment_methods WHERE is_active = 1 ORDER BY method_type, method_name ASC");
$payment_options = [];
if ($payment_methods_result) {
    while ($row = $payment_methods_result->fetch_assoc()) {
        $payment_options[$row['method_type']][] = $row;
    }
}

// --- LOGIKA CAPTCHA & REFERRAL ---
if (empty($_SESSION['captcha_code'])) {
    $_SESSION['captcha_code'] = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
}
function generateUniqueReferralCode($conn)
{
    do {
        $code = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);
    return $code;
}

$error_message = '';

// --- LOGIKA PROSES FORM ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['captcha'] !== $_SESSION['captcha_code']) {
        $error_message = "Kode Captcha yang Anda masukkan tidak sesuai.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $bank_name = $_POST['bank_name'];
        $account_name = trim($_POST['account_name']);
        $account_number = trim($_POST['account_number']);

        if ($password !== $confirm_password) {
            $error_message = "Konfirmasi password tidak cocok dengan password Anda.";
        } else {
            // Validasi: Pastikan email belum terdaftar
            $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check_email->bind_param("s", $email);
            $stmt_check_email->execute();
            if ($stmt_check_email->get_result()->num_rows > 0) {
                $error_message = "Email sudah digunakan. Silakan gunakan yang lain.";
                $stmt_check_email->close();
            } else {
                $stmt_check_email->close(); // Tutup statement sebelumnya

                // Validasi: Pastikan username belum terdaftar
                $stmt_check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt_check_username->bind_param("s", $username);
                $stmt_check_username->execute();
                if ($stmt_check_username->get_result()->num_rows > 0) {
                    $error_message = "Username sudah digunakan. Silakan gunakan yang lain.";
                    $stmt_check_username->close();
                } else {
                    $stmt_check_username->close(); // Tutup statement sebelumnya

                    $conn->begin_transaction();
                    try {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $referral_code = generateUniqueReferralCode($conn);
                        // Tambahkan ini di dekat pengambilan data POST lainnya
                        $referred_by = $_POST['referral'] ?? null;

                        // Ubah query INSERT
                        $stmt_user = $conn->prepare("INSERT INTO users (username, password, full_name, email, phone, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt_user->bind_param("sssssss", $username, $hashed_password, $full_name, $email, $phone, $referral_code, $referred_by);
                        if (!$stmt_user->execute()) {
                            throw new Exception("Gagal mendaftar user: " . $stmt_user->error);
                        }
                        $new_user_id = $conn->insert_id;
                        $stmt_user->close(); // Tutup statement user

                        $stmt_bank = $conn->prepare("INSERT INTO user_banks (user_id, bank_name, account_number, account_name, is_primary) VALUES (?, ?, ?, ?, 1)");
                        $stmt_bank->bind_param("isss", $new_user_id, $bank_name, $account_number, $account_name);
                        if (!$stmt_bank->execute()) {
                            throw new Exception("Gagal menyimpan rekening bank: " . $stmt_bank->error);
                        }
                        $stmt_bank->close(); // Tutup statement bank

                        $conn->commit();

                        $_SESSION['user_id'] = $new_user_id;
                        $_SESSION['username'] = $username;

                        // TAMBAHKAN DUA BARIS INI UNTUK MEMICU SWEETALERT
                        $_SESSION['registration_success'] = true; // <-- Gunakan nama baru
                        $_SESSION['username'] = $username; // Kita bisa pakai ulang session username


                        // Alihkan pengguna ke halaman beranda
                        header("Location: beranda.php");
                        exit();
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Terjadi kesalahan pada server: " . $e->getMessage();
                        error_log("Registration error: " . $e->getMessage()); // Log error
                    }
                }
            }
        }
    }
    $_SESSION['captcha_code'] = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT); // Refresh captcha jika ada error
}
?>

<main class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="page-header text-center mb-4">
                <h1 class="page-title">Formulir Pendaftaran</h1>
                <p class="text-white-50">Bergabunglah bersama kami dan nikmati permainannya.</p>
            </div>

            <div class="card bg-dark text-white border-secondary">
                <div class="card-body p-4">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form action="daftar.php" method="POST" id="registration-form">
                        <h5 class="form-section-title">Data Akun</h5>
                        <div class="mb-3"><label for="username" class="form-label">Username</label><input type="text" class="form-control" id="username" name="username" required></div>
                        <div class="mb-3"><label for="password" class="form-label">Password</label>
                            <div class="input-group"><input type="password" class="form-control" id="password" name="password" required><button class="btn btn-outline-secondary toggle-password" type="button"><i class="fas fa-eye"></i></button></div>
                        </div>
                        <div class="mb-3"><label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <div class="input-group"><input type="password" class="form-control" id="confirm_password" name="confirm_password" required><button class="btn btn-outline-secondary toggle-password" type="button"><i class="fas fa-eye"></i></button></div>
                            <div id="password-match-message" class="form-text mt-1"></div>
                        </div>
                        <h5 class="form-section-title mt-4">Data Kontak</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="full_name" class="form-label">Nama Lengkap</label><input type="text" class="form-control" id="full_name" name="full_name" required></div>
                            <div class="col-md-6 mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email" required></div>
                        </div>
                        <div class="mb-3"><label for="phone" class="form-label">Nomor HP</label><input type="tel" class="form-control" id="phone" name="phone" required></div>
                        <h5 class="form-section-title mt-4">Data Bank</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="bank_name" class="form-label">Pilih Metode Pembayaran</label><select class="form-select" id="bank_name" name="bank_name" required>
                                    <option value="">-- Pilih Bank / E-Wallet --</option><?php foreach ($payment_options as $type => $methods): ?><optgroup label="<?php echo $type; ?>"><?php foreach ($methods as $method): ?><option value="<?php echo htmlspecialchars($method['method_code']); ?>" data-minlength="<?php echo $method['min_length']; ?>" data-maxlength="<?php echo $method['max_length']; ?>"><?php echo htmlspecialchars($method['method_name']); ?></option><?php endforeach; ?></optgroup><?php endforeach; ?>
                                </select></div>
                            <div class="col-md-6 mb-3"><label for="account_name" class="form-label">Nama Pemilik Rekening</label><input type="text" class="form-control" id="account_name" name="account_name" required></div>
                        </div>
                        <div class="mb-3"><label for="account_number" class="form-label">Nomor Rekening</label><input type="tel" class="form-control" id="account_number" name="account_number" placeholder="Pilih metode pembayaran terlebih dahulu" required oninput="this.value = this.value.replace(/[^0-9]/g, '');"></div>
                        <h5 class="form-section-title mt-4">Lain-lain</h5>
                        <div class="mb-3"><label for="referral" class="form-label">Kode Referral (Opsional)</label><input type="text" class="form-control" id="referral" name="referral" placeholder="Masukkan kode jika ada"></div>
                        <div class="row align-items-center">
                            <div class="col-md-6 mb-3"><label for="captcha" class="form-label">Kode Captcha</label><input type="text" class="form-control" id="captcha" name="captcha" maxlength="4" required></div>
                            <div class="col-md-6 mb-3">
                                <div class="captcha-box"><?php echo $_SESSION['captcha_code']; ?></div>
                            </div>
                        </div>
                        <div class="d-grid mt-4"><button type="submit" class="btn btn-warning btn-lg fw-bold">Daftar Sekarang</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Handle referral code from hash parameter
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash && hash.includes('referral=')) {
        const referralCode = hash.split('referral=')[1];
        if (referralCode) {
            document.getElementById('referral').value = referralCode;
        }
    }
});
</script>

<?php
require_once 'includes/footer.php';
if (isset($conn)) {
    $conn->close();
}
?>