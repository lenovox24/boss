<?php
// File: admin/view_user_banks.php (VERSI BARU DENGAN DROPDOWN)

$page_title = "Kelola Rekening Bank User";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Cek user_id
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    header("Location: manage_users.php");
    exit();
}
$user_id = $_GET['user_id'];

// Ambil data user
$user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if ($user_result->num_rows === 0) {
    header("Location: manage_users.php");
    exit();
}
$user = $user_result->fetch_assoc();
$username = $user['username'];
$user_stmt->close();

// Ambil semua rekening bank user
$banks_result = $conn->query("SELECT * FROM user_banks WHERE user_id = $user_id ORDER BY is_primary DESC, id ASC");

// Ambil semua metode pembayaran untuk form tambah
$payment_methods_result = $conn->query("SELECT method_code, method_name, method_type FROM payment_methods WHERE is_active = 1 ORDER BY method_type, method_name ASC");
$payment_options = [];
while ($row = $payment_methods_result->fetch_assoc()) {
    $payment_options[$row['method_type']][] = $row;
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="manage_users.php">Manajemen User</a></li>
        <li class="breadcrumb-item active" aria-current="page">Kelola Rekening: <strong><?php echo htmlspecialchars($username); ?></strong></li>
    </ol>
</nav>

<h1 class="mb-4">Kelola Rekening Bank untuk "<?php echo htmlspecialchars($username); ?>"</h1>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success" role="alert">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
?>

<!-- Daftar Rekening yang Sudah Ada -->
<div class="card mb-4">
    <div class="card-header">Daftar Rekening Bank</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama Bank</th>
                        <th>Nomor Rekening</th>
                        <th>Nama Pemilik</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($banks_result && $banks_result->num_rows > 0): ?>
                        <?php while ($bank = $banks_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($bank['bank_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($bank['account_number']); ?></td>
                                <td><?php echo htmlspecialchars($bank['account_name']); ?></td>
                                <td>
                                    <?php if ($bank['is_primary'] == 1): ?>
                                        <span class="badge bg-primary">Utama</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Sekunder</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_user_bank.php?id=<?php echo $bank['id']; ?>" class="btn btn-sm btn-warning" title="Ubah"><i class="fas fa-edit"></i></a>
                                    <a href="delete_user_bank.php?id=<?php echo $bank['id']; ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Anda yakin ingin menghapus rekening ini?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">User ini belum memiliki rekening bank.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Form Tambah Rekening Baru -->
<div class="card">
    <div class="card-header">Tambah Rekening Bank Baru</div>
    <div class="card-body">
        <form action="add_user_bank.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="bank_name" class="form-label">Nama Bank / E-Wallet</label>
                    <select class="form-select" id="bank_name" name="bank_name" required>
                        <option value="">-- Pilih Metode --</option>
                        <?php foreach ($payment_options as $type => $methods): ?>
                            <optgroup label="<?php echo $type; ?>">
                                <?php foreach ($methods as $method): ?>
                                    <option value="<?php echo $method['method_code']; ?>">
                                        <?php echo htmlspecialchars($method['method_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="account_number" class="form-label">Nomor Rekening / No. HP</label>
                    <input type="text" class="form-control" id="account_number" name="account_number" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="account_name" class="form-label">Nama Pemilik Rekening</label>
                    <input type="text" class="form-control" id="account_name" name="account_name" required>
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_primary" name="is_primary" value="1">
                <label class="form-check-label" for="is_primary">Jadikan Rekening Utama?</label>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Tambah Rekening</button>
        </form>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>