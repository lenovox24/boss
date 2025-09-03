<?php
// File: includes/footer-user.php
?>
<nav class="mobile-footer-nav d-lg-none fixed-bottom">
    <a href="beranda.php" class="nav-item <?php echo ($current_page == 'beranda.php') ? 'active' : ''; ?>">
        <i class="fas fa-home"></i><span>Home</span>
    </a>
    <a href="transaksi.php" class="nav-item <?php echo ($current_page == 'transaksi.php') ? 'active' : ''; ?>">
        <i class="fas fa-exchange-alt"></i><span>Transaksi</span>
    </a>
    <a href="promo.php" class="nav-item <?php echo ($current_page == 'promo.php') ? 'active' : ''; ?>">
        <i class="fas fa-gift"></i><span>Promosi</span>
    </a>
    <a href="livechat.php" class="nav-item <?php echo ($current_page == 'livechat.php') ? 'active' : ''; ?>">
        <i class="fas fa-comments"></i><span>Live Chat</span>
    </a>
    <a href="profil.php" class="nav-item <?php echo ($current_page == 'profil.php') ? 'active' : ''; ?>">
        <i class="fas fa-user"></i><span>Profil</span>
    </a>
</nav>