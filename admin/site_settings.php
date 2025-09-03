<?php
// File: admin/site_settings.php (REVISI FINAL: Pengaturan Gambar QRIS di admin_deposit_accounts)
$page_title = "Pengaturan Situs";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// === PROSES BACKEND: UPLOAD & HAPUS GAMBAR PAYMENT/PROVIDER ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ppinfo_action'])) {
    $targetDir = dirname(__DIR__) . '/assets/images/payment_provider/';
    $targetDirWeb = '../assets/images/payment_provider/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    if ($_POST['ppinfo_action'] === 'add') {
        $type = $_POST['type'] === 'provider' ? 'provider' : 'payment';
        $image = $_FILES['image'] ?? null;
        if ($image && $image['tmp_name']) {
            $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            if (in_array($ext, $allowed)) {
                $newName = uniqid('ppimg_') . '.' . $ext;
                if (move_uploaded_file($image['tmp_name'], $targetDir . $newName)) {
                    $insert = $conn->query("INSERT INTO payment_provider_info (type, image) VALUES ('{$type}', '{$newName}')");
                    if (!$insert) {
                        $_SESSION['error_message'] = 'Gagal menyimpan data ke database: ' . $conn->error;
                    } else {
                        $_SESSION['success_message'] = 'Gambar berhasil diupload dan disimpan!';
                    }
                } else {
                    $_SESSION['error_message'] = 'Gagal upload file ke server.';
                }
            } else {
                $_SESSION['error_message'] = 'Format file tidak didukung.';
            }
        } else {
            $_SESSION['error_message'] = 'File gambar tidak ditemukan.';
        }
        header('Location: site_settings.php#ppinfo');
        exit;
    }
    if ($_POST['ppinfo_action'] === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $q = $conn->query("SELECT image FROM payment_provider_info WHERE id=$id");
        $row = $q ? $q->fetch_assoc() : null;
        if ($row && $row['image'] && file_exists($targetDir . $row['image'])) {
            unlink($targetDir . $row['image']);
        }
        $conn->query("DELETE FROM payment_provider_info WHERE id=$id");
        $_SESSION['success_message'] = 'Gambar berhasil dihapus!';
        header('Location: site_settings.php#ppinfo');
        exit;
    }
    if ($_POST['ppinfo_action'] === 'set_status' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $is_active = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;
        $conn->query("UPDATE payment_provider_info SET is_active=$is_active WHERE id=$id");
        $_SESSION['success_message'] = 'Status berhasil diubah!';
        header('Location: site_settings.php#ppinfo');
        exit;
    }
}

// === PROSES BACKEND: UPLOAD & HAPUS GAMBAR BANNER SLIDER ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slider_action'])) {
    $targetDir = dirname(__DIR__) . '/assets/images/promos/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    if ($_POST['slider_action'] === 'add') {
        $image = $_FILES['slider_image'] ?? null;
        if ($image && $image['tmp_name']) {
            $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif', 'ico'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            if (!in_array($ext, $allowed)) {
                $_SESSION['error_message'] = 'Format file tidak didukung.';
            } elseif ($image['size'] > $maxSize) {
                $_SESSION['error_message'] = 'Ukuran file maksimal 2MB.';
            } else {
                $newName = uniqid('slider_') . '.' . $ext;
                if (move_uploaded_file($image['tmp_name'], $targetDir . $newName)) {
                    $q = $conn->query("SELECT MAX(sort_order) as max_sort FROM banner_slider");
                    $maxSort = ($q && $row = $q->fetch_assoc()) ? (int)$row['max_sort'] : 0;
                    $conn->query("INSERT INTO banner_slider (image, sort_order) VALUES ('{$newName}', " . ($maxSort + 1) . ")");
                    $_SESSION['success_message'] = 'Banner berhasil diupload!';
                } else {
                    $_SESSION['error_message'] = 'Gagal upload file ke server.';
                }
            }
        } else {
            $_SESSION['error_message'] = 'File gambar tidak ditemukan.';
        }
        header('Location: site_settings.php#slider');
        exit;
    }
    if ($_POST['slider_action'] === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $q = $conn->query("SELECT image FROM banner_slider WHERE id=$id");
        $row = $q ? $q->fetch_assoc() : null;
        if ($row && $row['image'] && file_exists($targetDir . $row['image'])) {
            unlink($targetDir . $row['image']);
        }
        $conn->query("DELETE FROM banner_slider WHERE id=$id");
        $_SESSION['success_message'] = 'Banner berhasil dihapus!';
        header('Location: site_settings.php#slider');
        exit;
    }
}

// Path upload gambar untuk logo umum (main_logo, admin_logo, footer_logo)
$upload_dir_general = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/'; // Ganti ke path dinamis
// Path upload gambar untuk QRIS
$upload_dir_qris = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/qris/'; // Ganti ke path dinamis

// Pastikan folder qris ada
if (!is_dir($upload_dir_qris)) {
    mkdir($upload_dir_qris, 0777, true);
}

// Fungsi untuk mendapatkan base URL dinamis
function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $protocol . $host;
}
$base_url = get_base_url();

// Logika untuk menyimpan perubahan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Loop melalui semua data POST untuk menyimpan pengaturan teks (site_settings)
    foreach ($_POST as $key => $value) {
        // Abaikan old_logos, qris_old_image, dan qris_target_method_code
        if ($key !== 'old_logos' && $key !== 'qris_old_image' && $key !== 'qris_target_method_code') {
            $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_name = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
            $stmt->close();
        }
    }

    // --- LOGIKA UNTUK MENYIMPAN LOGO UMUM (main_logo, admin_logo, footer_logo) ---
    $old_logos = $_POST['old_logos'] ?? []; // Pastikan ini terdefinisi
    foreach (['main_logo', 'admin_logo', 'footer_logo'] as $logo_type) {
        if (isset($_FILES[$logo_type]) && $_FILES[$logo_type]['error'] == 0) {
            $original_filename = basename($_FILES[$logo_type]["name"]);
            $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
            $unique_filename = $logo_type . '_' . time() . '.' . $imageFileType;
            $target_file = $upload_dir_general . $unique_filename;
            $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp', 'svg'];

            if (in_array($imageFileType, $allowed_types) && move_uploaded_file($_FILES[$logo_type]["tmp_name"], $target_file)) {
                // Update nama file baru di database site_settings
                $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_name = ?");
                $stmt->bind_param("ss", $unique_filename, $logo_type);
                $stmt->execute();
                $stmt->close();

                // Hapus logo lama jika ada
                $old_logo_file = $old_logos[$logo_type] ?? '';
                if (!empty($old_logo_file) && file_exists($upload_dir_general . $old_logo_file)) {
                    unlink($upload_dir_general . $old_logo_file);
                }
            } else {
                $_SESSION['error_message'] = "Gagal mengunggah logo $logo_type atau tipe file tidak diizinkan.";
            }
        }
    }

    // --- LOGIKA BARU UNTUK MENYIMPAN/MENGHAPUS GAMBAR QRIS di admin_deposit_accounts ---
    $qris_target_method_code = $_POST['qris_target_method_code'] ?? 'QRIS_STATIC'; // Ambil dari hidden input atau default
    $qris_old_image = $_POST['qris_old_image'] ?? ''; // Ambil nama file lama dari hidden input

    if (isset($_FILES['qris_image']) && $_FILES['qris_image']['error'] == 0) { // Jika ada file baru diupload
        $qris_original_filename = basename($_FILES['qris_image']['name']);
        $qris_imageFileType = strtolower(pathinfo($qris_original_filename, PATHINFO_EXTENSION));
        $qris_unique_filename = 'qris_' . time() . '.' . $qris_imageFileType;
        $qris_target_file = $upload_dir_qris . $qris_unique_filename;
        $qris_allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp', 'svg'];

        // Validasi nama file hanya nama file, bukan path atau input lain
        if (preg_match('/^[a-zA-Z0-9_\-\.]+$/', $qris_unique_filename) && in_array($qris_imageFileType, $qris_allowed_types) && move_uploaded_file($_FILES['qris_image']['tmp_name'], $qris_target_file)) {
            // Simpan URL dinamis ke database (hanya nama file, URL dibangun saat tampil)
            $stmt = $conn->prepare("UPDATE admin_deposit_accounts SET qris_image_url = ? WHERE method_code = ?");
            $stmt->bind_param("ss", $qris_unique_filename, $qris_target_method_code);
            if ($stmt->execute()) {
                // Hapus gambar QRIS lama jika ada dan berbeda dengan file baru
                if (!empty($qris_old_image) && $qris_old_image !== $qris_unique_filename && file_exists($upload_dir_qris . $qris_old_image)) {
                    unlink($upload_dir_qris . $qris_old_image);
                }
                $_SESSION['success_message'] = "Gambar QRIS berhasil diupload!";
            } else {
                $_SESSION['error_message'] = "Gagal update gambar QRIS di database: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Gagal mengunggah file QRIS atau tipe file tidak diizinkan.";
        }
        // Redirect agar tidak lanjut ke proses hapus
        header("Location: site_settings.php");
        exit;
    } elseif (isset($_POST['delete_qris_image']) && $_POST['delete_qris_image'] === '1') { // Jika tombol hapus diklik
        // Hapus dari database (set menjadi NULL)
        $stmt = $conn->prepare("UPDATE admin_deposit_accounts SET qris_image_url = NULL WHERE method_code = ?");
        $stmt->bind_param("s", $qris_target_method_code);
        if ($stmt->execute()) {
            // Hapus file fisik
            if (!empty($qris_old_image) && file_exists($upload_dir_qris . $qris_old_image)) {
                unlink($upload_dir_qris . $qris_old_image);
            }
            $_SESSION['success_message'] = "Gambar QRIS berhasil dihapus!";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus gambar QRIS dari database: " . $stmt->error;
        }
        $stmt->close();
        header("Location: site_settings.php");
        exit;
    }

    $_SESSION['success_message'] = ($_SESSION['success_message'] ?? "") . " Pengaturan situs berhasil diperbarui!";
    // Redirect untuk mencegah resubmit form
    header("Location: site_settings.php");
    exit();
}

// Ambil semua pengaturan dari database site_settings untuk ditampilkan di form
$settings_result = $conn->query("SELECT * FROM site_settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_name']] = $row['setting_value'];
}

// === BARU: Ambil URL Gambar QRIS dari admin_deposit_accounts untuk ditampilkan ===
$qris_target_method_code = 'QRIS_STATIC'; // <-- PENTING: GANTI dengan method_code QRIS Anda
$qris_image_data = $conn->prepare("SELECT qris_image_url FROM admin_deposit_accounts WHERE method_code = ?");
$qris_image_data->bind_param("s", $qris_target_method_code);
$qris_image_data->execute();
$qris_image_result = $qris_image_data->get_result();
$qris_current_image_url = '';
if ($qris_image_result->num_rows > 0) {
    $qris_current_image_url = $qris_image_result->fetch_assoc()['qris_image_url'];
}
$qris_image_data->close();

// --- LOGIKA UNTUK MENYIMPAN LOGO UMUM ---
$old_logos = $_POST['old_logos'] ?? [];
// TAMBAHKAN 'admin_profile_picture' KE DALAM ARRAY INI
foreach (['main_logo', 'admin_logo', 'footer_logo', 'admin_profile_picture'] as $logo_type) {
}
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>

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

<form action="site_settings.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="qris_target_method_code" value="<?php echo htmlspecialchars($qris_target_method_code); ?>">
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Pengaturan Teks & Konten</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="site_title" class="form-label">Judul Situs (Title Tag)</label>
                        <input type="text" class="form-control" id="site_title" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="footer_tagline" class="form-label">Teks Tagline Footer</label>
                        <textarea class="form-control" id="footer_tagline" name="footer_tagline" rows="2"><?php echo htmlspecialchars($settings['footer_tagline'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Pengaturan Logo & QRIS</div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Logo Utama (Header)</label><br>
                        <img src="/assets/images/<?php echo htmlspecialchars($settings['main_logo'] ?? 'default_logo.png'); ?>" height="40" class="img-thumbnail bg-dark border-dark mb-2">
                        <input type="hidden" name="old_logos[main_logo]" value="<?php echo htmlspecialchars($settings['main_logo'] ?? ''); ?>">
                        <input class="form-control" type="file" name="main_logo" accept="image/*">
                    </div>
                    <hr>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Logo Admin (Sidebar)</label><br>
                        <img src="/assets/images/<?php echo htmlspecialchars($settings['admin_logo'] ?? 'default_admin_logo.png'); ?>" height="40" class="img-thumbnail bg-dark border-dark mb-2">
                        <input type="hidden" name="old_logos[admin_logo]" value="<?php echo htmlspecialchars($settings['admin_logo'] ?? ''); ?>">
                        <input class="form-control" type="file" name="admin_logo" accept="image/*">
                    </div>
                    <hr>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Logo Footer (Mobile)</label><br>
                        <img src="/assets/images/<?php echo htmlspecialchars($settings['footer_logo'] ?? 'default_footer_logo.png'); ?>" height="40" class="img-thumbnail bg-dark border-dark mb-2">
                        <input type="hidden" name="old_logos[footer_logo]" value="<?php echo htmlspecialchars($settings['footer_logo'] ?? ''); ?>">
                        <input class="form-control" type="file" name="footer_logo" accept="image/*">
                    </div>
                    <hr>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Foto Profil Admin (Live Chat)</label><br>
                        <img src="/assets/images/<?php echo htmlspecialchars($settings['admin_profile_picture'] ?? 'default_admin_profile.png'); ?>" height="40" class="img-thumbnail bg-dark border-dark mb-2 rounded-circle">
                        <input type="hidden" name="old_logos[admin_profile_picture]" value="<?php echo htmlspecialchars($settings['admin_profile_picture'] ?? ''); ?>">
                        <input class="form-control" type="file" name="admin_profile_picture" accept="image/*">
                        <small class="form-text text-muted">Disarankan gambar persegi (1:1).</small>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Gambar QRIS (Untuk Deposit)</label><br>
                        <?php if (!empty($qris_current_image_url)): ?>
                            <img src="/assets/images/qris/<?php echo htmlspecialchars($qris_current_image_url); ?>" height="100" class="img-thumbnail bg-dark border-dark mb-2">
                            <div class="form-check form-check-inline ms-2">
                                <input class="form-check-input" type="checkbox" id="delete_qris_image" name="delete_qris_image" value="1">
                                <label class="form-check-label text-danger" for="delete_qris_image">Hapus Gambar Ini</label>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Gambar QRIS belum diatur.</p>
                        <?php endif; ?>
                        <input type="hidden" name="qris_old_image" value="<?php echo htmlspecialchars($qris_current_image_url); ?>">
                        <input class="form-control mt-2" type="file" name="qris_image" accept="image/*">
                        <small class="form-text text-muted">Upload gambar QRIS Anda (JPG, PNG, WebP, dll.). Disarankan ukuran persegi.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary w-100">Simpan Semua Pengaturan</button>
    </div>
</form>

<?php
// === SECTION: Kelola Gambar & Deskripsi Metode Pembayaran/Provider ===
?>
<hr class="my-5">
<h2 class="mb-3">Kelola Gambar Metode Pembayaran/Provider</h2>
<?php
$ppinfo = $conn->query("SELECT * FROM payment_provider_info ORDER BY type, sort_order, name");
?>
<div class="card mb-4">
    <div class="card-body">
        <form action="site_settings.php" method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
            <input type="hidden" name="ppinfo_action" value="add">
            <div class="col-md-3">
                <label class="form-label">Tipe</label>
                <select name="type" class="form-select" required>
                    <option value="payment">Metode Pembayaran</option>
                    <option value="provider">Provider</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Upload Gambar</label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success">Tambah</button>
            </div>
        </form>
        <hr>
        <div class="row g-3">
            <?php foreach ($ppinfo as $row): ?>
                <div class="col-6 col-md-2 text-center">
                    <div class="border rounded p-2 bg-light position-relative">
                        <span class="badge bg-<?= $row['type'] === 'provider' ? 'primary' : 'warning' ?> position-absolute top-0 start-0 m-1">
                            <?= $row['type'] === 'provider' ? 'Provider' : 'Payment' ?>
                        </span>
                        <img src="/assets/images/payment_provider/<?= htmlspecialchars($row['image']) ?>" alt="img" class="img-fluid mb-2" style="max-height:60px;max-width:100%;object-fit:contain;">
                        <!-- Status Online/Offline -->
                        <form action="site_settings.php" method="post" style="display:inline;">
                            <input type="hidden" name="ppinfo_action" value="set_status">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <span class="position-absolute top-0 end-0 m-1" title="Status">
                                <?php if ($row['is_active']): ?>
                                    <span class="status-dot bg-success"></span>
                                <?php else: ?>
                                    <span class="status-dot bg-secondary"></span>
                                <?php endif; ?>
                            </span>
                            <select name="is_active" class="form-select form-select-sm mt-1" onchange="this.form.submit()">
                                <option value="1" <?= $row['is_active'] ? 'selected' : '' ?>>Online</option>
                                <option value="0" <?= !$row['is_active'] ? 'selected' : '' ?>>Offline</option>
                            </select>
                        </form>
                        <form action="site_settings.php" method="post" style="display:inline;">
                            <input type="hidden" name="ppinfo_action" value="delete">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Hapus gambar ini?')">Hapus</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// === SECTION: Kelola Banner Slider Promo ===
?>
<hr class="my-5">
<h2 class="mb-3">Kelola Banner Slider Promo</h2>
<?php
$slider = $conn->query("SELECT * FROM banner_slider ORDER BY sort_order, id");
?>
<div class="card mb-4">
    <div class="card-body">
        <form action="site_settings.php" method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
            <input type="hidden" name="slider_action" value="add">
            <div class="col-md-6">
                <label class="form-label">Upload Gambar Banner</label>
                <input type="file" name="slider_image" class="form-control" accept="image/*" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success">Tambah Banner</button>
            </div>
        </form>
        <hr>
        <div class="row g-3">
            <?php foreach ($slider as $row): ?>
                <div class="col-6 col-md-3 text-center">
                    <div class="border rounded p-2 bg-light position-relative">
                        <img src="/assets/images/promos/<?= htmlspecialchars($row['image']) ?>" alt="banner" class="img-fluid mb-2" style="max-height:90px;max-width:100%;object-fit:cover;">
                        <div class="mb-2">
                            <span class="badge bg-<?= $row['is_active'] ? 'success' : 'secondary' ?>"> <?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?> </span>
                            <span class="badge bg-dark">Urutan: <?= $row['sort_order'] ?></span>
                        </div>
                        <form action="site_settings.php" method="post" style="display:inline;">
                            <input type="hidden" name="slider_action" value="delete">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus banner ini?')">Hapus</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>