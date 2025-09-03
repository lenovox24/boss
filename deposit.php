<?php
// File: hokiraja/deposit.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';

// Cek dulu apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// "PENJAGA" untuk mengecek deposit yang pending
$check_pending_stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM deposit_transactions WHERE user_id = ? AND status = 'pending'");
$check_pending_stmt->bind_param("i", $_SESSION['user_id']);
$check_pending_stmt->execute();
$pending_result = $check_pending_stmt->get_result()->fetch_assoc();
$check_pending_stmt->close();

if ($pending_result['pending_count'] > 0) {
    // Jika ada deposit pending, atur notifikasi dan alihkan SEKARANG
    $_SESSION['pending_deposit_alert'] = "Anda masih memiliki transaksi deposit yang sedang diproses. Mohon tunggu hingga selesai.";
    header("Location: beranda.php");
    exit(); // Hentikan eksekusi di sini
}

// ========================================================
// LANGKAH 2: JIKA LOLOS PENJAGA, BARU TAMPILKAN HALAMAN
// ========================================================
$page_title = "Deposit Dana";
require_once 'includes/header.php'; // Panggil header SETELAH semua logika redirect selesai

// Logika SweetAlert jika ada (misal dari form submit)
if (isset($_SESSION['deposit_success'])) {
    $message = $_SESSION['deposit_success'];
    unset($_SESSION['deposit_success']);
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Deposit Berhasil!',
                    text: '" . htmlspecialchars($message) . "',
                    icon: 'success',
                    background: '#212529',
                    color: '#fff',
                    confirmButtonColor: '#ff006e'
                });
            });
          </script>";
}
if (isset($_SESSION['deposit_error'])) {
    $message = $_SESSION['deposit_error'];
    unset($_SESSION['deposit_error']);
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Deposit Gagal!',
                    text: '" . htmlspecialchars($message) . "',
                    icon: 'error',
                    background: '#212529',
                    color: '#fff',
                    confirmButtonColor: '#ff006e'
                });
            });
          </script>";
}

// Ambil data untuk dropdown Bonus dari database
$bonuses_result = $conn->query("SELECT id, bonus_name, bonus_code, min_deposit, max_bonus_amount, percentage, turnover_multiplier FROM bonuses WHERE is_active = 1 ORDER BY id ASC");
$bonuses_options = [];
if ($bonuses_result) {
    while ($row = $bonuses_result->fetch_assoc()) {
        $bonuses_options[] = $row;
    }
} else {
    // Fallback jika query gagal
    $bonuses_options[] = ['id' => 0, 'bonus_name' => '- Silahkan pilih -', 'bonus_code' => 'NONE', 'min_deposit' => 0.00, 'max_bonus_amount' => NULL, 'percentage' => NULL, 'turnover_multiplier' => 1.00, 'is_active' => 1];
}


// Ambil data untuk dropdown Tujuan (dari admin_deposit_accounts)
$admin_deposit_accounts_result = $conn->query("
    SELECT ada.id, ada.account_name, ada.account_number, pm.method_name, pm.method_type, pm.logo as method_logo, ada.min_deposit as acc_min_deposit, ada.max_deposit as acc_max_deposit, pm.method_code, ada.qris_image_url, ada.is_active
    FROM admin_deposit_accounts ada
    JOIN payment_methods pm ON ada.method_code = pm.method_code
    ORDER BY pm.method_type, pm.method_name ASC
");
$deposit_purposes_grouped = [];
$qris_static_info = null; // Untuk menyimpan info QRIS (Auto) yang statis (gambar, min/max)
$all_accounts_flat = [];
if ($admin_deposit_accounts_result) {
    while ($row = $admin_deposit_accounts_result->fetch_assoc()) {
        $all_accounts_flat[] = $row;
        // Cek jika ini adalah provider QRIS (Auto)
        if (strpos(strtoupper($row['method_name']), 'QRIS') !== false && $row['method_type'] === 'E-Wallet') {
            $qris_static_info = $row; // Ambil info QRIS (Auto)
        } else {
            // Kelompokkan tujuan deposit lainnya berdasarkan tipe
            $deposit_purposes_grouped[$row['method_type']][] = $row;
        }
    }
}

// Ambil data status bank untuk Accordion 'Status Bank' (Hanya untuk channel Bank Transfer)
$bank_status_data = $conn->query("
    SELECT pm.method_code, pm.method_name, pm.logo, bs.status
    FROM payment_methods pm
    LEFT JOIN bank_status bs ON pm.method_code = bs.method_code
    WHERE pm.is_active = 1 AND pm.method_type IN ('Bank', 'E-Wallet', 'Pulsa', 'Virtual Account') -- Hanya tampilkan status untuk metode transfer
    ORDER BY pm.method_type, pm.method_name ASC
");
$bank_status_list = [];
if ($bank_status_data) {
    while ($row = $bank_status_data->fetch_assoc()) {
        $bank_status_list[] = $row;
    }
}
?>

<main class="container my-4">
</main>

<?php
if (isset($conn)) {
    $conn->close();
}
?>

<main class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="modern-card">
                <div class="modern-title d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-title mb-0">Deposit</h1>
                </div>

                <div class="deposit-channels d-flex mb-4" id="deposit-channel-buttons">
                    <button class="btn btn-qris active flex-fill me-2" data-channel="qris" data-bs-toggle="collapse" data-bs-target="#qris-form-section" aria-expanded="true" aria-controls="qris-form-section">
                        <i class="fas fa-qrcode"></i> QRIS (Auto)
                    </button>
                    <button class="btn btn-bank-transfer flex-fill" data-channel="bank" data-bs-toggle="collapse" data-bs-target="#bank-transfer-form-section" aria-expanded="false" aria-controls="bank-transfer-form-section">
                        <i class="fas fa-bank"></i> Transfer Bank, E-Wallet & Pulsa
                    </button>
                </div>

                <div id="deposit-form-sections">
                    <div class="collapse show" id="qris-form-section" data-bs-parent="#deposit-form-sections">
                        <form id="deposit-qris-form" action="process_deposit_non_api.php" method="POST"> <input type="hidden" name="channel_type" value="qris">
                            <input type="hidden" name="payment_method_code" value="<?php echo htmlspecialchars($qris_static_info['method_code'] ?? 'QRIS_STATIC'); ?>">
                            <div class="alert alert-info d-flex align-items-center mb-3" style="background: linear-gradient(80deg, #212529 30%, #ff006e66 100%); color: #fff; border: none; border-radius: 10px;">
                                <i class="fas fa-key fa-2x me-3" style="color:#ff006e;"></i>
                                <div>
                                    <div style="font-weight: bold; font-size: 1.05rem;">Kode Unik Deposit</div>
                                    <div style="font-size: 0.97rem;">Deposit wajib menggunakan <span style="color:#ff006e;font-weight:bold;">kode unik</span> di akhir nominal, contoh: <span style="color:#ff006e;font-weight:bold;">100.985</span> agar proses lebih cepat & otomatis.</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="username_deposit_qris" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username_deposit_qris" value="<?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="bonus_qris" class="form-label">Bonus</label>
                                <div class="input-group">
                                    <select class="form-select" id="bonus_qris" name="bonus_id">
                                        <?php foreach ($bonuses_options as $bonus): ?>
                                            <option value="<?php echo $bonus['id']; ?>" data-min-deposit="<?php echo $bonus['min_deposit']; ?>" data-max-bonus="<?php echo $bonus['max_bonus_amount']; ?>" data-percentage="<?php echo $bonus['percentage']; ?>" data-turnover="<?php echo $bonus['turnover_multiplier']; ?>">
                                                <?php echo htmlspecialchars($bonus['bonus_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="input-group-text bg-transparent text-white-50"><i class="fas fa-info-circle"></i></span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="provider_qris_display" class="form-label">Provider <span class="text-warning">*</span></label>
                                <input type="text" class="form-control" id="provider_qris_display" value="QRIS (Auto)" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="amount_qris" class="form-label">Uang Sejumlah <span class="text-warning">*</span></label>
                                <input type="text" class="form-control deposit-amount-input" id="amount_qris" name="amount" placeholder="Uang Sejumlah" required min="10000">
                                <div class="form-text text-white-50" id="amount_qris_info">Min: IDR <?php echo number_format($qris_static_info['acc_min_deposit'] ?? 10000, 0, ',', '.'); ?> | Max: IDR <?php echo number_format($qris_static_info['acc_max_deposit'] ?? 50000000, 0, ',', '.'); ?></div>
                            </div>

                            <?php if (!empty($qris_static_info['qris_image_url'])): ?>
                                <div class="text-center my-4">
                                    <img src="<?php echo $base_url . '/assets/images/qris/' . htmlspecialchars($qris_static_info['qris_image_url']); ?>" alt="QRIS Code" class="img-fluid qris-image" style="max-width: 250px; border-radius: 5px;">
                                    <p class="text-white-50 mt-2">Scan QR Code ini untuk deposit Anda.</p>
                                </div>
                            <?php else: ?>
                                <div class="text-center my-4">
                                    <p class="text-danger">Gambar QRIS belum diatur di Admin Panel.</p>
                                </div>
                            <?php endif; ?>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-warning btn-lg fw-bold">Kirim Deposit QRIS</button>
                            </div>
                        </form>
                    </div>

                    <div class="collapse" id="bank-transfer-form-section" data-bs-parent="#deposit-form-sections">
                        <form id="deposit-bank-form" action="process_deposit_non_api.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="channel_type" value="bank_transfer">
                            <div class="alert alert-info d-flex align-items-center mb-3" style="background: linear-gradient(90deg,#212529 60%,#ff006e 100%); color: #fff; border: none; border-radius: 10px;">
                                <i class="fas fa-key fa-2x me-3" style="color:#ff006e;"></i>
                                <div>
                                    <div style="font-weight: bold; font-size: 1.05rem;">Kode Unik Deposit</div>
                                    <div style="font-size: 0.97rem;">Deposit wajib menggunakan <span style="color:#ff006e;font-weight:bold;">kode unik</span> di akhir nominal, contoh: <span style="color:#ff006e;font-weight:bold;">100.985</span> agar proses lebih cepat & otomatis.</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="username_deposit_bank" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username_deposit_bank" value="<?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="bonus_bank" class="form-label">Bonus</label>
                                <div class="input-group">
                                    <select class="form-select" id="bonus_bank" name="bonus_id">
                                        <?php foreach ($bonuses_options as $bonus): ?>
                                            <option value="<?php echo $bonus['id']; ?>" data-min-deposit="<?php echo $bonus['min_deposit']; ?>" data-max-bonus="<?php echo $bonus['max_bonus_amount']; ?>" data-percentage="<?php echo $bonus['percentage']; ?>" data-turnover="<?php echo $bonus['turnover_multiplier']; ?>">
                                                <?php echo htmlspecialchars($bonus['bonus_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="input-group-text bg-transparent text-white-50"><i class="fas fa-info-circle"></i></span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="purpose_bank" class="form-label">Tujuan <span class="text-warning">*</span></label>
                                <select class="form-select" id="purpose_bank" name="admin_deposit_account_id" required>
                                    <option value="">- Pilih Tujuan Deposit -</option>
                                    <?php foreach ($deposit_purposes_grouped as $type => $accounts): ?>
                                        <optgroup label="<?php echo htmlspecialchars($type); ?>">
                                            <?php foreach ($accounts as $account): ?>
                                                <option value="<?php echo htmlspecialchars($account['id']); ?>"
                                                    data-account-name="<?php echo htmlspecialchars($account['account_name']); ?>"
                                                    data-account-number="<?php echo htmlspecialchars($account['account_number']); ?>"
                                                    data-method-name="<?php echo htmlspecialchars($account['method_name']); ?>"
                                                    data-min-deposit-acc="<?php echo htmlspecialchars($account['acc_min_deposit']); ?>"
                                                    data-max-deposit-acc="<?php echo htmlspecialchars($account['acc_max_deposit']); ?>">
                                                    <?php echo htmlspecialchars($account['method_name'] . ' - ' . $account['account_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-white-50" id="selected_purpose_info"></div>
                            </div>

                            <div class="mb-3">
                                <label for="amount_bank" class="form-label">Uang Sejumlah <span class="text-warning">*</span></label>
                                <input type="text" class="form-control deposit-amount-input" id="amount_bank" name="amount" placeholder="Uang Sejumlah" required min="10000">
                                <div class="form-text text-white-50" id="amount_bank_info"></div>
                            </div>

                            <div class="mb-3">
                                <label for="proof_of_transfer_bank" class="form-label">Upload Bukti Transfer</label>
                                <input type="file" class="form-control" id="proof_of_transfer_bank" name="proof_of_transfer" accept="image/*">
                            </div>

                            <div class="mb-3">
                                <label for="remark_bank" class="form-label">Berita</label>
                                <textarea class="form-control" id="remark_bank" name="remark" rows="3" placeholder="Mohon diisi kolom berita / harap cantumkan kode SN untuk deposit pulsa"></textarea>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-warning btn-lg fw-bold">Kirim</button>
                            </div>
                        </form>

                        <div class="accordion accordion-flush mt-4" id="bankDepositAccordion">
                            <div class="accordion-item bg-dark border-secondary">
                                <h2 class="accordion-header" id="headingNotesBank">
                                    <button class="accordion-button bg-dark text-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotesBank" aria-expanded="false" aria-controls="collapseNotesBank">
                                        Catatan
                                    </button>
                                </h2>
                                <div id="collapseNotesBank" class="accordion-collapse collapse" aria-labelledby="headingNotesBank" data-bs-parent="#bankDepositAccordion">
                                    <div class="accordion-body text-white-50">
                                        <p>Mohon perhatikan hal-hal berikut saat melakukan deposit:</p>
                                        <ul>
                                            <li>Minimal deposit adalah IDR 50.000.</li>
                                            <li>Deposit diproses dalam 5-10 menit setelah dana diterima.</li>
                                            <li>Pastikan jumlah transfer sesuai dengan nominal yang diajukan.</li>
                                            <li>Jika ada kendala, segera hubungi Live Chat Support.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item bg-dark border-secondary" id="bank-status-accordion-item">
                                <h2 class="accordion-header" id="headingBankStatusBank">
                                    <button class="accordion-button bg-dark text-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBankStatusBank" aria-expanded="false" aria-controls="collapseBankStatusBank">
                                        Status Bank
                                    </button>
                                </h2>
                                <div id="collapseBankStatusBank" class="accordion-collapse collapse" aria-labelledby="headingBankStatusBank" data-bs-parent="#bankDepositAccordion">
                                    <div class="accordion-body text-white-50">
                                        <div id="bank-status-list" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                                            <?php if (!empty($all_accounts_flat)): ?>
                                                <?php foreach ($all_accounts_flat as $acc): ?>
                                                    <div class="col">
                                                        <div class="bank-status-item-modern shadow-sm p-3 rounded-4 d-flex flex-column align-items-center justify-content-center gap-2 <?php echo ($acc['is_active'] ? 'bank-online' : 'bank-offline'); ?>" style="background:#23272b;min-height:120px;">
                                                            <img src="assets/images/bank_logos/<?php echo htmlspecialchars($acc['method_logo'] ?? 'default_bank_logo.png'); ?>" alt="<?php echo htmlspecialchars($acc['method_name']); ?>" class="bank-logo-modern mb-2">
                                                            <div class="fw-bold text-white text-center mb-1" style="font-size:1.05rem;line-height:1.2;">
                                                                <?php echo htmlspecialchars($acc['method_name']); ?>
                                                            </div>
                                                            <span class="badge rounded-pill <?php echo ($acc['is_active'] ? 'bg-success' : 'bg-danger'); ?> px-3 py-2" style="font-size:0.95rem;">
                                                                <i class="fas fa-circle me-1"></i><?php echo $acc['is_active'] ? 'Online' : 'Offline'; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="col-12">
                                                    <p class="text-center text-muted">Tidak ada data rekening bank admin.</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
// Penting: Tutup koneksi di akhir file ini, setelah semua konten dan include footer