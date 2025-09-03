<?php
// File: admin/manage_promo.php (Versi dengan Deskripsi)
$page_title = "Manajemen Promo";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$result = $conn->query("SELECT * FROM promotions ORDER BY sort_order ASC, id DESC");
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success" role="alert">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
?>

<div class="mb-3">
    <a href="add_promo.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Promo Baru</a>
</div>

<div class="card">
    <div class="card-header">Daftar Promo / Slider</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Gambar</th>
                        <th>Detail Promo</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center fw-bold fs-5"><?php echo $row['sort_order']; ?></td>
                                <td>
                                    <div style="overflow:hidden;padding:0;background:transparent;">
                                        <img src="/assets/images/promos/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" style="display:block;width:100%;height:auto;object-fit:cover;border-radius:10px;" onerror="this.src='https://placehold.co/200x80/EEE/31343C?text=No+Image';">
                                    </div>
                                </td>
                                <td>
                                    <div class="promo-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                    <div class="promo-description"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
                                    <div class="promo-link mt-2">
                                        <small>Link: <a href="<?php echo htmlspecialchars($row['link_url']); ?>" target="_blank"><?php echo htmlspecialchars($row['link_url']); ?></a></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo ($row['is_active'] == 1) ? 'success' : 'danger'; ?>"><?php echo ($row['is_active'] == 1) ? 'Aktif' : 'Tidak Aktif'; ?></span>
                                </td>
                                <td>
                                    <a href="edit_promo.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Ubah"><i class="fas fa-edit"></i></a>
                                    <a href="delete_promo.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Anda yakin ingin menghapus promo ini?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data promo.</td>
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