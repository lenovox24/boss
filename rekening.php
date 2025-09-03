<?php
// File: Hokiraja/rekening.php

$page_title = "Akun Bank Saya";
require_once 'includes/header.php'; // Header akan menangani semua kebutuhan sesi

$user_id = $_SESSION['user_id'];

// Ambil semua rekening bank milik pengguna
$banks_stmt = $conn->prepare("SELECT * FROM user_banks WHERE user_id = ? ORDER BY is_primary DESC, id ASC");
$banks_stmt->bind_param("i", $user_id);
$banks_stmt->execute();
$banks_result = $banks_stmt->get_result();

// Ambil data rekening utama untuk digunakan di form tambah
$primary_bank_stmt = $conn->prepare("SELECT account_name FROM user_banks WHERE user_id = ? AND is_primary = 1");
$primary_bank_stmt->bind_param("i", $user_id);
$primary_bank_stmt->execute();
$primary_bank_name = $primary_bank_stmt->get_result()->fetch_assoc()['account_name'] ?? '';

// Ambil daftar metode pembayaran yang belum dimiliki user untuk dropdown
$existing_banks_query = $conn->query("SELECT bank_name FROM user_banks WHERE user_id = $user_id");
$existing_banks = [];
while ($row = $existing_banks_query->fetch_assoc()) {
    $existing_banks[] = "'" . $row['bank_name'] . "'";
}
$existing_banks_str = !empty($existing_banks) ? implode(',', $existing_banks) : "''";

$payment_methods_result = $conn->query("
    SELECT method_code, method_name, method_type 
    FROM payment_methods 
    WHERE is_active = 1 AND method_code NOT IN ($existing_banks_str)
    ORDER BY method_type, method_name ASC
");
$payment_options = [];
if ($payment_methods_result) {
    while ($row = $payment_methods_result->fetch_assoc()) {
        $payment_options[$row['method_type']][] = $row;
    }
}
?>

<main class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="modern-title text-center mb-4">
                <h1 class="page-title">Manajemen Akun Bank</h1>
            </div>
            <?php
            if (isset($_SESSION['rekening_error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['rekening_error']) . '</div>';
                unset($_SESSION['rekening_error']);
            }
            if (isset($_SESSION['rekening_success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['rekening_success']) . '</div>';
                unset($_SESSION['rekening_success']);
            }
            ?>
            <div class="modern-card mb-4">
                <div class="card-header">
                    <h5>Daftar Rekening Anda</h5>
                </div>
                <div class="card-body">
                    <?php if ($banks_result->num_rows > 0) : ?>
                        <ul class="list-group list-group-flush">
                            <?php while ($bank = $banks_result->fetch_assoc()) : ?>
                                <li class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($bank['bank_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($bank['account_number']); ?> (a.n. <?php echo htmlspecialchars($bank['account_name']); ?>)</small>
                                    </div>
                                    <?php if ($bank['is_primary']) : ?>
                                        <span class="badge bg-warning text-dark">Utama</span>
                                    <?php endif; ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else : ?>
                        <p class="text-center text-white-50">Anda belum memiliki rekening bank.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modern-card">
                <div class="card-header">
                    <h5>Tambah Rekening Baru</h5>
                </div>
                <div class="card-body">
                    <form action="process_rekening.php" method="POST">
                        <div class="mb-3">
                            <label for="bank_name" class="form-label">Pilih Bank / E-Wallet</label>
                            <select class="form-select" id="bank_name" name="bank_name" required>
                                <option value="">-- Pilih Metode --</option>
                                <?php foreach ($payment_options as $type => $methods) : ?>
                                    <optgroup label="<?php echo $type; ?>">
                                        <?php foreach ($methods as $method) : ?>
                                            <option value="<?php echo htmlspecialchars($method['method_code']); ?>">
                                                <?php echo htmlspecialchars($method['method_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="account_number" class="form-label">Nomor Rekening / No. HP</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="account_name" class="form-label">Nama Pemilik Rekening</label>
                            <input type="text" class="form-control" id="account_name" name="account_name" value="<?php echo htmlspecialchars($primary_bank_name); ?>" readonly>
                            <div class="form-text">Nama pemilik rekening disamakan dengan rekening utama Anda.</div>
                        </div>
                        <hr class="border-secondary my-4">
                        <div class="mb-3">
                            <label for="password" class="form-label">Konfirmasi Password Anda</label>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password untuk menyimpan">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning fw-bold">Tambah Rekening</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
$conn->close();
?>