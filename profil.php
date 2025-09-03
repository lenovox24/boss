<?php
// File: Hokiraja/profil.php (REVISI TOTAL)

$page_title = "Profil Saya";
require_once 'includes/header.php'; // Header akan menangani semua kebutuhan sesi

$user_id = $_SESSION['user_id'];

// Ambil data lengkap pengguna dari tabel 'users'
$user_stmt = $conn->prepare("SELECT full_name, email, phone, referral_code FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Ambil data rekening bank utama pengguna untuk ditampilkan di form
$bank_stmt = $conn->prepare("SELECT bank_name, account_number FROM user_banks WHERE user_id = ? AND is_primary = 1");
$bank_stmt->bind_param("i", $user_id);
$bank_stmt->execute();
$primary_bank = $bank_stmt->get_result()->fetch_assoc();
$bank_stmt->close();

?>

<main class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="modern-card">
                <div class="modern-title">
                    <h3 class="text-center fw-bold mb-4">Profil</h3>
                </div>
                <div class="card-body p-4 p-md-5">

                    <div class="profile-info-section mb-4">
                        <div class="profile-info-item">
                            <span class="profile-info-label">Nama Lengkap</span>
                            <span class="profile-info-value"><?php echo htmlspecialchars($user_data['full_name']); ?></span>
                        </div>
                        <div class="profile-info-item">
                            <span class="profile-info-label">Email</span>
                            <span class="profile-info-value"><?php echo htmlspecialchars($user_data['email']); ?></span>
                        </div>
                        <div class="profile-info-item">
                            <span class="profile-info-label">Telepon</span>
                            <span class="profile-info-value"><?php echo htmlspecialchars($user_data['phone']); ?></span>
                        </div>
                        <div class="profile-info-item">
                            <span class="profile-info-label">Kode Referral</span>
                            <span class="profile-info-value"><?php echo htmlspecialchars($user_data['referral_code']); ?></span>
                        </div>
                    </div>

                    <hr class="border-secondary my-4">

                    <div class="modern-title">
                        <h4 class="text-center text-warning mb-4">TUKAR PASSWORD</h4>
                    </div>

                    <form action="process_profil.php" method="POST">
                        <div class="mb-3">
                            <label for="old_password" class="form-label">Password Lama</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Password Lama" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Password Baru" required>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password Baru" required>
                        </div>

                        <div class="mb-4">
                            <label for="bank_digits" class="form-label">Nomor Rekening</label>
                            <div class="input-group">
                                <span class="input-group-text bg-secondary border-secondary text-white">
                                    <?php echo htmlspecialchars($primary_bank['bank_name'] ?? 'N/A'); ?>
                                </span>
                                <input type="text" class="form-control" id="bank_digits" name="bank_digits" placeholder="4 Digit Terakhir" required maxlength="4" pattern="\d{4}">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning fw-bold">Kirim</button>
                            <a href="beranda.php" class="btn btn-secondary">Tetap Menggunakan Password Lama</a>
                        </div>
                    </form>

                    <div class="profile-info-text mt-4 text-center">
                        <p>Anda wajib mengganti Password setiap 30 hari</p>
                        <p>Password harus diisi dengan Angka dan Huruf minimal 6 digit</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
$conn->close();
?>