<?php
$page_title = "Manajemen Live Chat";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<h1 class="mb-4 d-flex align-items-center justify-content-between">
    <?php echo $page_title; ?>
    <span id="admin-status-indicator" class="badge rounded-pill" style="font-size:1rem;"></span>
</h1>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-comments me-1"></i> Daftar Percakapan
            </div>
            <div class="list-group list-group-flush" id="conversation-list" style="height: 600px; overflow-y: auto;">
                <div class="p-5 text-center text-muted">Memuat percakapan...</div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header" id="chat-header">
                Pilih percakapan untuk memulai
            </div>
            <div class="chat-window-body" id="admin-chat-window">
            </div>
            <div class="card-footer">
                <form id="admin-reply-form" class="d-flex" style="display: none!important;">
                    <input type="hidden" id="active-session-id" name="session_id">
                    <input type="text" id="admin-message-input" class="form-control me-2" placeholder="Ketik balasan..." autocomplete="off" required>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusEl = document.getElementById('admin-status-indicator');
        async function updateStatus() {
            try {
                const res = await fetch('../api_get_admin_status.php');
                const data = await res.json();
                if (data.status === 'online') {
                    statusEl.innerHTML = '<i class="fas fa-circle me-1" style="color:#fff;"></i> <span style="color:#fff;font-weight:bold;">Admin Online</span>';
                    statusEl.className = 'badge rounded-pill bg-success';
                } else {
                    statusEl.innerHTML = '<i class="fas fa-circle me-1" style="color:#fff;"></i> <span style="color:#fff;font-weight:bold;">Admin Offline</span>';
                    statusEl.className = 'badge rounded-pill bg-danger';
                }
            } catch (e) {
                statusEl.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Status Error';
                statusEl.className = 'badge rounded-pill bg-secondary';
            }
        }
        updateStatus();
        setInterval(updateStatus, 10000); // update setiap 10 detik
    });
</script>