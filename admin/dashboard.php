<?php
// File: admin/dashboard.php (VERSI BARU DENGAN STATISTIK)

$page_title = "Dashboard";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// --- LOGIKA PENGAMBILAN DATA STATISTIK ---

// 1. Total User
$total_users_result = $conn->query("SELECT COUNT(id) as count FROM users");
$total_users = $total_users_result->fetch_assoc()['count'];

// 2. Total Deposit (yang sudah disetujui)
$old_deposit_result = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE type = 'deposit' AND status = 'approved'");
$old_total = $old_deposit_result->fetch_assoc()['total'] ?? 0;
$new_deposit_result = $conn->query("SELECT SUM(amount) as total FROM deposit_transactions WHERE status = 'approved'");
$new_total = $new_deposit_result->fetch_assoc()['total'] ?? 0;

// Jumlahkan keduanya untuk mendapatkan total yang benar
$total_deposit = $old_total + $new_total;

// 3. Total Withdraw (yang sudah disetujui)
$total_withdraw_result = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE type = 'withdraw' AND status = 'approved'");
$total_withdraw = $total_withdraw_result->fetch_assoc()['total'] ?? 0;

// 4. Jumlah Proses Deposit (yang masih pending)
$pending_deposit_result = $conn->query("SELECT COUNT(id) as count FROM deposit_transactions WHERE status = 'pending'");
$pending_deposit_count = $pending_deposit_result->fetch_assoc()['count'];

// 5. Jumlah Proses Withdraw (yang masih pending)
$pending_withdraw_result = $conn->query("SELECT COUNT(id) as count FROM transactions WHERE type = 'withdraw' AND status = 'pending'");
$pending_withdraw_count = $pending_withdraw_result->fetch_assoc()['count'];

?>

<h1 class="mb-4">Dashboard Statistik</h1>

<!-- Baris untuk Kartu Statistik -->
<div class="row">

    <!-- Kartu Total User -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total User</div>
                        <div class="h5 mb-0 fw-bold text-gray-800"><?php echo number_format($total_users); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Total Deposit -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Deposit (Approved)</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">IDR <?php echo number_format($total_deposit, 2, ',', '.'); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Total Withdraw -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">Total Withdraw (Approved)</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">IDR <?php echo number_format($total_withdraw, 2, ',', '.'); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Baris untuk Kartu Transaksi Pending -->
<div class="row">
    <!-- Kartu Proses Deposit -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="manage_transactions.php?type=deposit" class="text-decoration-none">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Proses Deposit (Pending)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo number_format($pending_deposit_count); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-inbox fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Kartu Proses Withdraw -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="manage_transactions.php?type=withdraw" class="text-decoration-none">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Proses Withdraw (Pending)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo number_format($pending_withdraw_count); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>