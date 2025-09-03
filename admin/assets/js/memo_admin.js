// File: Hokiraja/admin/assets/js/memo_admin.js
document.addEventListener("DOMContentLoaded", function () {
  const conversationContainer = document.getElementById(
    "conversation-list-container"
  );
  const memoHeader = document.getElementById("memo-header");
  const messageWindow = document.getElementById("memo-message-window");
  const replyFooter = document.getElementById("memo-reply-footer");
  const replyForm = document.getElementById("admin-memo-reply-form");
  const activeUserIdInput = document.getElementById("active-user-id");
  const subjectInput = document.getElementById("admin-memo-subject");
  const bodyInput = document.getElementById("admin-memo-body");

  let activeUserId = null;

  // Fungsi untuk memuat daftar percakapan
  async function loadConversations() {
    try {
      const response = await fetch(
        "api_admin_get_memos.php?action=get_conversations"
      );
      const conversations = await response.json();

      conversationContainer.innerHTML = "";
      if (conversations.length === 0) {
        conversationContainer.innerHTML =
          '<div class="p-3 text-center text-muted">Belum ada memo masuk.</div>';
        return;
      }

      conversations.forEach((conv) => {
        const convItem = document.createElement("a");
        convItem.href = "#";
        convItem.className = `list-group-item list-group-item-action ${
          conv.unread_count > 0 ? "fw-bold" : ""
        }`;
        convItem.dataset.userId = conv.user_id;
        convItem.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${conv.username}</h6>
                        <small>${new Date(
                          conv.last_message_time
                        ).toLocaleDateString("id-ID")}</small>
                    </div>
                    <p class="mb-1 text-muted small">${conv.last_subject}</p>
                    ${
                      conv.unread_count > 0
                        ? `<span class="badge bg-danger rounded-pill">${conv.unread_count}</span>`
                        : ""
                    }
                `;

        convItem.addEventListener("click", (e) => {
          e.preventDefault();
          document
            .querySelectorAll(
              "#conversation-list-container .list-group-item.active"
            )
            .forEach((item) => item.classList.remove("active"));
          convItem.classList.add("active");
          loadMessages(conv.user_id, conv.username);
        });

        conversationContainer.appendChild(convItem);
      });
    } catch (error) {
      console.error("Error loading conversations:", error);
    }
  }

  // Fungsi untuk memuat pesan dari percakapan yang dipilih
  async function loadMessages(userId, username) {
    activeUserId = userId;
    activeUserIdInput.value = userId;
    memoHeader.textContent = `Percakapan dengan: ${username}`;
    messageWindow.innerHTML =
      '<div class="p-5 text-center text-muted">Memuat pesan...</div>';
    replyFooter.style.display = "block";

    try {
      const response = await fetch(
        `api_admin_get_memos.php?action=get_messages&user_id=${userId}`
      );
      const messages = await response.json();

      messageWindow.innerHTML = "";
      messages.forEach((msg) => {
        const bubbleWrapper = document.createElement("div");
        bubbleWrapper.className = `chat-bubble-wrapper ${
          msg.sender_type === "admin" ? "sent" : "received"
        }`;

        bubbleWrapper.innerHTML = `
                    <div class="chat-sender-label">${
                      msg.sender_type === "admin" ? "Anda (Admin)" : username
                    }</div>
                    <div class="chat-bubble">
                        <strong>${msg.subject}</strong><br>
                        ${msg.body.replace(/\n/g, "<br>")}
                    </div>
                    <div class="chat-meta">${new Date(
                      msg.sent_at
                    ).toLocaleString("id-ID")}</div>
                `;
        messageWindow.appendChild(bubbleWrapper);
      });
      messageWindow.scrollTop = messageWindow.scrollHeight;
      loadConversations(); // Refresh conversation list untuk update status 'unread'
    } catch (error) {
      console.error("Error loading messages:", error);
    }
  }

  // Fungsi untuk mengirim balasan
  replyForm.addEventListener("submit", async function (e) {
    e.preventDefault();
    const recipient_id = activeUserIdInput.value;
    const subject = subjectInput.value;
    const body = bodyInput.value;

    if (!recipient_id || !subject || !body) {
      alert("Semua kolom balasan wajib diisi.");
      return;
    }

    try {
      await fetch("api_admin_send_memo.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ recipient_id, subject, body }),
      });

      // Kosongkan form dan muat ulang pesan
      subjectInput.value = "";
      bodyInput.value = "";
      loadMessages(
        recipient_id,
        memoHeader.textContent.replace("Percakapan dengan: ", "")
      );
    } catch (error) {
      console.error("Error sending reply:", error);
    }
  });

  // Panggil fungsi utama
  loadConversations();
});
