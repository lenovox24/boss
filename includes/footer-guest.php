<?php
// File: includes/footer-guest.php
?>
<nav class="mobile-footer-nav d-lg-none fixed-bottom">
    <a href="index.php" class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
        <i class="fas fa-home"></i><span>Home</span>
    </a>
    <a href="promo.php" class="nav-item <?php echo ($current_page == 'promo.php') ? 'active' : ''; ?>">
        <i class="fas fa-gift"></i><span>Promo</span>
    </a>
    <a href="daftar.php" class="nav-item register-btn <?php echo ($current_page == 'daftar.php') ? 'active' : ''; ?>">
        <i class="fas fa-user-plus"></i><span>Daftar</span>
    </a>
    <a href="livechat.php" class="nav-item <?php echo ($current_page == 'livechat.php') ? 'active' : ''; ?>">
        <i class="fas fa-comments"></i><span>Live Chat</span>
    </a>
    <a href="login.php" class="nav-item <?php echo ($current_page == 'login.php') ? 'active' : ''; ?>">
        <i class="fas fa-sign-in-alt"></i><span>Login</span>
    </a>
</nav>