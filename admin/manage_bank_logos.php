<?php
// File: admin/manage_bank_logos.php
session_start();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/../includes/db_connect.php';

$page_title = 'Kelola Logo Bank';

// Proses upload/ganti logo
define('BANK_LOGO_DIR', __DIR__ . '/../assets/images/bank_logos/');
if (!is_dir(BANK_LOGO_DIR)) mkdir(BANK_LOGO_DIR, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method_code'])) {
    $method_code = $_POST['method_code'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $original_filename = basename($_FILES['logo']['name']);
        $ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (in_array($ext, $allowed)) {
            $newName = 'bank_' . $method_code . '_' . time() . '.' . $ext;
            $target_file = BANK_LOGO_DIR . $newName;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                // Ambil logo lama
                $q = $conn->prepare('SELECT logo FROM payment_methods WHERE method_code = ?');
                $q->bind_param('s', $method_code);
                $q->execute();
                $q->bind_result($old_logo);
                $q->fetch();
                $q->close();
                // Update DB
                $stmt = $conn->prepare('UPDATE payment_methods SET logo = ? WHERE method_code = ?');
                $stmt->bind_param('ss', $newName, $method_code);
                $stmt->execute();
                $stmt->close();
                // Hapus file lama jika bukan default
                if ($old_logo && $old_logo !== 'default_bank_logo.png' && file_exists(BANK_LOGO_DIR . $old_logo)) {
                    unlink(BANK_LOGO_DIR . $old_logo);
                }
                $_SESSION['success_message'] = 'Logo bank berhasil diupdate!';
            } else {
                $_SESSION['error_message'] = 'Gagal upload file ke server.';
            }
        } else {
            $_SESSION['error_message'] = 'Format file tidak didukung.';
        }
    } else {
        $_SESSION['error_message'] = 'File gambar tidak ditemukan.';
    }
    header('Location: manage_bank_logos.php');
    exit;
}

// Ambil semua metode pembayaran (bank, e-wallet, dll)
$banks = $conn->query("SELECT method_code, method_name, logo FROM payment_methods ORDER BY method_type, method_name ASC");
?>
<div class="container-fluid p-4">
    <h1 class="mb-4">Kelola Logo Bank</h1>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message'];
                                            unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message'];
                                        unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    <div class="row g-4">
        <?php while ($bank = $banks->fetch_assoc()): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <img src="../assets/images/bank_logos/<?php echo htmlspecialchars($bank['logo'] ?? 'default_bank_logo.png'); ?>" alt="<?php echo htmlspecialchars($bank['method_name']); ?>" class="mb-3" style="width:64px;height:64px;object-fit:contain;background:#fff;border-radius:10px;border:1px solid #eee;">
                        <h5 class="card-title text-center mb-2" style="font-size:1.1rem;line-height:1.2;min-height:2.2em;">
                            <?php echo htmlspecialchars($bank['method_name']); ?>
                        </h5>
                        <form action="" method="post" enctype="multipart/form-data" class="w-100 mt-auto">
                            <input type="hidden" name="method_code" value="<?php echo htmlspecialchars($bank['method_code']); ?>">
                            <div class="mb-2">
                                <input type="file" name="logo" accept="image/*" class="form-control form-control-sm" required>
                            </div>
                            <button type="submit" class="btn btn-warning w-100">Ganti Logo</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>