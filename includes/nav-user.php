<?php
// File: includes/nav-user.php (REVISI FINAL FULL - Tombol Toggle Sidebar Mobile)
// Ambil data saldo user dari database
$user_id_from_session = $_SESSION['user_id'];
$balance_stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$balance_stmt->bind_param("i", $user_id_from_session);
$balance_stmt->execute();
$balance_result = $balance_stmt->get_result();
$user_balance = ($balance_result->num_rows > 0) ? $balance_result->fetch_assoc()['balance'] : 0;
$balance_stmt->close();
?>
<header class="user-main-header sticky-top">
    <div class="super-top-bar">
        <div class="container">
            <!-- Mobile Layout -->
            <div class="d-lg-none d-flex justify-content-between align-items-center">
                <button class="btn btn-header-icon"><i class="fas fa-bell"></i></button>
                <a href="beranda" class="header-logo-link text-center">
                    <img src="<?php echo $base_url; ?>assets/images/<?php echo htmlspecialchars($settings['main_logo'] ?? 'logo.png'); ?>" alt="Logo Situs" class="main-logo-animated" style="height: 50px;">
                </a>
                <button class="btn btn-header-icon" type="button" data-bs-toggle="offcanvas" data-bs-target="#userSidebar" aria-controls="userSidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <!-- Desktop Layout -->
            <div class="d-none d-lg-flex justify-content-between align-items-center position-relative">
                <button class="btn btn-header-icon"><i class="fas fa-bell"></i></button>
                
                <a href="beranda" class="header-logo-center">
                    <img src="<?php echo $base_url; ?>assets/images/<?php echo htmlspecialchars($settings['main_logo'] ?? 'logo.png'); ?>" alt="Logo Situs" class="main-logo-animated" style="height: 60px;">
                </a>
                
                <div style="width: 50px;"></div> <!-- Spacer untuk balance -->
            </div>
        </div>
    </div>
    <div class="bottom-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="deposit" class="header-icon-link deposit-link">
                <i class="fas fa-arrow-circle-down"></i>
                <span>Deposit</span>
            </a>
            <a href="withdraw" class="header-icon-link withdraw-link">
                <i class="fas fa-arrow-circle-up"></i>
                <span>Withdraw</span>
            </a>
            <a href="rekening" class="header-icon-link bank-link">
                <i class="fas fa-credit-card"></i>
                <span>Bank Account</span>
            </a>
            <div class="user-wallet-block ms-auto">
                <div class="user-icon-wrapper">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="user-details-wrapper">
                    <span class="username"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span>
                    <div class="balance" id="user-balance">
                        <i class="fas fa-gem"></i>
                        <span>IDR <?php echo number_format($user_balance, 0, ',', '.'); ?></span>
                    </div>
                </div>
                <button id="refresh-balance" class="btn btn-sm btn-refresh-wallet"><i class="fas fa-arrows-rotate"></i></button>
            </div>
        </div>
    </div>
</header>
<script>
    window.USER_SALDO = <?php echo isset($user_balance) ? (int)$user_balance : 0; ?>;
</script>