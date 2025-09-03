<?php
// File: admin/add_promo.php (Versi dengan Deskripsi)
$page_title = "Tambah Promo Baru";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description']; // Ambil data deskripsi
    $link_url = $_POST['link_url'];
    $sort_order = (int)$_POST['sort_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $image_url = '';
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        // Gunakan path filesystem yang stabil relatif ke root project
        $target_dir = dirname(__DIR__) . '/assets/images/promos/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder jika belum ada
        }
        $original_filename = basename($_FILES["image_url"]["name"]);
        $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $unique_filename;
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
        if (in_array($imageFileType, $allowed_types) && move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_file)) {
            $image_url = $unique_filename; // Hanya nama file yang disimpan ke database
        }
    }

    $stmt = $conn->prepare("INSERT INTO promotions (title, description, image_url, link_url, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $title, $description, $image_url, $link_url, $is_active, $sort_order);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Promo baru berhasil ditambahkan!";
        header("Location: manage_promo");
        exit();
    }
    $stmt->close();
}
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>
<div class="card">
    <div class="card-header">Formulir Promo Baru</div>
    <div class="card-body">
        <form action="add_promo.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Judul Promo</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <!-- Input Deskripsi Baru -->
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi Singkat</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="image_url" class="form-label">Gambar Promo (Slider)</label>
                <input class="form-control" type="file" id="image_url" name="image_url" required>
            </div>
            <div class="mb-3">
                <label for="link_url" class="form-label">Link Tujuan (Opsional)</label>
                <input type="text" class="form-control" id="link_url" name="link_url">
            </div>
            <div class="mb-3">
                <label for="sort_order" class="form-label">Nomor Urut</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">Aktifkan Promo?</label>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Promo</button>
            <a href="manage_promo.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>