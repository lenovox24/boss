<?php
// File: admin/edit_game.php

$page_title = "Ubah Data Game";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Cek apakah ID game ada di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID Game tidak valid.";
    header("Location: manage_games.php");
    exit();
}

$game_id = $_GET['id'];

// Logika saat form di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama_game = $_POST['nama_game'];
    $provider = $_POST['provider'];
    $kategori = $_POST['kategori'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $old_image = $_POST['old_image']; // Ambil nama gambar lama
    $game_url = isset($_POST['game_url']) ? trim($_POST['game_url']) : NULL;

    $gambar_thumbnail = $old_image; // Defaultnya adalah gambar lama
    $image_input_type = $_POST['image_input_type'] ?? 'file';

    if ($image_input_type == 'file') {
        // Logika upload gambar baru jika ada
        if (isset($_FILES['gambar_thumbnail']) && $_FILES['gambar_thumbnail']['error'] == 0) {
            $project_folder = basename(dirname(__DIR__));
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $project_folder . '/assets/images/games/';
            $original_filename = basename($_FILES["gambar_thumbnail"]["name"]);
            $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
            $unique_filename = uniqid() . '_' . time() . '.' . $imageFileType;
            $target_file = $target_dir . $unique_filename;

            $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["gambar_thumbnail"]["tmp_name"], $target_file)) {
                    $gambar_thumbnail = $unique_filename; // Gunakan nama file baru
                    // Hapus gambar lama jika ada (hanya untuk file lokal)
                    if (!empty($old_image) && !filter_var($old_image, FILTER_VALIDATE_URL) && file_exists($target_dir . $old_image)) {
                        unlink($target_dir . $old_image);
                    }
                }
            }
        }
    } else {
        // Logika input URL eksternal
        if (isset($_POST['image_url']) && !empty(trim($_POST['image_url']))) {
            $image_url = trim($_POST['image_url']);
            if (filter_var($image_url, FILTER_VALIDATE_URL)) {
                $gambar_thumbnail = $image_url; // Gunakan URL eksternal
                // Hapus gambar lama jika ada (hanya untuk file lokal)
                if (!empty($old_image) && !filter_var($old_image, FILTER_VALIDATE_URL)) {
                    $project_folder = basename(dirname(__DIR__));
                    $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $project_folder . '/assets/images/games/';
                    if (file_exists($target_dir . $old_image)) {
                        unlink($target_dir . $old_image);
                    }
                }
            }
        }
    }

    // Update data ke database
    $stmt = $conn->prepare("UPDATE games SET nama_game = ?, provider = ?, kategori = ?, gambar_thumbnail = ?, is_featured = ?, is_active = ?, game_url = ? WHERE id = ?");
    $stmt->bind_param("ssssii si", $nama_game, $provider, $kategori, $gambar_thumbnail, $is_featured, $is_active, $game_url, $game_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Data game berhasil diperbarui!";
        header("Location: manage_games.php");
        exit();
    } else {
        $message = "Error: " . $stmt->error;
        $message_type = "danger";
    }
    $stmt->close();
}

// Ambil data game saat ini dari database untuk ditampilkan di form
$stmt = $conn->prepare("SELECT * FROM games WHERE id = ?");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $game = $result->fetch_assoc();
} else {
    $_SESSION['error_message'] = "Game tidak ditemukan.";
    header("Location: manage_games.php");
    exit();
}
$stmt->close();

// Ambil daftar provider untuk dropdown
$providers = $conn->query("SELECT nama_provider FROM providers ORDER BY nama_provider ASC");
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Formulir Ubah Data Game</div>
    <div class="card-body">
        <form action="edit_game.php?id=<?php echo $game_id; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($game['gambar_thumbnail']); ?>">

            <div class="mb-3">
                <label for="nama_game" class="form-label">Nama Game</label>
                <input type="text" class="form-control" id="nama_game" name="nama_game" value="<?php echo htmlspecialchars($game['nama_game']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="provider" class="form-label">Provider</label>
                <select class="form-select" id="provider" name="provider" required>
                    <?php while ($p = $providers->fetch_assoc()): ?>
                        <option value="<?php echo $p['nama_provider']; ?>" <?php if ($game['provider'] == $p['nama_provider']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($p['nama_provider']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="kategori" class="form-label">Kategori</label>
                <select class="form-select" id="kategori" name="kategori" required>
                    <option value="Slot" <?php if ($game['kategori'] == 'Slot') echo 'selected'; ?>>Slot</option>
                    <option value="Live Casino" <?php if ($game['kategori'] == 'Live Casino') echo 'selected'; ?>>Live Casino</option>
                    <option value="Sports" <?php if ($game['kategori'] == 'Sports') echo 'selected'; ?>>Sports</option>
                    <option value="Togel" <?php if ($game['kategori'] == 'Togel') echo 'selected'; ?>>Togel</option>
                    <option value="Fishing" <?php if ($game['kategori'] == 'Fishing') echo 'selected'; ?>>Fishing</option>
                    <option value="Arcade" <?php if ($game['kategori'] == 'Arcade') echo 'selected'; ?>>Arcade</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Gambar Saat Ini</label><br>
                <?php
                $gambar_thumbnail = $game['gambar_thumbnail'];
                if (filter_var($gambar_thumbnail, FILTER_VALIDATE_URL)) {
                    // Jika URL eksternal, gunakan langsung
                    $image_src = $gambar_thumbnail;
                } else {
                    // Jika file lokal, tambahkan path folder
                    $image_src = "/assets/images/games/" . htmlspecialchars($gambar_thumbnail);
                }
                ?>
                <img src="<?php echo $image_src; ?>" alt="Gambar saat ini" width="150" class="mb-2 img-thumbnail" onerror="this.src='https://placehold.co/150x150/EEE/31343C?text=No+Image';">
            </div>

            <div class="mb-3">
                <label class="form-label">Pilih Metode Input Gambar:</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="image_input_type" id="input_file" value="file" checked>
                    <label class="form-check-label" for="input_file">
                        Upload File Gambar
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="image_input_type" id="input_url" value="url">
                    <label class="form-check-label" for="input_url">
                        URL Gambar Eksternal
                    </label>
                </div>
            </div>

            <div id="file-input-section" class="mb-3">
                <label for="gambar_thumbnail" class="form-label">Ganti Gambar Thumbnail (Opsional)</label>
                <input class="form-control" type="file" id="gambar_thumbnail" name="gambar_thumbnail">
                <div class="form-text">Biarkan kosong jika tidak ingin mengganti gambar.</div>
            </div>

            <div id="url-input-section" class="mb-3" style="display: none;">
                <label for="image_url" class="form-label">URL Gambar Eksternal</label>
                <input type="text" class="form-control" id="image_url" name="image_url"
                    value="<?php echo filter_var($game['gambar_thumbnail'], FILTER_VALIDATE_URL) ? htmlspecialchars($game['gambar_thumbnail']) : ''; ?>"
                    placeholder="https://example.com/game-image.jpg" autocomplete="off">
                <div class="form-text">Masukkan URL gambar game dari sumber eksternal.</div>
            </div>
            <div class="mb-3">
                <label for="game_url" class="form-label">URL Game</label>
                <input type="text" class="form-control" id="game_url" name="game_url" value="<?php echo htmlspecialchars($game['game_url'] ?? ''); ?>" placeholder="https://..." autocomplete="off">
                <div class="form-text">Isi dengan link game asli (jika ada).</div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" <?php if ($game['is_featured'] == 1) echo 'checked'; ?>>
                <label class="form-check-label" for="is_featured">Tampilkan sebagai Game Unggulan (Featured)?</label>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?php if ($game['is_active'] == 1) echo 'checked'; ?>>
                <label class="form-check-label" for="is_active">Aktifkan Game (Tampilkan di situs)?</label>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="manage_games.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputTypeRadios = document.querySelectorAll('input[name="image_input_type"]');
        const fileSection = document.getElementById('file-input-section');
        const urlSection = document.getElementById('url-input-section');
        const gambarInput = document.getElementById('gambar_thumbnail');
        const imageUrlInput = document.getElementById('image_url');

        // Deteksi tipe gambar saat ini
        const currentImageUrl = '<?php echo $game['gambar_thumbnail']; ?>';
        const isExternalUrl = currentImageUrl.startsWith('http://') || currentImageUrl.startsWith('https://');

        // Set radio button yang sesuai
        if (isExternalUrl) {
            document.getElementById('input_url').checked = true;
            fileSection.style.display = 'none';
            urlSection.style.display = 'block';
        } else {
            document.getElementById('input_file').checked = true;
            fileSection.style.display = 'block';
            urlSection.style.display = 'none';
        }

        // Toggle input sections
        inputTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'file') {
                    fileSection.style.display = 'block';
                    urlSection.style.display = 'none';
                    gambarInput.required = false;
                    imageUrlInput.required = false;
                } else {
                    fileSection.style.display = 'none';
                    urlSection.style.display = 'block';
                    gambarInput.required = false;
                    imageUrlInput.required = false;
                }
            });
        });
    });
</script>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>