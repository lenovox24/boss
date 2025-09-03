<?php
// File: togel.php (UI mirip gambar, 3 step dinamis)
session_start();
require_once 'includes/header.php';
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");

$pasaran_list = [
    ['name' => 'TOTO WUHAN', 'tutup' => 'Tiap 9 jam', 'undi' => 'Tiap 9 jam'],
    ['name' => 'HK SIANG', 'tutup' => '10:30 WIB', 'undi' => '11:00 WIB'],
    ['name' => 'SG METRO', 'tutup' => '12:30 WIB', 'undi' => '14:00 WIB'],
    ['name' => 'SYDNEY', 'tutup' => '13:30 WIB', 'undi' => '14:00 WIB'],
    ['name' => 'MALAYSIA SIANG', 'tutup' => '14:30 WIB', 'undi' => '16:00 WIB'],
    ['name' => 'SINGAPORE', 'tutup' => '17:20 WIB', 'undi' => '17:45 WIB'],
    ['name' => 'MALAYSIA', 'tutup' => '18:00 WIB', 'undi' => '18:00 WIB'],
    ['name' => 'MACAU', 'tutup' => '19:30 WIB', 'undi' => '20:00 WIB'],
    ['name' => 'QATAR', 'tutup' => '20:30 WIB', 'undi' => '21:00 WIB'],
];
$jenis_bet_list = [
    '4D/3D/2D',
    '4D/3D/2D set',
    'Bolak Balik',
    'Quick 2D',
    'Colok Bebas',
    'Colok Macau',
    'Colok Naga',
    'Colok Jitu',
    '5050 Umum',
    '5050 Special',
    '5050 Kombinasi',
    'Kombinasi',
    'Dasar',
    'Shio'
];
?>
<div class="announcement-bar">
    <span class="announcement-icon"><i class="fas fa-bullhorn"></i></span>
    <div class="announcement-marquee">
        <span data-text="RESMI TERLENGKAP DAN TERPERCAYA NO 1 DI INDONESIA! PROMO MENARIK SETIAP HARI! DAFTAR SEKARANG DAN RAIH JACKPOTNYA!">RESMI TERLENGKAP DAN TERPERCAYA NO 1 DI INDONESIA! PROMO MENARIK SETIAP HARI! DAFTAR SEKARANG DAN RAIH JACKPOTNYA!</span>
    </div>
</div>
<main class="container my-3">
    <div class="category-scroll-wrapper mb-4">
        <div class="category-list" id="beranda-category-menu">
            <a href="beranda" class="category-item" data-category="all">Semua</a>
            <?php
            // Query ulang categories agar tidak habis karena fetch_assoc sebelumnya
            $categories2 = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
            if ($categories2 && $categories2->num_rows > 0):
                while ($cat = $categories2->fetch_assoc()):
                    $catNameLower = strtolower(str_replace(' ', '', $cat['name']));
                    $isTogel = $catNameLower == 'togel';
                    $activeClass = $isTogel ? 'active' : '';
                    $href = $isTogel ? 'togel' : 'beranda?category=' . $catNameLower;
            ?>
                    <a href="<?php echo $href; ?>" class="category-item <?php echo $activeClass; ?>" data-category="<?php echo htmlspecialchars($cat['name']); ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
            <?php endwhile;
            endif; ?>
        </div>
    </div>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/togel.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo $base_url; ?>assets/js/togel.js"></script>
    <script>
        window.USER_SALDO = <?php echo isset($_SESSION['user_saldo']) ? (int)$_SESSION['user_saldo'] : 0; ?>;
    </script>
    <!-- Step 1: Pilih Pasaran -->
    <div id="step-pasaran" class="togel-step">
        <div class="togel-step-title">Pilih Pasaran</div>
        <div class="togel-pasaran-list">
            <?php foreach ($pasaran_list as $pasaran): ?>
                <button class="togel-pasaran-btn" data-pasaran="<?php echo $pasaran['name']; ?>">
                    <div class="pasaran-title"><?php echo $pasaran['name']; ?></div>
                    <div class="pasaran-info">Tutup: <?php echo $pasaran['tutup']; ?> - Diundi: <?php echo $pasaran['undi']; ?></div>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Step 2: Pilih Jenis Bet -->
    <div id="step-jenis" class="togel-step d-none">
        <div class="togel-step-title">Pilih Jenis Taruhan</div>
        <div class="togel-diskon-bar">
            <select class="togel-diskon-select">
                <option>Discount</option>
                <option>Full</option>
            </select>
        </div>
        <div class="togel-jenis-list">
            <?php foreach ($jenis_bet_list as $jenis): ?>
                <button class="togel-jenis-btn" data-jenis="<?php echo $jenis; ?>"><?php echo $jenis; ?></button>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-secondary mt-3" id="btn-back-jenis">&lt; Kembali</button>
    </div>
    <!-- Step 3: Input Nomor & Bet -->
    <div id="step-input" class="togel-step d-none">
        <div class="togel-step-title">Input Nomor & Jumlah Bet</div>
        <div class="togel-input-table-wrapper">
            <table class="togel-input-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor<br>(2-4 digit)</th>
                        <th>Game</th>
                        <th>Bet (min 100)</th>
                        <th>Potongan</th>
                        <th>Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 1; $i <= 30; $i++): ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><input type="text" class="input-nomor" maxlength="4"></td>
                            <td><input type="text" class="input-game" readonly></td>
                            <td><input type="text" class="input-bet"></td>
                            <td><input type="text" class="input-potongan" readonly></td>
                            <td><input type="text" class="input-bayar" readonly></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
        <div class="togel-input-action-bar d-flex justify-content-between align-items-center mt-3">
            <div class="togel-quickbet-group">
                <input type="text" class="input-quickbet" placeholder="QuickBet (Nominal untuk semua)">
                <button class="btn btn-warning btn-sm ms-2" id="btn-quickbet">QuickBet</button>
            </div>
            <div class="togel-total-bet">Total: <span id="input-total-bet">0</span></div>
            <button class="btn btn-success" id="btn-konfirmasi">Konfirmasi</button>
        </div>
        <button class="btn btn-secondary mt-3" id="btn-back-input">&lt; Kembali</button>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>