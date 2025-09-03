<?php
// File: Hokiraja/referral.php
$page_title = "Referral";
require_once 'includes/header.php';

// Ambil kode referral pengguna yang sedang login
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT referral_code FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$referral_code = $stmt->get_result()->fetch_assoc()['referral_code'];
$stmt->close();

// Buat link referral lengkap
$referral_link = "https://cuanss.web.id/daftar#referral=" . htmlspecialchars($referral_code);
?>

<main class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-md-11">
            <div class="card bg-dark text-white border-secondary shadow-lg rounded-4">
                <div class="card-body p-3 p-md-5">
                    <div class="referral-nav nav nav-pills justify-content-center gap-2 mb-4 flex-wrap">
                        <button class="nav-link active d-flex align-items-center gap-2 px-4 py-2 fs-6 fw-semibold" data-view="daftar">
                            <i class="fas fa-link"></i> Daftar Referral
                        </button>
                        <button class="nav-link d-flex align-items-center gap-2 px-4 py-2 fs-6 fw-semibold" data-view="anggota">
                            <i class="fas fa-users"></i> Anggota
                        </button>
                        <button class="nav-link d-flex align-items-center gap-2 px-4 py-2 fs-6 fw-semibold" data-view="bonus">
                            <i class="fas fa-gift"></i> Bonus
                        </button>
                    </div>
                    <div class="referral-content">
                        <div class="referral-view active" id="view-daftar">
                            <h4 class="text-center mb-3">Daftar Anggota Referral</h4>
                            <div class="text-center">
                                <label class="form-label">Referral link:</label>
                                <div class="input-group mb-3 mx-auto" style="max-width: 500px;">
                                    <input type="text" id="referral-link-input" class="form-control" value="<?php echo $referral_link; ?>" readonly>
                                    <button class="btn btn-warning" id="copy-ref-link-btn" type="button">
                                        <i class="fas fa-copy me-1"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="referral-view" id="view-anggota" style="display: none;">
                            <h4 class="text-center mb-3">Anggota Referral</h4>
                            <p class="text-center text-white-50" id="total-referral-text">Total Referral Anda: 0</p>
                            <div class="table-responsive rounded-3 overflow-hidden">
                                <table class="table table-bordered table-striped text-white mb-0">
                                    <thead class="bg-secondary text-warning">
                                        <tr>
                                            <th>No</th>
                                            <th>User</th>
                                            <th>Tanggal Masuk</th>
                                        </tr>
                                    </thead>
                                    <tbody id="anggota-referral-table">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="referral-view" id="view-bonus" style="display: none;">
                            <h4 class="text-center mb-3">Bonus Referral</h4>
                            <div class="table-responsive rounded-3 overflow-hidden">
                                <table class="table table-bordered table-striped text-white mb-0">
                                    <thead class="bg-secondary text-warning">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bonus-referral-table">
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

<script src="assets/js/referral_script.js?v=<?php echo time(); ?>"></script>

<?php
require_once 'includes/footer.php';
$conn->close();
?>