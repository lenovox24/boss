<?php
// File: hokiraja/transaksi.php (Halaman Transaksi - Revisi Bet Summary)
$page_title = "Transaksi Saya"; // Judul halaman
require_once 'includes/header.php'; // Memanggil header, yang juga memuat nav-user/nav-guest & sidebar-user

// Logika SweetAlert jika ada (misal dari halaman deposit/withdraw sukses)
if (isset($_SESSION['transaction_success_message'])) {
    $message = $_SESSION['transaction_success_message'];
    unset($_SESSION['transaction_success_message']);
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Berhasil!',
                    text: '" . htmlspecialchars($message) . "',
                    icon: 'success',
                    background: '#212529',
                    color: '#fff',
                    confirmButtonColor: '#ff006e'
                });
            });
          </script>";
}
if (isset($_SESSION['transaction_error_message'])) {
    $message = $_SESSION['transaction_error_message'];
    unset($_SESSION['transaction_error_message']);
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Gagal!',
                    text: '" . htmlspecialchars($message) . "',
                    icon: 'error',
                    background: '#212529',
                    color: '#fff',
                    confirmButtonColor: '#ff006e'
                });
            });
          </script>";
}

// Data dummy untuk opsi tipe transaksi
$transaction_types = [
    "All",
    "Deposit",
    "Withdraw",
    "Bonus",
    "Adjustment",
    "Referral"
];

// Ambil daftar provider dari database untuk dropdown 'Providers'
$providers_result = $conn->query("SELECT nama_provider FROM providers ORDER BY nama_provider ASC");
$providers_options = ['All']; // Tambahkan opsi 'All'
if ($providers_result) {
    while ($row = $providers_result->fetch_assoc()) {
        $providers_options[] = $row['nama_provider'];
    }
}
?>

<main class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <div class="modern-card">
                <div class="modern-title text-center mb-4">
                    <h1 class="page-title">Riwayat Transaksi</h1>
                </div>

                <ul class="nav nav-tabs mb-4" id="transactionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="wallet-summary-tab" data-bs-toggle="tab" data-bs-target="#wallet-summary" type="button" role="tab" aria-controls="wallet-summary" aria-selected="true">Wallet Summary</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bet-summary-tab" data-bs-toggle="tab" data-bs-target="#bet-summary" type="button" role="tab" aria-controls="bet-summary" aria-selected="false">Bet Summary</button>
                    </li>
                </ul>

                <div class="tab-content" id="transactionTabsContent">
                    <div class="tab-pane fade show active" id="wallet-summary" role="tabpanel" aria-labelledby="wallet-summary-tab">
                        <div class="modern-card">
                            <div class="modern-title text-center mb-4">
                                <h2 class="page-title">Ringkasan Dompet</h2>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="date_range_wallet" class="form-label">Tanggal</label>
                                    <input type="text" class="form-control" id="date_range_wallet">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="type_wallet" class="form-label">Tipe</label>
                                    <select class="form-select" id="type_wallet">
                                        <?php foreach ($transaction_types as $type): ?>
                                            <option value="<?php echo strtolower($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-grid mb-4">
                                <button class="btn btn-warning btn-lg fw-bold" id="search_wallet_summary">Cari</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-white">
                                    <thead>
                                        <tr>
                                            <th>Jenis</th>
                                            <th>Tanggal Transaksi</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="wallet-summary-results">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada data transaksi.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="bet-summary" role="tabpanel" aria-labelledby="bet-summary-tab">
                        <div class="modern-card">
                            <div class="modern-title text-center mb-4">
                                <h2 class="page-title">Ringkasan Taruhan</h2>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="date_range_bet" class="form-label">Tanggal</label>
                                    <input type="text" class="form-control" id="date_range_bet">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="type_bet" class="form-label">Tipe</label>
                                    <select class="form-select" id="type_bet">
                                        <?php foreach ($transaction_types as $type): ?>
                                            <option value="<?php echo strtolower($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="providers_bet" class="form-label">Providers</label>
                                    <select class="form-select" id="providers_bet">
                                        <?php foreach ($providers_options as $provider): ?>
                                            <option value="<?php echo strtolower($provider); ?>"><?php echo htmlspecialchars($provider); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-grid mb-4">
                                <button class="btn btn-warning btn-lg fw-bold" id="search_bet_summary">Cari</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-white">
                                    <thead>
                                        <tr>
                                            <th>Tanggal (WIB)</th>
                                            <th>Total Taruhan</th>
                                            <th>Total Turnover</th>
                                            <th>Total Menang/Kalah</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bet-summary-results">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada data taruhan.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
// Penting: Tutup koneksi di akhir file ini, setelah semua konten dan include footer
if (isset($conn)) {
    $conn->close();
}
?>