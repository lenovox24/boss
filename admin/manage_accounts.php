<?php
$page_title = "Rekening Bank Admin";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Ambil semua data rekening dari database untuk ditampilkan
$accounts_query = "
    SELECT 
        ada.id, 
        ada.account_name, 
        ada.account_number, 
        pm.method_name, 
        ada.is_active
    FROM admin_deposit_accounts ada
    JOIN payment_methods pm ON ada.method_code = pm.method_code
    ORDER BY ada.id DESC
";
$accounts_result = $conn->query($accounts_query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?php echo $page_title; ?></h1>
    <a href="edit_account.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Tambah Rekening Baru
    </a>
</div>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Metode Pembayaran</th>
                        <th>Nama Rekening</th>
                        <th>Nomor Rekening</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($accounts_result->num_rows > 0): ?>
                        <?php while ($row = $accounts_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['method_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['account_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                                <td>
                                    <?php if ($row['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_account.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_account.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus rekening ini?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada rekening yang ditambahkan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>