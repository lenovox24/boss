<?php
// File: admin/add_provider.php
$page_title = "Tambah Provider Baru";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Ambil kategori dari tabel categories
$category_options = [];
$cat_res = $conn->query("SELECT name FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $category_options[] = $row['name'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_provider = $_POST['nama_provider'];
    $kategori = $_POST['kategori'];
    $sort_order = (int)$_POST['sort_order'];
    $external_url = isset($_POST['external_url']) ? trim($_POST['external_url']) : null;
    $logo_provider = '';

    if (isset($_FILES['logo_provider']) && $_FILES['logo_provider']['error'] == 0) {
        $project_folder = basename(dirname(__DIR__));
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $project_folder . '/assets/images/providers/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $original_filename = basename($_FILES["logo_provider"]["name"]);
        $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $unique_filename;

        $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp', 'svg'];
        if (in_array($imageFileType, $allowed_types) && move_uploaded_file($_FILES["logo_provider"]["tmp_name"], $target_file)) {
            $logo_provider = $unique_filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO providers (nama_provider, kategori, logo_provider, sort_order, external_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $nama_provider, $kategori, $logo_provider, $sort_order, $external_url);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Provider baru berhasil ditambahkan!";
        header("Location: manage_games.php?tab=providers");
        exit();
    }
    $stmt->close();
}
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>
<div class="card">
    <div class="card-header">Formulir Provider Baru</div>
    <div class="card-body">
        <form action="add_provider.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nama_provider" class="form-label">Nama Provider</label>
                <input type="text" class="form-control" id="nama_provider" name="nama_provider" required>
            </div>
            <div class="mb-3">
                <label for="logo_provider" class="form-label">Logo Provider</label>
                <input class="form-control" type="file" id="logo_provider" name="logo_provider">
            </div>
            <div class="mb-3">
                <label for="kategori" class="form-label">Kategori</label>
                <select class="form-control" id="kategori" name="kategori" required>
                    <option value="">Pilih Kategori</option>
                    <?php foreach ($category_options as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="sort_order" class="form-label">Nomor Urut</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" required>
                <div class="form-text">Angka lebih kecil akan tampil lebih dulu.</div>
            </div>
            <div class="mb-3">
                <label for="external_url" class="form-label">Link Eksternal (khusus Casino)</label>
                <input type="url" class="form-control" id="external_url" name="external_url" placeholder="https://...">
                <div class="form-text">Isi hanya jika provider ini untuk kategori Casino dan ingin diarahkan ke link eksternal.</div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Provider</button>
            <a href="manage_games.php?tab=providers" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>