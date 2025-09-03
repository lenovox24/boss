<?php
// File: admin/edit_category.php
$page_title = "Ubah Kategori";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

if (!isset($_GET['id'])) {
    header("Location: manage_games.php?tab=categories");
    exit();
}
$category_id = (int)$_GET['id'];

// Logika update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $icon_class = $_POST['icon_class'];
    $sort_order = (int)$_POST['sort_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $old_image = $_POST['old_image'];
    $image = $old_image;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $project_folder = basename(dirname(__DIR__));
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $project_folder . '/assets/images/categories/';
        $original_filename = basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $unique_filename;
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp', 'svg'];
        if (in_array($imageFileType, $allowed_types) && move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $unique_filename;
            if (!empty($old_image) && file_exists($target_dir . $old_image)) {
                unlink($target_dir . $old_image);
            }
        }
    }

    $stmt = $conn->prepare("UPDATE categories SET name = ?, image = ?, icon_class = ?, sort_order = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("sssiii", $name, $image, $icon_class, $sort_order, $is_active, $category_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Kategori berhasil diperbarui!";
        header("Location: manage_games.php?tab=categories");
        exit();
    }
    $stmt->close();
}

// Ambil data kategori saat ini
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
if (!$category) {
    header("Location: manage_games.php?tab=categories");
    exit();
}
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>
<div class="card">
    <div class="card-header">Formulir Ubah Kategori</div>
    <div class="card-body">
        <form action="edit_category.php?id=<?php echo $category_id; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($category['image']); ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Nama Kategori</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Gambar Saat Ini</label><br>
                <img src="/assets/images/categories/<?php echo htmlspecialchars($category['image']); ?>" alt="Gambar saat ini" width="80" class="img-thumbnail">
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Ganti Gambar (Opsional)</label>
                <input class="form-control" type="file" id="image" name="image">
            </div>
            <div class="mb-3">
                <label for="icon_class" class="form-label">Kelas Ikon (Font Awesome)</label>
                <input type="text" class="form-control" id="icon_class" name="icon_class" value="<?php echo htmlspecialchars($category['icon_class']); ?>" placeholder="Contoh: fas fa-dice-d6">
            </div>
            <div class="mb-3">
                <label for="sort_order" class="form-label">Nomor Urut</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo $category['sort_order']; ?>" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?php if ($category['is_active']) echo 'checked'; ?>>
                <label class="form-check-label" for="is_active">Aktifkan Kategori?</label>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="manage_games.php?tab=categories" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>