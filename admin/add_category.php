<?php
// File: admin/add_category.php
$page_title = "Tambah Kategori Baru";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $icon_class = $_POST['icon_class'];
    $sort_order = (int)$_POST['sort_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $image = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $project_folder = basename(dirname(__DIR__));
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $project_folder . '/assets/images/categories/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $original_filename = basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $unique_filename;

        $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp', 'svg'];
        if (in_array($imageFileType, $allowed_types) && move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $unique_filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO categories (name, image, icon_class, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $name, $image, $icon_class, $sort_order, $is_active);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Kategori baru berhasil ditambahkan!";
        header("Location: manage_games.php?tab=categories");
        exit();
    }
    $stmt->close();
}
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>
<div class="card">
    <div class="card-header">Formulir Kategori Baru</div>
    <div class="card-body">
        <form action="add_category.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Nama Kategori</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Gambar Kategori</label>
                <input class="form-control" type="file" id="image" name="image">
            </div>
            <div class="mb-3">
                <label for="icon_class" class="form-label">Kelas Ikon (Font Awesome)</label>
                <input type="text" class="form-control" id="icon_class" name="icon_class" placeholder="Contoh: fas fa-dice-d6">
            </div>
            <div class="mb-3">
                <label for="sort_order" class="form-label">Nomor Urut</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">Aktifkan Kategori?</label>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            <a href="manage_games.php?tab=categories" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>