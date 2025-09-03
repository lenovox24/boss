<?php
// File: admin/includes/sidebar.php (VERSI DENGAN TOMBOL TOGGLE)

// Dapatkan nama file saat ini untuk menyorot menu yang aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="bg-dark border-right" id="sidebar-wrapper">
    <div class="sidebar-heading text-white p-3 border-bottom text-center">
        <img src="/assets/images/<?php echo htmlspecialchars($settings['admin_logo'] ?? 'logo-admin.png'); ?>" alt="Admin Logo" style="height: 35px;">
    </div>
    <div class="list-group list-group-flush">
        <a href="dashboard" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt fa-fw me-2"></i>Dashboard
        </a>
        <a href="manage_games" class="list-group-item list-group-item-action bg-dark text-white <?php echo (in_array($current_page, ['manage_games.php', 'add_bulk_games.php', 'edit_game.php', 'add_provider.php', 'edit_provider.php', 'add_category.php', 'edit_category.php'])) ? 'active' : ''; ?>">
            <i class="fas fa-gamepad fa-fw me-2"></i>Manajemen Game
        </a>
        <a href="manage_users" class="list-group-item list-group-item-action bg-dark text-white <?php echo (in_array($current_page, ['manage_users.php', 'edit_user.php', 'view_user_banks.php', 'edit_user_bank.php'])) ? 'active' : ''; ?>">
            <i class="fas fa-users fa-fw me-2"></i>Manajemen User
        </a>
        <a href="manage_transactions" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($current_page == 'manage_transactions.php') ? 'active' : ''; ?>">
            <i class="fas fa-money-bill-wave fa-fw me-2"></i>Manajemen Transaksi
        </a>
        <a href="manage_accounts.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo (in_array($current_page, ['manage_accounts.php', 'edit_account.php'])) ? 'active' : ''; ?>">
            <i class="fas fa-university fa-fw me-2"></i>Rekening Bank Admin
        </a>
        <a href="manage_promo.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo (in_array($current_page, ['manage_promo.php', 'add_promo.php', 'edit_promo.php'])) ? 'active' : ''; ?>">
            <i class="fas fa-gift fa-fw me-2"></i>Manajemen Promo
        </a>
        <a href="manage_memos.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($current_page == 'manage_memos.php') ? 'active' : ''; ?>">
            <i class="fas fa-envelope fa-fw me-2"></i>Manajemen Memo
        </a>
        <a href="manage_livechat.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($current_page == 'manage_livechat.php') ? 'active' : ''; ?>">
            <i class="fas fa-headset fa-fw me-2"></i>Live Chat
        </a>
        <a href="site_settings.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($current_page == 'site_settings.php') ? 'active' : ''; ?>">
            <i class="fas fa-cogs fa-fw me-2"></i>Pengaturan Situs
        </a>
        <a href="manage_bank_logos.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($current_page == 'manage_bank_logos.php') ? 'active' : ''; ?>">
            <i class="fas fa-images fa-fw me-2"></i>Kelola Logo Bank
        </a>
    </div>
</div>
<div id="page-content-wrapper">
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container-fluid">
            <button class="btn btn-primary" id="menu-toggle"><i class="fas fa-bars"></i></button>

            <div class="ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user-circle me-1"></i>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
                </span>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-4">