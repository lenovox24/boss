<?php
// File: Hokiraja/admin/manage_memos.php
$page_title = "Manajemen Memo";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list-ul me-1"></i> Daftar Percakapan
            </div>
            <div class="list-group list-group-flush" id="conversation-list-container" style="height: 600px; overflow-y: auto;">
                <div class="p-5 text-center text-muted">Memuat percakapan...</div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header" id="memo-header">
                Pilih percakapan untuk dibaca
            </div>
            <div class="memo-window-body" id="memo-message-window">
            </div>
            <div class="card-footer" id="memo-reply-footer" style="display: none;">
                <form id="admin-memo-reply-form">
                    <input type="hidden" id="active-user-id" name="user_id">
                    <div class="mb-2">
                        <input type="text" id="admin-memo-subject" class="form-control" placeholder="Subjek balasan..." required>
                    </div>
                    <div class="mb-2">
                        <textarea id="admin-memo-body" class="form-control" rows="3" placeholder="Ketik balasan Anda..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Kirim Balasan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/memo_admin.js?v=<?php echo time(); ?>"></script>

<?php
require_once 'includes/footer.php';
$conn->close();
?>