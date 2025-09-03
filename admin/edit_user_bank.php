<?php
// File: admin/edit_user_bank.php (VERSI BARU DENGAN DROPDOWN)

$page_title = "Ubah Rekening Bank";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Cek ID rekening
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID Rekening tidak valid.";
    header("Location: manage_users.php");
    exit();
}
$bank_id = $_GET['id'];

// Ambil data rekening saat ini
$stmt = $conn->prepare("SELECT * FROM user_banks WHERE id = ?");
$stmt->bind_param("i", $bank_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $bank = $result->fetch_assoc();
    $user_id = $bank['user_id'];
} else {
    $_SESSION['error_message'] = "Rekening bank tidak ditemukan.";
    header("Location: manage_users.php");
    exit();
}
$stmt->close();

// Logika saat form di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bank_name = $_POST['bank_name'];
    $account_number = $_POST['account_number'];
    $account_name = $_POST['account_name'];
    $is_primary = isset($_POST['is_primary']) ? 1 : 0;

    if ($is_primary == 1) {
        $conn->query("UPDATE user_banks SET is_primary = 0 WHERE user_id = $user_id");
    }

    $update_stmt = $conn->prepare("UPDATE user_banks SET bank_name = ?, account_number = ?, account_name = ?, is_primary = ? WHERE id = ?");
    $update_stmt->bind_param("sssii", $bank_name, $account_number, $account_name, $is_primary, $bank_id);

    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Data rekening bank berhasil diperbarui!";
        header("Location: view_user_banks.php?user_id=" . $user_id);
        exit();
    }
    $update_stmt->close();
}

// Ambil semua metode pembayaran yang aktif untuk dropdown
$payment_methods_result = $conn->query("SELECT method_code, method_name, method_type FROM payment_methods WHERE is_active = 1 ORDER BY method_type, method_name ASC");
$payment_options = [];
while ($row = $payment_methods_result->fetch_assoc()) {
    $payment_options[$row['method_type']][] = $row;
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="manage_users.php">Manajemen User</a></li>
        <li class="breadcrumb-item"><a href="view_user_banks.php?user_id=<?php echo $user_id; ?>">Kelola Rekening</a></li>
        <li class="breadcrumb-item active" aria-current="page">Ubah Rekening</li>
    </ol>
</nav>

<h1 class="mb-4">Ubah Rekening Bank</h1>

<div class="card">
    <div class="card-header">Formulir Ubah Rekening Bank</div>
    <div class="card-body">
        <form action="edit_user_bank.php?id=<?php echo $bank_id; ?>" method="post">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="bank_name" class="form-label">Nama Bank / E-Wallet</label>
                    <select class="form-select" id="bank_name" name="bank_name" required>
                        <option value="">-- Pilih Metode --</option>
                        <?php foreach ($payment_options as $type => $methods): ?>
                            <optgroup label="<?php echo $type; ?>">
                                <?php foreach ($methods as $method): ?>
                                    <option value="<?php echo $method['method_code']; ?>" <?php if ($bank['bank_name'] == $method['method_code']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($method['method_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="account_number" class="form-label">Nomor Rekening / No. HP</label>
                    <input type="text" class="form-control" id="account_number" name="account_number" value="<?php echo htmlspecialchars($bank['account_number']); ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="account_name" class="form-label">Nama Pemilik Rekening</label>
                    <input type="text" class="form-control" id="account_name" name="account_name" value="<?php echo htmlspecialchars($bank['account_name']); ?>" required>
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_primary" name="is_primary" value="1" <?php if ($bank['is_primary'] == 1) echo 'checked'; ?>>
                <label class="form-check-label" for="is_primary">Jadikan Rekening Utama?</label>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="view_user_banks.php?user_id=<?php echo $user_id; ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>