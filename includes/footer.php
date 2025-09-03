<?php
// File: includes/footer.php (REVISI FINAL: Grid payment/provider & tagline hanya di index.php & beranda.php)
?>
<nav class="mobile-footer-nav d-lg-none fixed-bottom">
    <?php
    // Dapatkan nama file saat ini untuk menyorot menu yang aktif
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    // Tentukan navigasi footer berdasarkan status login
    if (isset($_SESSION['user_id'])) {
    ?>
        <a href="beranda" class="nav-item <?php echo ($current_page == 'beranda') ? 'active' : ''; ?>">
            <div class="nav-icon-block"><i class="fas fa-house-chimney"></i></div>
            <span class="nav-text">Home</span>
        </a>
        <a href="transaksi" class="nav-item <?php echo ($current_page == 'transaksi') ? 'active' : ''; ?>">
            <div class="nav-icon-block"><i class="fas fa-chart-line"></i></div>
            <span class="nav-text">Transaksi</span>
        </a>
        <a href="promo" class="nav-item <?php echo ($current_page == 'promo') ? 'active' : ''; ?>">
            <div class="nav-icon-block"><i class="fas fa-fire"></i></div>
            <span class="nav-text">Promosi</span>
        </a>
        <a href="livechat" class="nav-item <?php echo ($current_page == 'livechat') ? 'active' : ''; ?>">
            <div class="nav-icon-block"><i class="fas fa-headset"></i></div>
            <span class="nav-text">Live Chat</span>
        </a>
        <a href="profil" class="nav-item <?php echo ($current_page == 'profil') ? 'active' : ''; ?>">
            <div class="nav-icon-block"><i class="fas fa-user-circle"></i></div>
            <span class="nav-text">Profil</span>
        </a>
    <?php
    } else {
    ?>
        <a href="index" class="nav-item <?php echo ($current_page == 'index') ? 'active' : ''; ?>">
            <div class="nav-icon-block"><i class="fas fa-house-chimney"></i></div>
            <span class="nav-text">Home</span>
        </a>
        <a href="promo" class="nav-item <?php echo ($current_page == 'promo') ? 'active' : ''; ?>">
            <div class="nav-icon-block"><i class="fas fa-fire"></i></div>
            <span class="nav-text">Promo</span>
        </a>
        <a href="daftar" class="nav-item register-btn <?php echo ($current_page == 'daftar') ? 'active' : ''; ?>">
            <div class="nav-icon-block"><i class="fas fa-rocket"></i></div>
            <span class="nav-text">Daftar</span>
        </a>
        <a href="livechat" class="nav-item <?php echo ($current_page == 'livechat') ? 'active' : ''; ?>">
            <div class="nav-icon-block"><i class="fas fa-headset"></i></div>
            <span class="nav-text">Live Chat</span>
        </a>
        <a href="login" class="nav-item <?php echo ($current_page == 'login') ? 'active' : ''; ?>">
            <div class="nav-icon-block"><i class="fas fa-fingerprint"></i></div>
            <span class="nav-text">Login</span>
        </a>
    <?php
    }
    ?>
</nav>
<footer class="main-footer text-center p-4 mt-5">
    <div class="container">
        <?php
        $show_footer = in_array(basename($_SERVER['PHP_SELF']), ['index.php', 'beranda.php']);
        if ($show_footer):
            // Tampilkan payment & provider di footer
            require_once __DIR__ . '/../includes/db_connect.php';
            $ppinfo = $conn->query("SELECT * FROM payment_provider_info ORDER BY type, sort_order, name");
            $payments = [];
            $providers = [];
            foreach ($ppinfo as $row) {
                if ($row['type'] === 'payment') $payments[] = $row;
                else if ($row['type'] === 'provider') $providers[] = $row;
            }
        ?>
            <div class="footer-ppinfo container py-2 mb-3">
                <?php if ($payments): ?>
                    <div class="mb-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-credit-card text-warning" style="font-size:1.2em;"></i>
                        <span class="fw-bold" style="font-size:1.08em;letter-spacing:0.5px;color:#ff006e;text-shadow:0 1px 4px #222;">Metode Pembayaran</span>
                    </div>
                    <div class="d-flex flex-wrap gap-3 mb-3 justify-content-center">
                        <?php foreach ($payments as $p): ?>
                            <div style="display:flex;flex-direction:column;align-items:center;min-width:60px;">
                                <img src="/assets/images/payment_provider/<?= htmlspecialchars($p['image']) ?>" alt="payment" style="height:32px;max-width:80px;object-fit:contain;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.04);padding:2px 6px;">
                                <span class="status-dot <?= $p['is_active'] ? 'bg-success' : 'bg-secondary' ?> mt-1" style="position:static;"></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($providers): ?>
                    <div class="mb-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-dice text-primary" style="font-size:1.2em;"></i>
                        <span class="fw-bold" style="font-size:1.08em;letter-spacing:0.5px;color:#b3e5fc;text-shadow:0 1px 4px #222;">Provider</span>
                    </div>
                    <div class="footer-provider-grid">
                        <?php foreach ($providers as $p): ?>
                            <div style="display:flex;flex-direction:column;align-items:center;min-width:60px;">
                                <img src="/assets/images/payment_provider/<?= htmlspecialchars($p['image']) ?>" alt="provider" style="height:140px;max-width:350px;object-fit:contain;background:none;border-radius:0;box-shadow:none;padding:0;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <p class="footer-tagline mb-2"><?php echo htmlspecialchars($settings['footer_tagline'] ?? ''); ?></p>
            <p class="text-white-50">&copy; <?php echo date('Y'); ?> BOSSCUAN69. All Rights Reserved.</p>
        <?php endif; ?>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js"></script>
<script src="<?php echo $base_url; ?>assets/js/script.js?v=<?php echo time(); ?>"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
</body>

</html>
