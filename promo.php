<?php
// File: hokiraja/promo.php

require_once 'includes/header.php';

function base_url($path = '')
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path_parts = explode('/', $script);
    array_pop($path_parts); // remove script filename
    $base = implode('/', $path_parts);
    return $protocol . $host . $base . '/' . ltrim($path, '/');
}

// Ambil semua data promo yang aktif dari database
$promos = $conn->query("SELECT * FROM promotions WHERE is_active = 1 ORDER BY sort_order ASC");
?>

<main class="container my-4">
    <div class="page-header text-center mb-4">
        <h1 class="page-title">Promosi Spesial</h1>
        <p class="text-white-50">Temukan semua penawaran dan bonus menarik yang tersedia untuk Anda.</p>
    </div>

    <!-- Galeri Promo -->
    <div class="row g-4">
        <?php if ($promos && $promos->num_rows > 0): ?>
            <?php while ($promo = $promos->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="promo-card">
                        <div class="promo-card-image">
                            <img src="/assets/images/promos/<?php echo htmlspecialchars($promo['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($promo['title']); ?>"
                                onerror="this.src='https://placehold.co/600x300/111/FFF?text=<?php echo urlencode($promo['title']); ?>';">
                        </div>
                        <div class="promo-card-body">
                            <h3 class="promo-card-title"><?php echo htmlspecialchars($promo['title']); ?></h3>
                            <div class="promo-description-wrapper">
                                <div class="promo-card-description collapsed" data-promo-id="<?php echo $promo['id']; ?>">
                                    <?php echo nl2br(htmlspecialchars($promo['description'])); ?>
                                </div>
                                <button class="btn-toggle-description" data-promo-id="<?php echo $promo['id']; ?>">
                                    <span class="btn-text">Lihat Selengkapnya</span>
                                    <i class="fas fa-chevron-down btn-icon"></i>
                                </button>
                            </div>
                        </div>
                        <?php if (!empty($promo['link_url']) && $promo['link_url'] !== '#'): ?>
                            <div class="promo-card-footer">
                                <a href="<?php echo htmlspecialchars($promo['link_url']); ?>" class="btn btn-warning w-100">Klaim Sekarang</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center text-white-50">Saat ini belum ada promosi yang tersedia. Silakan kembali lagi nanti.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fungsi untuk toggle deskripsi promo
        function togglePromoDescription(promoId) {
            const description = document.querySelector(`.promo-card-description[data-promo-id="${promoId}"]`);
            const button = document.querySelector(`.btn-toggle-description[data-promo-id="${promoId}"]`);
            const buttonText = button.querySelector('.btn-text');
            const buttonIcon = button.querySelector('.btn-icon');

            if (description.classList.contains('collapsed')) {
                // Expand deskripsi
                description.classList.remove('collapsed');
                description.classList.add('expanded');
                buttonText.textContent = 'Sembunyikan';
                buttonIcon.classList.remove('fa-chevron-down');
                buttonIcon.classList.add('fa-chevron-up');
            } else {
                // Collapse deskripsi
                description.classList.remove('expanded');
                description.classList.add('collapsed');
                buttonText.textContent = 'Lihat Selengkapnya';
                buttonIcon.classList.remove('fa-chevron-up');
                buttonIcon.classList.add('fa-chevron-down');
            }
        }

        // Event listener untuk tombol toggle
        document.querySelectorAll('.btn-toggle-description').forEach(button => {
            button.addEventListener('click', function() {
                const promoId = this.getAttribute('data-promo-id');
                togglePromoDescription(promoId);
            });
        });

        // Auto-hide tombol jika deskripsi pendek
        document.querySelectorAll('.promo-description-wrapper').forEach(wrapper => {
            const description = wrapper.querySelector('.promo-card-description');
            const button = wrapper.querySelector('.btn-toggle-description');

            // Cek apakah deskripsi perlu di-truncate
            if (description.scrollHeight <= description.clientHeight) {
                button.style.display = 'none';
            }
        });
    });
</script>

<?php
require_once 'includes/footer.php';
// Penting: tutup koneksi di akhir halaman
if (isset($conn)) {
    $conn->close();
}
?>