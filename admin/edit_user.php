<?php
// File: admin/edit_user.php

$page_title = "Ubah User";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Cek apakah ID user ada di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID User tidak valid.";
    header("Location: manage_users.php");
    exit();
}

$user_id = $_GET['id'];

// Logika saat form di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $balance = $_POST['balance'];
    $status = $_POST['status'];

    // Validasi sederhana
    if (empty($full_name) || empty($email) || empty($status)) {
        $message = "Semua field yang ditandai * wajib diisi.";
        $message_type = "danger";
    } else {
        // Update data ke database
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, balance = ?, status = ? WHERE id = ?");
        // s = string, d = double, i = integer
        $stmt->bind_param("sssdsi", $full_name, $email, $phone, $balance, $status, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Data user berhasil diperbarui!";
            header("Location: manage_users.php");
            exit();
        } else {
            $message = "Error saat memperbarui data: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
}

// Ambil data user saat ini dari database untuk ditampilkan di form
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    $_SESSION['error_message'] = "User tidak ditemukan.";
    header("Location: manage_users.php");
    exit();
}
$stmt->close();
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="manage_users.php">Manajemen User</a></li>
        <li class="breadcrumb-item active" aria-current="page">Ubah User: <strong><?php echo htmlspecialchars($user['username']); ?></strong></li>
    </ol>
</nav>

<h1 class="mb-4">Ubah Data User "<?php echo htmlspecialchars($user['username']); ?>"</h1>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Formulir Data User</div>
    <div class="card-body">
        <form action="edit_user.php?id=<?php echo $user_id; ?>" method="post">

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                <div class="form-text">Username tidak dapat diubah.</div>
            </div>

            <div class="mb-3">
                <label for="full_name" class="form-label">Nama Lengkap*</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Nomor Telepon</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="balance" class="form-label">Saldo</label>
                    <div class="input-group">
                        <span class="input-group-text">IDR</span>
                        <input type="number" step="0.01" class="form-control" id="balance" name="balance" value="<?php echo htmlspecialchars($user['balance']); ?>">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status Akun*</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="active" <?php if ($user['status'] == 'active') echo 'selected'; ?>>Aktif (Active)</option>
                        <option value="suspended" <?php if ($user['status'] == 'suspended') echo 'selected'; ?>>Ditangguhkan (Suspended)</option>
                        <option value="banned" <?php if ($user['status'] == 'banned') echo 'selected'; ?>>Diblokir (Banned)</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="manage_users.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>