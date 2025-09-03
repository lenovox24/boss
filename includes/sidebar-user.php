<?php
// File: includes/sidebar-user.php (REVISI FINAL FULL - Struktur Offcanvas)

// Ambil data saldo user
$sidebar_user_id = $_SESSION['user_id'];
$sidebar_stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$sidebar_stmt->bind_param("i", $sidebar_user_id);
$sidebar_stmt->execute();
$sidebar_result = $sidebar_stmt->get_result();
$sidebar_balance = ($sidebar_result->num_rows > 0) ? $sidebar_result->fetch_assoc()['balance'] : 0;
$sidebar_stmt->close();
?>
<div class="offcanvas offcanvas-start user-sidebar" tabindex="-1" id="userSidebar" aria-labelledby="userSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="userSidebarLabel">MENU</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="sidebar-user-info">
            <div class="username"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></div>
            <div class="balance" id="sidebar-balance">
                <span>IDR <?php echo number_format($sidebar_balance, 0, ',', '.'); ?></span>
                <button id="sidebar-refresh-balance" class="btn btn-sm btn-refresh-v2"><i class="fas fa-sync-alt"></i></button>
            </div>
            <div class="sidebar-user-actions">
                <a href="deposit">Deposit</a>
                <a href="withdraw">Withdraw</a>
                <a href="rekening">Akun Bank</a>
            </div>
        </div>

        <hr class="sidebar-divider">

        <nav class="user-sidebar-nav">
            <a href="profil.php" class="nav-link"><i class="fas fa-user-edit fa-fw me-2"></i>Profil</a>
            <a href="memo.php" class="nav-link"><i class="fas fa-envelope fa-fw me-2"></i>Memo</a>
            <a href="referral.php" class="nav-link"><i class="fas fa-users fa-fw me-2"></i>Referral</a>
            <a href="bantuan.php" class="nav-link"><i class="fas fa-question-circle fa-fw me-2"></i>Bantuan</a>
            <a href="logout.php" class="nav-link text-danger mt-3"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Logout</a>
        </nav>
    </div>
</div>