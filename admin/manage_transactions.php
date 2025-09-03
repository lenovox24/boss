<?php
// File: admin/manage_transactions.php (REVISI FINAL: Menampilkan Deposit Transactions Pending)

$page_title = "Manajemen Transaksi";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Logika untuk tab yang aktif
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'deposit';
if (!in_array($type_filter, ['deposit', 'withdraw'])) {
    $type_filter = 'deposit';
}

// --- LOGIKA PENCARIAN ---
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$params = [];
$param_types = "";

$sql_search = "";
if (!empty($search_query)) {
    $sql_search = " AND u.username LIKE ?";
    // Tambahkan parameter pencarian ke array
    $params[] = "%" . $search_query . "%";
    $param_types .= "s";
}

// Ambil semua transaksi DEPOSIT yang masih PENDING berdasarkan tipe dan pencarian
// (menggunakan tabel deposit_transactions yang baru)
$sql_query = "";
if ($type_filter == 'deposit') {
    $sql_query = "
        SELECT dt.id, dt.amount, dt.created_at, dt.channel_type, dt.payment_method_code, dt.bonus_id, dt.proof_of_transfer_url, u.username
        FROM deposit_transactions dt
        JOIN users u ON dt.user_id = u.id
        WHERE dt.status = 'pending'" . $sql_search . "
        ORDER BY dt.created_at ASC"; // Urutkan berdasarkan waktu request
} elseif ($type_filter == 'withdraw') {
    // Jika ada tabel withdraw_transactions, gunakan itu
    // Jika tidak, gunakan tabel 'transactions' lama jika withdraw masih di sana
    // Untuk saat ini, saya akan buat query untuk transactions.withdraw
    $sql_query = "
        SELECT t.id, t.amount, t.created_at, u.username
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        WHERE t.status = 'pending' AND t.type = 'withdraw'" . $sql_search . "
        ORDER BY t.created_at ASC";
}

$stmt = $conn->prepare($sql_query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error); // Untuk debugging
}

// Bind parameter sesuai dengan jenis filter dan pencarian
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success" role="alert">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
?>

<div class="card mb-4">
    <div class="card-header"><i class="fas fa-search me-1"></i>Cari Transaksi Pending</div>
    <div class="card-body">
        <form action="manage_transactions.php" method="GET">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type_filter); ?>">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari berdasarkan username..." name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                <button class="btn btn-primary" type="submit">Cari</button>
                <?php if (!empty($search_query)): ?>
                    <a href="manage_transactions.php?type=<?php echo htmlspecialchars($type_filter); ?>" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link <?php echo ($type_filter == 'deposit') ? 'active' : ''; ?>" href="manage_transactions.php?type=deposit">Permintaan Deposit</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($type_filter == 'withdraw') ? 'active' : ''; ?>" href="manage_transactions.php?type=withdraw">Permintaan Withdraw</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <h5 class="card-title">Daftar Permintaan <span class="text-primary"><?php echo ucfirst($type_filter); ?></span> yang Menunggu Persetujuan</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Username</th>
                        <th>Jumlah</th>
                        <?php if ($type_filter == 'deposit'): ?>
                            <th>Channel</th>
                            <th>Metode</th>
                            <th>Bonus</th>
                            <th>Bukti</th>
                        <?php endif; ?>
                        <th>Tanggal Permintaan</th>
                        <th style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                <td>IDR <?php echo number_format($row['amount'], 2, ',', '.'); ?></td>
                                <?php if ($type_filter == 'deposit'): ?>
                                    <td><?php echo htmlspecialchars(ucfirst($row['channel_type'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['payment_method_code']); ?></td>
                                    <td>
                                        <?php
                                        // Ambil nama bonus jika bonus_id tidak NULL
                                        if ($row['bonus_id']) {
                                            $bonus_stmt = $conn->prepare("SELECT bonus_name FROM bonuses WHERE id = ?");
                                            $bonus_stmt->bind_param("i", $row['bonus_id']);
                                            $bonus_stmt->execute();
                                            $bonus_name = $bonus_stmt->get_result()->fetch_assoc()['bonus_name'] ?? 'N/A';
                                            $bonus_stmt->close();
                                            echo htmlspecialchars($bonus_name);
                                        } else {
                                            echo 'Tanpa Bonus';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['proof_of_transfer_url'])): ?>
                                            <a href="../assets/uploads/proofs/<?php echo htmlspecialchars($row['proof_of_transfer_url']); ?>" target="_blank" class="btn btn-sm btn-info">Lihat Bukti</a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="process_transaction?id=<?php echo $row['id']; ?>&action=approve&type=<?php echo $type_filter; ?>" class="btn btn-sm btn-success" onclick="return confirm('Anda yakin ingin MENYETUJUI transaksi ini?');">
                                        <i class="fas fa-check me-1"></i> Approve
                                    </a>
                                    <a href="process_transaction?id=<?php echo $row['id']; ?>&action=reject&type=<?php echo $type_filter; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Anda yakin ingin MENOLAK transaksi ini?');">
                                        <i class="fas fa-times me-1"></i> Reject
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo ($type_filter == 'deposit') ? '9' : '5'; ?>" class="text-center">
                                <?php echo !empty($search_query) ? 'Transaksi tidak ditemukan untuk pencarian Anda.' : 'Tidak ada permintaan ' . $type_filter . ' yang pending.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>