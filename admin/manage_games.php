<?php
// File: admin/manage_games.php (VERSI FINAL DENGAN URUTAN)
$page_title = "Manajemen Game";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'games';

// Paginasi untuk daftar game
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$per_page = 25;
$offset = ($page - 1) * $per_page;

// Hitung total data game
$total_games = $conn->query("SELECT COUNT(*) as total FROM games")->fetch_assoc()['total'];
$total_pages = ceil($total_games / $per_page);
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
<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link <?php echo ($active_tab == 'games') ? 'active' : ''; ?>" href="manage_games?tab=games">Daftar Game</a></li>
    <li class="nav-item"><a class="nav-link <?php echo ($active_tab == 'providers') ? 'active' : ''; ?>" href="manage_games?tab=providers">Daftar Provider</a></li>
    <li class="nav-item"><a class="nav-link <?php echo ($active_tab == 'categories') ? 'active' : ''; ?>" href="manage_games?tab=categories">Daftar Kategori</a></li>
</ul>
<div class="tab-content">

    <!-- KONTEN TAB 1: DAFTAR GAME -->
    <?php if ($active_tab == 'games'): ?>
        <div class="tab-pane fade show active">
            <div class="mb-3">
                <a href="add_bulk_games" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Game Massal</a>
                <span class="text-muted ms-3">Total: <?php echo $total_games; ?> game</span>
            </div>
            <div class="card">
                <div class="card-header">Daftar Semua Game (Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>)</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Gambar</th>
                                    <th>Nama Game</th>
                                    <th>Provider</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $games_result = $conn->query("SELECT * FROM games ORDER BY id DESC LIMIT $per_page OFFSET $offset");
                                if ($games_result && $games_result->num_rows > 0):
                                    while ($row = $games_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td>
                                                <?php
                                                $gambar_thumbnail = $row['gambar_thumbnail'];
                                                if (filter_var($gambar_thumbnail, FILTER_VALIDATE_URL)) {
                                                    // Jika URL eksternal, gunakan langsung
                                                    $image_src = $gambar_thumbnail;
                                                } else {
                                                    // Jika file lokal, tambahkan path folder
                                                    $image_src = $base_url . 'assets/images/games/' . htmlspecialchars($gambar_thumbnail);
                                                }
                                                ?>
                                                <img src="<?php echo $image_src; ?>" width="80" onerror="this.src='https://placehold.co/80x80/EEE/31343C?text=No+Image';">
                                            </td>
                                            <td><?php echo htmlspecialchars($row['nama_game']); ?></td>
                                            <td><?php echo htmlspecialchars($row['provider']); ?></td>
                                            <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                            <td><span class="badge bg-<?php echo ($row['is_active'] == 1) ? 'success' : 'danger'; ?>"><?php echo ($row['is_active'] == 1) ? 'Aktif' : 'Tidak Aktif'; ?></span></td>
                                            <td>
                                                <a href="edit_game?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Ubah"><i class="fas fa-edit"></i></a>
                                                <a href="delete_game?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus game ini?');"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada data game.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginasi -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Paginasi Game" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="manage_games?tab=games&page=<?php echo ($page - 1); ?>">« Sebelumnya</a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);

                                if ($start_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="manage_games?tab=games&page=1">1</a>
                                    </li>
                                    <?php if ($start_page > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="manage_games?tab=games&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="manage_games?tab=games&page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                                    </li>
                                <?php endif; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="manage_games?tab=games&page=<?php echo ($page + 1); ?>">Selanjutnya »</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- KONTEN TAB 2: DAFTAR PROVIDER -->
    <?php if ($active_tab == 'providers'): ?>
        <div class="tab-pane fade show active">
            <div class="mb-3"><a href="add_provider" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Provider Baru</a></div>
            <div class="card">
                <div class="card-header">Daftar Semua Provider</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Urutan</th>
                                    <th>Logo</th>
                                    <th>Nama Provider</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $providers_result = $conn->query("SELECT * FROM providers ORDER BY sort_order ASC, id ASC");
                                if ($providers_result && $providers_result->num_rows > 0):
                                    while ($row = $providers_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['sort_order']; ?></td>
                                            <td><img src="/assets/images/providers/<?php echo htmlspecialchars($row['logo_provider']); ?>" width="100" onerror="this.src='https://placehold.co/100x40/EEE/31343C?text=No+Logo';"></td>
                                            <td><?php echo htmlspecialchars($row['nama_provider']); ?></td>
                                            <td>
                                                <a href="edit_provider?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Ubah"><i class="fas fa-edit"></i></a>
                                                <a href="delete_provider?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus provider ini?');"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                <?php endwhile;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- KONTEN TAB 3: DAFTAR KATEGORI -->
    <?php if ($active_tab == 'categories'): ?>
        <div class="tab-pane fade show active">
            <div class="mb-3"><a href="add_category" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Kategori Baru</a></div>
            <div class="card">
                <div class="card-header">Daftar Semua Kategori Game</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Urutan</th>
                                    <th>Gambar</th>
                                    <th>Nama Kategori</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $categories_result = $conn->query("SELECT * FROM categories ORDER BY sort_order ASC, id ASC");
                                if ($categories_result && $categories_result->num_rows > 0):
                                    while ($row = $categories_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['sort_order']; ?></td>
                                            <td><img src="/assets/images/categories/<?php echo htmlspecialchars($row['image']); ?>" width="80" onerror="this.src='https://placehold.co/80x80/EEE/31343C?text=No+Image';"></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><span class="badge bg-<?php echo ($row['is_active'] == 1) ? 'success' : 'danger'; ?>"><?php echo ($row['is_active'] == 1) ? 'Aktif' : 'Tidak Aktif'; ?></span></td>
                                            <td>
                                                <a href="edit_category?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Ubah"><i class="fas fa-edit"></i></a>
                                                <a href="delete_category?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus kategori ini?');"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                <?php endwhile;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $conn->close();
require_once __DIR__ . '/includes/footer.php'; ?>