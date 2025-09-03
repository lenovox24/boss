<?php
require_once 'includes/header.php'; // Ini akan memproses redirect jika belum login
// Add body class for beranda styling
echo '<script>document.body.classList.add("beranda-page");</script>';
require_once 'includes/nav-user.php'; // Panggil header khusus user di sini
// Announcement berjalan di atas navbar user
$annText = 'RESMI TERLENGKAP DAN TERPERCAYA NO 1 DI INDONESIA! PROMO MENARIK SETIAP HARI! DAFTAR SEKARANG DAN RAIH JACKPOTNYA!';
echo '<div class="announcement-bar">
    <span class="announcement-icon"><i class="fas fa-bullhorn"></i></span>
    <div class="announcement-marquee">
        <span data-text="' . $annText . '">' . $annText . '</span>
    </div>
</div>';
require_once 'includes/sidebar-user.php'; // Sidebar user dipanggil setelah header user

$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
?>

<main class="container my-3">
    <div class="category-scroll-wrapper mb-4">
        <div class="category-list" id="beranda-category-menu">
            <a href="#" class="category-item active" data-category="all">Semua</a>
            <?php if (
                $categories && $categories->num_rows > 0
            ):
                while ($cat = $categories->fetch_assoc()): ?>
                    <?php if (strtolower($cat['name']) == 'togel'): ?>
                        <a href="togel" class="category-item" data-category="Togel">Togel</a>
                    <?php else: ?>
                        <a href="#" class="category-item" data-category="<?php echo htmlspecialchars($cat['name']); ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endif; ?>
            <?php endwhile;
            endif; ?>
        </div>
    </div>

    <div class="search-section-sticky">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2 id="game-section-title" class="section-title text-white mb-0">Semua Provider</h2>
                <div class="search-box">
                    <input type="text" id="search-game-input" class="form-control" placeholder="Cari game...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>
    </div>

    <div id="game-display-container">
        <!-- Content will be loaded dynamically -->
    </div>
</main>

<!-- Add animate.css for popup animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<?php
require_once 'includes/footer.php';
if (isset($conn)) {
    $conn->close();
}
?>