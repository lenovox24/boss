<?php
// File: admin/edit_promo.php (Versi dengan Deskripsi)
$page_title = "Ubah Promo";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

if (!isset($_GET['id'])) {
    header("Location: manage_promo.php");
    exit();
}
$promo_id = (int)$_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description']; // Ambil data deskripsi
    $link_url = $_POST['link_url'];
    $sort_order = (int)$_POST['sort_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $old_image = $_POST['old_image'];
    $image_url = $old_image;

    // ... (Logika upload gambar tidak berubah) ...
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/promos/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $original_filename = basename($_FILES["image_url"]["name"]);
        $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $unique_filename;
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
        if (in_array($imageFileType, $allowed_types) && move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_file)) {
            $image_url = $unique_filename;
            if (!empty($old_image) && file_exists($target_dir . $old_image)) {
                unlink($target_dir . $old_image);
            }
        }
    }

    $stmt = $conn->prepare("UPDATE promotions SET title = ?, description = ?, image_url = ?, link_url = ?, is_active = ?, sort_order = ? WHERE id = ?");
    $stmt->bind_param("ssssiii", $title, $description, $image_url, $link_url, $is_active, $sort_order, $promo_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Promo berhasil diperbarui!";
        header("Location: manage_promo.php");
        exit();
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM promotions WHERE id = ?");
$stmt->bind_param("i", $promo_id);
$stmt->execute();
$result = $stmt->get_result();
$promo = $result->fetch_assoc();
if (!$promo) {
    header("Location: manage_promo.php");
    exit();
}
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>
<div class="card">
    <div class="card-header">Formulir Ubah Promo</div>
    <div class="card-body">
        <form action="edit_promo.php?id=<?php echo $promo_id; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($promo['image_url']); ?>">
            <div class="mb-3">
                <label for="title" class="form-label">Judul Promo</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($promo['title']); ?>" required>
            </div>
            <!-- Input Deskripsi Baru -->
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi Singkat</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($promo['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Gambar Saat Ini</label><br>
                <img src="/assets/images/promos/<?php echo htmlspecialchars($promo['image_url']); ?>" alt="Gambar saat ini" style="width:auto;max-width:100%;height:auto;display:block;margin:auto;background:transparent;box-shadow:0 2px 8px rgba(0,0,0,0.08);border-radius:10px;" class="img-thumbnail">
            </div>
            <div class="mb-3">
                <label for="image_url" class="form-label">Ganti Gambar (Opsional)</label>
                <input class="form-control" type="file" id="image_url" name="image_url">
            </div>
            <div class="mb-3">
                <label for="link_url" class="form-label">Link Tujuan</label>
                <input type="text" class="form-control" id="link_url" name="link_url" value="<?php echo htmlspecialchars($promo['link_url']); ?>">
            </div>
            <div class="mb-3">
                <label for="sort_order" class="form-label">Nomor Urut</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo $promo['sort_order']; ?>" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?php if ($promo['is_active']) echo 'checked'; ?>>
                <label class="form-check-label" for="is_active">Aktifkan Promo?</label>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="manage_promo.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>