<?php
$page_title = "Live Chat";
require_once 'includes/header.php'; // Ini sudah memanggil session_start()

$is_logged_in = isset($_SESSION['user_id']);
$username = 'Guest';
$email = '';

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    $username = $user_data['username'];
    $email = $user_data['email'];
    $stmt->close();
}
?>

<main class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="modern-card">
                <h2 class="modern-title">Live Chat Support</h2>
                <div class="card-header d-flex justify-content-between align-items-center chat-card-header">
                    <div>
                        <small id="admin-status" class="admin-status-indicator">Mengecek status admin...</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-headset fa-2x text-warning me-3"></i>
                        <img src="assets/images/<?php echo htmlspecialchars($settings['admin_profile_picture'] ?? 'default_admin_profile.png'); ?>" alt="Admin" class="chat-admin-pfp">
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!$is_logged_in): ?>
                        <div id="guest-form-container" class="p-4">
                            <p class="text-white-50">Silakan isi form di bawah ini untuk memulai obrolan.</p>
                            <form id="guest-chat-form">
                                <div class="mb-3">
                                    <label for="guest_name" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="guest_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="guest_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="guest_email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="guest_problem" class="form-label">Kendala Akun</label>
                                    <textarea class="form-control" id="guest_problem" rows="3" required></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-warning fw-bold">Mulai Obrolan</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                    <div id="chat-container" class="<?php echo $is_logged_in ? '' : 'd-none'; ?>">
                        <div id="chat-window" style="height: 400px; overflow-y: auto;">
                        </div>
                        <form id="chat-message-form" class="d-flex chat-input-form">
                            <input type="text" id="chat-message-input" class="form-control me-2" placeholder="Ketik pesan Anda..." autocomplete="off" required>
                            <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Deklarasi Variabel
        const adminStatusEl = document.getElementById('admin-status');
        const guestFormContainer = document.getElementById('guest-form-container');
        const chatContainer = document.getElementById('chat-container');
        const guestChatForm = document.getElementById('guest-chat-form');
        const chatWindow = document.getElementById('chat-window');
        const chatMessageForm = document.getElementById('chat-message-form');
        const chatMessageInput = document.getElementById('chat-message-input');
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        let chatSessionId = localStorage.getItem('chat_session_id') || "<?php echo session_id(); ?>";
        localStorage.setItem('chat_session_id', chatSessionId);
        let messagePollingInterval;

        // Fungsi untuk menampilkan pesan
        function appendMessage(senderType, message, timestamp, currentUserType) {
            const wrapper = document.createElement('div');
            wrapper.className = 'chat-bubble-wrapper';

            const senderLabel = document.createElement('div');
            senderLabel.className = 'chat-sender-label';

            const bubble = document.createElement('div');
            bubble.className = 'chat-bubble';
            bubble.textContent = message;

            const meta = document.createElement('div');
            meta.className = 'chat-meta';
            const time = new Date(timestamp);
            meta.textContent = time.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });

            if (senderType === currentUserType) {
                wrapper.classList.add('sent');
                senderLabel.textContent = 'Anda';
            } else {
                wrapper.classList.add('received');
                senderLabel.textContent = 'Admin';
            }

            wrapper.appendChild(senderLabel);
            wrapper.appendChild(bubble);
            wrapper.appendChild(meta);
            chatWindow.appendChild(wrapper);
        }

        // Fungsi untuk mengambil pesan
        async function getMessages() {
            try {
                const response = await fetch(`api_get_messages.php?session_id=${chatSessionId}`);
                const messages = await response.json();
                chatWindow.innerHTML = '';
                messages.forEach(msg => {
                    appendMessage(msg.sender_type, msg.message, msg.timestamp, 'user');
                });
                chatWindow.scrollTop = chatWindow.scrollHeight;
            } catch (error) {
                console.error("Gagal mengambil pesan:", error);
            }
        }

        // Fungsi lainnya (sendMessage, checkAdminStatus, startChat, dll. tetap sama)
        async function sendMessage(message) {
            try {
                await fetch('api_send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: chatSessionId
                    })
                });
                getMessages();
            } catch (error) {
                console.error("Gagal mengirim pesan:", error);
            }
        }
        async function checkAdminStatus() {
            try {
                const response = await fetch('api_get_admin_status.php');
                const data = await response.json();
                if (data.status === 'online') {
                    adminStatusEl.innerHTML = '<span class="dot online"></span> <span>Admin Online</span>';
                } else {
                    adminStatusEl.innerHTML = '<span class="dot offline"></span> <span>Admin Offline</span>';
                }
            } catch (error) {
                adminStatusEl.innerHTML = '<span class="dot offline"></span> <span>Status Error</span>';
            }
        }

        function startChat() {
            if (guestFormContainer) guestFormContainer.classList.add('d-none');
            chatContainer.classList.remove('d-none');
            getMessages();
            messagePollingInterval = setInterval(getMessages, 10000); // Kurangi frekuensi untuk mencegah kedipan
        }
        if (isLoggedIn) {
            startChat();
        } else {
            if (localStorage.getItem('guest_chat_started') === 'true') {
                startChat();
            } else {
                if (guestFormContainer) guestFormContainer.classList.remove('d-none');
                chatContainer.classList.add('d-none');
            }
        }
        if (guestChatForm) {
            guestChatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const name = document.getElementById('guest_name').value;
                const email = document.getElementById('guest_email').value;
                const problem = document.getElementById('guest_problem').value;
                const introMessage = `Pengguna baru memulai obrolan:\nNama: ${name}\nEmail: ${email}\nKendala: ${problem}`;
                sendMessage(introMessage);
                localStorage.setItem('guest_chat_started', 'true');
                startChat();
            });
        }
        chatMessageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = chatMessageInput.value.trim();
            if (message) {
                sendMessage(message);
                chatMessageInput.value = '';
            }
        });
        checkAdminStatus();
        setInterval(checkAdminStatus, 30000);
    });
</script>

<?php
require_once 'includes/footer.php';
$conn->close();
?>