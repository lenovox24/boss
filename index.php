<?php
// File: hokiraja/index.php (REVISI FINAL FULL - Bersih dari Duplikasi & Header Dinamis)
require_once 'includes/header.php'; // Ini akan memproses redirect jika sudah login dan memanggil header navigasi yang sesuai

// Jika user sudah login, dia sudah direject oleh includes/header.php ke beranda.php.
// Jadi, kode di bawah ini hanya untuk user yang BELUM login.

function base_url($path = '')
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path_parts = explode('/', $script);
    array_pop($path_parts); // remove script filename
    $base = implode('/', $path_parts);
    return $protocol . $host . $base . '/' . ltrim($path, '/');
}

$categories_result = $conn->query("SELECT * FROM togel_results WHERE is_active = 1 ORDER BY result_date DESC, id DESC LIMIT 3"); // Query ini seharusnya untuk togel_results, bukan categories
$categories_query_result = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC"); // Query yang benar untuk categories
?>

<main class="container my-4">
    <div class="announcement-bar">
        <span class="announcement-icon"><i class="fas fa-bullhorn"></i></span>
        <div class="announcement-marquee">
            <span data-text="BOSSCUAN69 SITUS SLOT ONLINE TERPERCAYA NO 1 DI INDONESIA! PROMO MENARIK SETIAP HARI! DAFTAR DI BOSSCUAN69 SEKARANG DAN RAIH JACKPOTNYA!">BOSSCUAN69 SITUS SLOT ONLINE TERPERCAYA NO 1 DI INDONESIA! PROMO MENARIK SETIAP HARI! DAFTAR DI BOSSCUAN69 SEKARANG DAN RAIH JACKPOTNYA!</span>
        </div>
    </div>
    <!-- SLIDER BANNER PROMO OTOMATIS -->
    <div class="promo-slider" id="promoSlider">
        <div class="slider-track" id="sliderTrack">
            <?php
            $slider = $conn->query("SELECT * FROM banner_slider WHERE is_active=1 ORDER BY sort_order, id");
            $slider_dir = __DIR__ . '/assets/images/promos/';
            $slider_url = base_url('assets/images/promos/');
            $slider_items = [];
            $ada = false;
            
            if ($slider && $slider->num_rows > 0):
                while ($row = $slider->fetch_assoc()):
                    $img_path = $slider_dir . $row['image'];
                    if (file_exists($img_path)) {
                        $ada = true;
                        $slider_items[] = $row;
            ?>
                        <div class="slider-item">
                            <img src="<?= $slider_url . htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title'] ?? 'Banner Promo') ?>">
                        </div>
            <?php
                    }
                endwhile;
            endif;
            
            if (!$ada): ?>
                <div class="slider-item">
                    <img src="<?= $slider_url ?>slider1.jpg" alt="Promo Default">
                </div>
                <div class="slider-item">
                    <img src="<?= $slider_url ?>slider2.jpg" alt="Promo Default 2">
                </div>
                <div class="slider-item">
                    <img src="<?= $slider_url ?>slider3.jpg" alt="Promo Default 3">
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($ada && count($slider_items) > 1): ?>
        <div class="slider-dots" id="sliderDots">
            <?php for ($i = 0; $i < count($slider_items); $i++): ?>
                <button type="button" class="<?= $i === 0 ? 'active' : '' ?>" data-slide="<?= $i ?>"></button>
            <?php endfor; ?>
        </div>
        <?php elseif (!$ada): ?>
        <div class="slider-dots" id="sliderDots">
            <button type="button" class="active" data-slide="0"></button>
            <button type="button" data-slide="1"></button>
            <button type="button" data-slide="2"></button>
        </div>
        <?php endif; ?>
    </div>

    <!-- SEO H1 Tag -->
    <div class="text-center mb-4 d-none d-lg-block" style="background: transparent !important; padding: 15px 0;">
        <h1 style="font-size: 2rem; font-weight: 800; color: #ffc107; text-shadow: 2px 2px 8px rgba(0,0,0,0.9); margin-bottom: 8px; background: transparent !important; letter-spacing: 1px;">
            🎰 BOSSCUAN69 - Situs Slot Online Terpercaya Indonesia 🎰
        </h1>
        <p style="font-size: 0.95rem; color: #f5f5dc; text-shadow: 1px 1px 4px rgba(0,0,0,0.8); background: transparent !important; margin: 0; opacity: 0.9;">
            Bermain slot online gacor maxwin di BOSSCUAN69 dengan RTP tinggi dan bonus melimpah!
        </p>
    </div>

    <div class="d-grid gap-2 d-lg-none mb-4">
        <a href="login" class="btn btn-outline-warning">Login</a>
        <a href="daftar" class="btn btn-warning fw-bold">Daftar</a>
    </div>

    <div class="game-content-desktop d-none d-lg-grid">
        <div class="category-menu-desktop" id="category-menu-desktop">
            <div class="menu-item-img active" data-filter="all">
                <i class="fas fa-star fa-fw"></i>
                <span>Semua</span>
            </div>
            <?php if ($categories_query_result && $categories_query_result->num_rows > 0): mysqli_data_seek($categories_query_result, 0);
                while ($cat = $categories_query_result->fetch_assoc()): ?>
                    <div class="menu-item-img" data-filter="<?php echo htmlspecialchars($cat['name']); ?>">
                        <i class="<?php echo htmlspecialchars($cat['icon_class']); ?> fa-fw"></i>
                        <span><?php echo htmlspecialchars($cat['name']); ?></span>
                    </div>
            <?php endwhile;
            endif; ?>
        </div>
        <div class="provider-grid-desktop" id="provider-list-container-desktop">
        </div>
    </div>

    <div class="main-game-content-mobile d-lg-none">
        <div class="game-menu-container mt-4">
            <div class="row g-0 h-100">
                <div class="col-5">
                    <div class="menu-column category-column" id="category-menu-mobile">
                        <div class="menu-item-img active" data-filter="all">
                            <i class="fas fa-star fa-2x"></i>
                            <span class="category-label">Semua</span>
                        </div>
                        <?php if ($categories_query_result && $categories_query_result->num_rows > 0): mysqli_data_seek($categories_query_result, 0);
                            while ($cat = $categories_query_result->fetch_assoc()): ?>
                                <div class="menu-item-img" data-filter="<?php echo htmlspecialchars($cat['name']); ?>">
                                    <img src="assets/images/categories/<?php echo htmlspecialchars($cat['image']); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>">
                                    <span class="category-label"><?php echo htmlspecialchars($cat['name']); ?></span>
                                </div>
                        <?php endwhile;
                        endif; ?>
                    </div>
                </div>
                <div class="col-7">
                    <div class="menu-column provider-logo-column" id="provider-list-container-mobile">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 d-none d-lg-block">
        <div class="contact-info-sidebar mt-4">
            <h3 class="sidebar-title">HUBUNGI KAMI</h3>
            <ul class="list-unstyled">
                <li><i class="fab fa-whatsapp me-2"></i> WhatsApp: +62-812-3456-7890</li>
                <li><i class="fab fa-telegram-plane me-2"></i> Telegram: @hokiraja_support</li>
                <li><i class="fas fa-envelope me-2"></i> Email: support@hokiraja.com</li>
                <li><i class="fas fa-phone-alt me-2"></i> Telepon: (021) 12345678</li>
            </ul>
            <a href="livechat.php" class="btn btn-warning w-100 mt-3"><i class="fas fa-comments me-2"></i>Live Chat</a>
        </div>
    </div>
</main>

<div class="toast-notification" id="login-toast">
    <span id="toast-message"></span>
</div>

<?php include 'includes/footer.php'; ?>
v