<?php
// File: admin/manage_users.php (VERSI BARU DENGAN AKSI DROPDOWN)

$page_title = "Manajemen User";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// --- LOGIKA PENCARIAN ---
$search_query = "";
$sql_where = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql_where = " WHERE username LIKE ? OR full_name LIKE ? OR email LIKE ?";
}

// Mengambil semua data user dari database
$sql = "SELECT id, username, full_name, email, balance, status, registration_date FROM users" . $sql_where . " ORDER BY id DESC";
$stmt = $conn->prepare($sql);

if (!empty($sql_where)) {
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>

<!-- Form Pencarian -->
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-search me-1"></i>Cari User</div>
    <div class="card-body">
        <form action="manage_users.php" method="GET">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari berdasarkan username, nama, atau email..." name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                <button class="btn btn-primary" type="submit">Cari</button>
                <?php if (!empty($search_query)): ?>
                    <a href="manage_users.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabel untuk Menampilkan Data User -->
<div class="card">
    <div class="card-header">Daftar User Terdaftar</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Saldo</th>
                        <th>Status</th>
                        <th>Tgl. Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>IDR <?php echo number_format($row['balance'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php
                                    $status = $row['status'];
                                    $badge_class = 'bg-secondary';
                                    if ($status == 'active') $badge_class = 'bg-success';
                                    elseif ($status == 'suspended') $badge_class = 'bg-warning text-dark';
                                    elseif ($status == 'banned') $badge_class = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                </td>
                                <td><?php echo date('d M Y, H:i', strtotime($row['registration_date'])); ?></td>
                                <td>
                                    <!-- ================================================== -->
                                    <!-- === PERUBAHAN UTAMA: TOMBOL AKSI MENJADI DROPDOWN === -->
                                    <!-- ================================================== -->
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $row['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $row['id']; ?>">
                                            <li>
                                                <a class="dropdown-item" href="edit_user.php?id=<?php echo $row['id']; ?>">
                                                    <i class="fas fa-user-edit fa-fw me-2"></i>Ubah Profil/Saldo
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="view_user_banks.php?user_id=<?php echo $row['id']; ?>">
                                                    <i class="fas fa-university fa-fw me-2"></i>Kelola Rekening Bank
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Belum ada user yang terdaftar.</td>
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
require_once 'includes/footer.php';
?>