document.addEventListener("DOMContentLoaded", function () {
  // ========================================================
  // == BAGIAN 1: LOGIKA UNTUK TOGGLE SIDEBAR RESPONSIVE ==
  // ========================================================
  const menuToggle = document.getElementById("menu-toggle");
  const wrapper = document.getElementById("wrapper");

  if (menuToggle && wrapper) {
    menuToggle.addEventListener("click", function (e) {
      e.preventDefault();
      wrapper.classList.toggle("toggled");
    });
  }
});

// File: admin/assets/js/admin_script.js

document.addEventListener("DOMContentLoaded", function () {
  // ... (kode toggle sidebar Anda yang sudah ada) ...

  // ========================================================
  // == BAGIAN BARU: LOGIKA UNTUK PANEL LIVE CHAT ADMIN ==
  // ========================================================
  const conversationList = document.getElementById("conversation-list");
  const adminChatWindow = document.getElementById("admin-chat-window");
  const adminReplyForm = document.getElementById("admin-reply-form");
  const adminMessageInput = document.getElementById("admin-message-input");
  const activeSessionIdInput = document.getElementById("active-session-id");
  const chatHeader = document.getElementById("chat-header");

  let activeSessionId = null;
  let messagePollingInterval = null;

  // Fungsi untuk menampilkan pesan dengan alignment dinamis
  function appendAdminMessage(senderType, message, timestamp, currentUserType) {
    const wrapper = document.createElement("div");
    wrapper.className = "chat-bubble-wrapper";
    const bubble = document.createElement("div");
    bubble.className = "chat-bubble";
    bubble.textContent = message;
    bubble.style.whiteSpace = "pre-wrap";
    const meta = document.createElement("div");
    meta.className = "chat-time";
    const time = new Date(timestamp);
    meta.textContent = time.toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
    });

    if (senderType === currentUserType) {
      wrapper.classList.add("sent");
    } else {
      wrapper.classList.add("received");
    }

    wrapper.appendChild(bubble);
    wrapper.appendChild(meta);
    adminChatWindow.appendChild(wrapper);
  }

  // Fungsi untuk mengambil pesan dari sebuah percakapan
  async function loadMessages(sessionId) {
    activeSessionId = sessionId;
    activeSessionIdInput.value = sessionId;
    adminReplyForm.style.display = "flex";

    // Hentikan polling lama jika ada
    if (messagePollingInterval) clearInterval(messagePollingInterval);

    try {
      const response = await fetch(
        `../api_get_messages.php?session_id=${sessionId}`
      );
      const messages = await response.json();
      // Simpan posisi scroll sebelum update
      console.log('scrollTop:', adminChatWindow.scrollTop, 'clientHeight:', adminChatWindow.clientHeight, 'scrollHeight:', adminChatWindow.scrollHeight);
      const wasAtBottom = (adminChatWindow.scrollTop + adminChatWindow.clientHeight) >= (adminChatWindow.scrollHeight - 10);
      if (adminChatWindow.clientHeight === 0) {
        console.warn('adminChatWindow.clientHeight = 0. Cek CSS: pastikan #admin-chat-window punya height dan overflow-y: auto');
      }
      const prevScrollHeight = adminChatWindow.scrollHeight;
      const prevScrollTop = adminChatWindow.scrollTop;
      adminChatWindow.innerHTML = ""; // Bersihkan jendela chat
      messages.forEach((msg) => {
        // Beritahu fungsi bahwa yang melihat adalah 'admin'
        appendAdminMessage(
          msg.sender_type,
          msg.message,
          msg.timestamp,
          "admin"
        );
      });
      // Jika user sebelumnya di bawah, auto-scroll ke bawah. Jika tidak, biarkan posisi scroll tetap.
      if (wasAtBottom) {
      adminChatWindow.scrollTop = adminChatWindow.scrollHeight;
        console.log('Auto-scroll ke bawah');
      } else {
        adminChatWindow.scrollTop = adminChatWindow.scrollHeight - prevScrollHeight + prevScrollTop;
        console.log('Pertahankan posisi scroll:', adminChatWindow.scrollTop);
      }

      // Mulai polling baru untuk percakapan ini
      messagePollingInterval = setInterval(() => loadMessages(sessionId), 3000);
    } catch (error) {
      console.error("Gagal mengambil pesan:", error);
    }
  }

  // Fungsi untuk mengambil daftar percakapan
  async function loadConversations() {
    if (!conversationList) return; // Hanya berjalan jika di halaman chat
    try {
      const response = await fetch("api_admin_get_conversations.php");
      const conversations = await response.json();
      conversationList.innerHTML = "";

      if (conversations.length === 0) {
        conversationList.innerHTML =
          '<div class="p-3 text-center text-muted">Belum ada percakapan.</div>';
        return;
      }

      conversations.forEach((conv) => {
        const convItem = document.createElement("a");
        convItem.href = "#";
        convItem.className = "list-group-item list-group-item-action";
        convItem.dataset.sessionId = conv.session_id;

        const displayName = conv.username
          ? conv.username
          : `Tamu (${conv.session_id.substr(0, 6)})`;
        const lastMessage =
          conv.last_message.length > 30
            ? conv.last_message.substr(0, 30) + "..."
            : conv.last_message;

        convItem.innerHTML = `
                  <div class="d-flex w-100 justify-content-between">
                      <h6 class="mb-1">${displayName}</h6>
                      <small>${new Date(
                        conv.last_message_time
                      ).toLocaleTimeString("id-ID", {
                        hour: "2-digit",
                        minute: "2-digit",
                      })}</small>
                  </div>
                  <p class="mb-1 text-muted">${lastMessage}</p>
              `;

        convItem.addEventListener("click", function (e) {
          e.preventDefault();
          // Hapus class 'active' dari item lain
          document
            .querySelectorAll("#conversation-list .list-group-item.active")
            .forEach((item) => item.classList.remove("active"));
          // Tambahkan class 'active' ke item yang diklik
          this.classList.add("active");
          chatHeader.textContent = `Percakapan dengan ${displayName}`;
          loadMessages(this.dataset.sessionId);
        });

        conversationList.appendChild(convItem);
      });
    } catch (error) {
      console.error("Gagal mengambil daftar percakapan:", error);
    }
  }

  // Event listener untuk form balasan admin
  if (adminReplyForm) {
    adminReplyForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const message = adminMessageInput.value.trim();
      const sessionId = activeSessionIdInput.value;

      if (message && sessionId) {
        adminMessageInput.value = "";
        try {
          await fetch("api_admin_send_message.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ message: message, session_id: sessionId }),
          });
          loadMessages(sessionId); // Muat ulang pesan setelah mengirim
        } catch (error) {
          console.error("Gagal mengirim balasan:", error);
        }
      }
    });
  }

  // Panggil fungsi utama saat halaman dimuat
  if (window.location.pathname.endsWith("manage_livechat.php")) {
    loadConversations();
    setInterval(loadConversations, 10000); // Refresh daftar percakapan setiap 10 detik
  }
});
