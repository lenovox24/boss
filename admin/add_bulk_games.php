<?php
// File: admin/add_bulk_games.php (VERSI FINAL DENGAN DROPDOWN DINAMIS)

$page_title = "Tambah Game Massal (Bulk)";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$message = '';
$message_type = '';

// Cek jika form telah di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data umum dari form
    $provider_id = $_POST['provider'];
    $kategori = $_POST['kategori'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $input_type = $_POST['input_type']; // 'file' atau 'url'

    // Ambil nama provider dari database berdasarkan ID
    $provider_stmt = $conn->prepare("SELECT nama_provider FROM providers WHERE id = ?");
    $provider_stmt->bind_param("i", $provider_id);
    $provider_stmt->execute();
    $provider_result = $provider_stmt->get_result();
    $provider_data = $provider_result->fetch_assoc();
    $provider_name = $provider_data['nama_provider'];
    $provider_stmt->close();

    $uploaded_count = 0;
    $error_count = 0;

    if ($input_type == 'file') {
        // --- LOGIKA UPLOAD BANYAK GAMBAR DARI FILE ---
        if (isset($_FILES['gambar_games']) && !empty(array_filter($_FILES['gambar_games']['name']))) {

            $project_folder = basename(dirname(__DIR__));
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $project_folder . '/assets/images/games/';

            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {
                    $_SESSION['error_message'] = "Error: Gagal membuat folder di '{$target_dir}'. Silakan buat manual.";
                    header("Location: manage_games.php");
                    exit();
                }
            }

            // Loop untuk setiap file yang di-upload
            foreach ($_FILES['gambar_games']['name'] as $key => $val) {
                if ($_FILES['gambar_games']['error'][$key] == 0) {
                    $original_filename = basename($_FILES["gambar_games"]["name"][$key]);

                    $nama_game = pathinfo($original_filename, PATHINFO_FILENAME);
                    $nama_game = str_replace(['_', '-'], ' ', $nama_game);
                    $nama_game = ucwords($nama_game);

                    $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
                    $unique_filename = uniqid() . '_' . time() . '_' . $key . '.' . $imageFileType;
                    $target_file = $target_dir . $unique_filename;

                    $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp', 'svg'];
                    if (in_array($imageFileType, $allowed_types)) {
                        if (move_uploaded_file($_FILES["gambar_games"]["tmp_name"][$key], $target_file)) {
                            // Ambil URL dari input game_urls[]
                            $game_url = isset($_POST['game_urls'][$key]) ? trim($_POST['game_urls'][$key]) : NULL;
                            $stmt = $conn->prepare("INSERT INTO games (nama_game, provider, kategori, gambar_thumbnail, is_featured, is_active, game_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("ssssiss", $nama_game, $provider_name, $kategori, $unique_filename, $is_featured, $is_active, $game_url);
                            if ($stmt->execute()) {
                                $uploaded_count++;
                            }
                            $stmt->close();
                        } else {
                            $error_count++;
                        }
                    } else {
                        $error_count++;
                    }
                }
            }
        } else {
            $message = "Silakan pilih setidaknya satu file gambar untuk diunggah.";
            $message_type = "danger";
        }
    } else {
        // --- LOGIKA INPUT URL GAMBAR EKSTERNAL ---
        if (isset($_POST['image_urls']) && !empty(array_filter($_POST['image_urls']))) {

            foreach ($_POST['image_urls'] as $key => $image_url) {
                $image_url = trim($image_url);
                if (!empty($image_url)) {
                    // Validasi URL
                    if (filter_var($image_url, FILTER_VALIDATE_URL)) {
                        // Ambil nama game dari input nama_games[]
                        $nama_game = isset($_POST['nama_games'][$key]) ? trim($_POST['nama_games'][$key]) : '';
                        if (empty($nama_game)) {
                            // Jika nama game kosong, coba ambil dari URL
                            $url_parts = parse_url($image_url);
                            $path_parts = pathinfo($url_parts['path']);
                            $nama_game = str_replace(['_', '-'], ' ', $path_parts['filename']);
                            $nama_game = ucwords($nama_game);
                        }

                        // Ambil URL game dari input game_urls[]
                        $game_url = isset($_POST['game_urls'][$key]) ? trim($_POST['game_urls'][$key]) : NULL;

                        // Simpan URL gambar langsung ke database
                        $stmt = $conn->prepare("INSERT INTO games (nama_game, provider, kategori, gambar_thumbnail, is_featured, is_active, game_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssiss", $nama_game, $provider_name, $kategori, $image_url, $is_featured, $is_active, $game_url);
                        if ($stmt->execute()) {
                            $uploaded_count++;
                        } else {
                            $error_count++;
                        }
                        $stmt->close();
                    } else {
                        $error_count++;
                    }
                }
            }
        } else {
            $message = "Silakan masukkan setidaknya satu URL gambar.";
            $message_type = "danger";
        }
    }

    if ($uploaded_count > 0) {
        $_SESSION['success_message'] = "$uploaded_count game baru berhasil ditambahkan!";
    }
    if ($error_count > 0) {
        $_SESSION['error_message'] = "$error_count item gagal diproses.";
    }

    if (empty($message)) {
        header("Location: manage_games.php?tab=games");
        exit();
    }
}

// ========================================================
// === PERUBAHAN UTAMA: AMBIL DATA PROVIDER & KATEGORI ===
// ========================================================
$providers = $conn->query("SELECT * FROM providers ORDER BY nama_provider ASC");
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>
<p class="text-muted">Fitur ini memungkinkan Anda menambah banyak game sekaligus untuk satu provider dan kategori yang sama.</p>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Formulir Tambah Game Massal</div>
    <div class="card-body">
        <form action="add_bulk_games.php" method="post" enctype="multipart/form-data">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="provider" class="form-label">Provider</label>
                    <select class="form-select" id="provider" name="provider" required>
                        <option value="">-- Pilih Provider --</option>
                        <?php if ($providers && $providers->num_rows > 0): while ($p = $providers->fetch_assoc()): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama_provider']); ?></option>
                        <?php endwhile;
                        endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php if ($categories && $categories->num_rows > 0): while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endwhile;
                        endif; ?>
                    </select>
                </div>
            </div>

            <!-- Pilihan Input Type -->
            <div class="mb-3">
                <label class="form-label">Pilih Metode Input Gambar:</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="input_type" id="input_file" value="file" checked>
                    <label class="form-check-label" for="input_file">
                        Upload File Gambar
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="input_type" id="input_url" value="url">
                    <label class="form-check-label" for="input_url">
                        URL Gambar Eksternal
                    </label>
                </div>
            </div>

            <!-- Input File Upload -->
            <div id="file-input-section" class="mb-3">
                <label for="gambar_games" class="form-label">Pilih Gambar Game (Bisa Pilih Banyak)</label>
                <input class="form-control" type="file" id="gambar_games" name="gambar_games[]" multiple accept="image/*">
                <div id="preview-url-list" class="mt-3"></div>
                <div class="form-text">Nama game akan otomatis diambil dari nama file gambar. Contoh: "Gates of Olympus.png" akan menjadi "Gates of Olympus".<br>Setelah memilih gambar, silakan isi URL game untuk masing-masing gambar.</div>
            </div>

            <!-- Input URL Eksternal -->
            <div id="url-input-section" class="mb-3" style="display: none;">
                <label class="form-label">URL Gambar Game (Satu URL per baris)</label>
                <div id="url-inputs">
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <input type="text" class="form-control" name="image_urls[]" placeholder="https://example.com/game-image.jpg" autocomplete="off">
                        <input type="text" class="form-control" name="nama_games[]" placeholder="Nama Game" style="max-width:200px;" autocomplete="off">
                        <input type="text" class="form-control" name="game_urls[]" placeholder="URL Game" style="max-width:200px;" autocomplete="off">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-url" style="display:none;">Hapus</button>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-url">+ Tambah URL</button>
                <div class="form-text">Masukkan URL gambar game, nama game (opsional), dan URL game untuk masing-masing item.</div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const inputTypeRadios = document.querySelectorAll('input[name="input_type"]');
                    const fileSection = document.getElementById('file-input-section');
                    const urlSection = document.getElementById('url-input-section');
                    const gambarInput = document.getElementById('gambar_games');
                    const previewList = document.getElementById('preview-url-list');
                    const urlInputs = document.getElementById('url-inputs');
                    const addUrlBtn = document.getElementById('add-url');

                    // Toggle input sections
                    inputTypeRadios.forEach(radio => {
                        radio.addEventListener('change', function() {
                            if (this.value === 'file') {
                                fileSection.style.display = 'block';
                                urlSection.style.display = 'none';
                                gambarInput.required = true;
                                document.querySelectorAll('input[name="image_urls[]"]').forEach(input => input.required = false);
                            } else {
                                fileSection.style.display = 'none';
                                urlSection.style.display = 'block';
                                gambarInput.required = false;
                                document.querySelectorAll('input[name="image_urls[]"]').forEach(input => input.required = true);
                            }
                        });
                    });

                    // File upload preview
                    gambarInput.addEventListener('change', function() {
                        previewList.innerHTML = '';
                        Array.from(this.files).forEach((file, idx) => {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const div = document.createElement('div');
                                div.className = 'mb-2 d-flex align-items-center gap-2';
                                div.innerHTML = `
                                <img src="${e.target.result}" alt="preview" style="width:60px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #ccc;">
                                <input type="text" class="form-control" name="game_urls[]" placeholder="URL Game untuk: ${file.name}" style="max-width:350px;" autocomplete="off">
                            `;
                                previewList.appendChild(div);
                            };
                            reader.readAsDataURL(file);
                        });
                    });

                    // Add URL input
                    addUrlBtn.addEventListener('click', function() {
                        const div = document.createElement('div');
                        div.className = 'mb-2 d-flex align-items-center gap-2';
                        div.innerHTML = `
                            <input type="text" class="form-control" name="image_urls[]" placeholder="https://example.com/game-image.jpg" autocomplete="off" required>
                            <input type="text" class="form-control" name="nama_games[]" placeholder="Nama Game" style="max-width:200px;" autocomplete="off">
                            <input type="text" class="form-control" name="game_urls[]" placeholder="URL Game" style="max-width:200px;" autocomplete="off">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-url">Hapus</button>
                        `;
                        urlInputs.appendChild(div);
                        updateRemoveButtons();
                    });

                    // Remove URL input
                    function updateRemoveButtons() {
                        const removeButtons = document.querySelectorAll('.remove-url');
                        removeButtons.forEach(btn => {
                            btn.addEventListener('click', function() {
                                this.parentElement.remove();
                                updateRemoveButtons();
                            });
                        });
                    }

                    updateRemoveButtons();
                });
            </script>

            <hr>
            <p class="fw-bold">Pengaturan Umum untuk Semua Game yang Diupload:</p>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1">
                <label class="form-check-label" for="is_featured">Tampilkan sebagai Game Unggulan (Featured)?</label>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">Aktifkan Game (Tampilkan di situs)?</label>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-2"></i>Upload & Simpan Semua</button>
            <a href="manage_games.php?tab=games" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>