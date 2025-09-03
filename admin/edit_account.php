<?php
$page_title = "Tambah Rekening Baru";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$account_id = $_GET['id'] ?? null;
$is_edit_mode = !is_null($account_id);
$account_data = [];

// Ambil daftar metode pembayaran untuk dropdown
$payment_methods = $conn->query("SELECT method_code, method_name FROM payment_methods WHERE is_active = 1 ORDER BY method_name ASC");

if ($is_edit_mode) {
    $page_title = "Edit Rekening";
    $stmt = $conn->prepare("SELECT * FROM admin_deposit_accounts WHERE id = ?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $account_data = $result->fetch_assoc();
    } else {
        // Jika ID tidak ditemukan, alihkan
        header("Location: manage_accounts.php");
        exit();
    }
    $stmt->close();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?php echo $page_title; ?></h1>
    <a href="manage_accounts.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="process_account.php" method="POST">
            <?php if ($is_edit_mode): ?>
                <input type="hidden" name="account_id" value="<?php echo $account_id; ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="method_code" class="form-label">Metode Pembayaran</label>
                <select class="form-select" id="method_code" name="method_code" required>
                    <option value="">- Pilih Metode -</option>
                    <?php while ($method = $payment_methods->fetch_assoc()): ?>
                        <option value="<?php echo $method['method_code']; ?>" <?php echo (isset($account_data['method_code']) && $account_data['method_code'] == $method['method_code']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($method['method_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="account_name" class="form-label">Nama Pemilik Rekening</label>
                <input type="text" class="form-control" id="account_name" name="account_name" value="<?php echo htmlspecialchars($account_data['account_name'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="account_number" class="form-label">Nomor Rekening / E-Wallet</label>
                <input type="text" class="form-control" id="account_number" name="account_number" value="<?php echo htmlspecialchars($account_data['account_number'] ?? ''); ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="min_deposit" class="form-label">Minimum Deposit</label>
                    <input type="number" class="form-control" id="min_deposit" name="min_deposit" value="<?php echo htmlspecialchars($account_data['min_deposit'] ?? '10000'); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="max_deposit" class="form-label">Maksimum Deposit</label>
                    <input type="number" class="form-control" id="max_deposit" name="max_deposit" value="<?php echo htmlspecialchars($account_data['max_deposit'] ?? '50000000'); ?>" required>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?php echo (isset($account_data['is_active']) && $account_data['is_active'] == 1) || !$is_edit_mode ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_active">Aktifkan rekening ini</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <?php echo $is_edit_mode ? 'Simpan Perubahan' : 'Tambah Rekening'; ?>
            </button>
        </form>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>