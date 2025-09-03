// File: Hokiraja/assets/js/memo_script.js
document.addEventListener("DOMContentLoaded", function () {
  const memoNavButtons = document.querySelectorAll(".btn-memo-nav");
  const memoListView = document.getElementById("memo-list-view");
  const memoComposeView = document.getElementById("memo-compose-view");
  const memoReadView = document.getElementById("memo-read-view");
  const memoListContainer = document.getElementById("memo-list-container");
  const composeForm = document.getElementById("compose-form");
  const selectAllCheckbox = document.getElementById("select-all-memos");
  const deleteBtn = document.getElementById("delete-btn");

  let currentBox = "inbox";

  function switchView(view) {
    memoListView.classList.add("d-none");
    memoComposeView.classList.add("d-none");
    memoReadView.classList.add("d-none");

    if (view === "list") {
      memoListView.classList.remove("d-none");
    } else if (view === "compose") {
      memoComposeView.classList.remove("d-none");
    } else if (view === "read") {
      memoReadView.classList.remove("d-none");
    }
  }

  async function loadMemos(box) {
    currentBox = box;
    memoListContainer.innerHTML =
      '<div class="p-5 text-center text-muted">Loading...</div>';
    try {
      const response = await fetch(`get_memos.php?box=${box}`);
      const memos = await response.json();
      renderMemoList(memos);
    } catch (error) {
      memoListContainer.innerHTML =
        '<div class="p-3 text-danger">Gagal memuat memo.</div>';
    }
  }

  function renderMemoList(memos) {
    memoListContainer.innerHTML = "";
    if (memos.length === 0) {
      memoListContainer.innerHTML = `<div class="list-group-item text-center text-muted">Tidak ada memo di folder ${currentBox}.</div>`;
      return;
    }

    memos.forEach((memo) => {
      const memoItem = document.createElement("div");
      memoItem.className = `list-group-item d-flex justify-content-between align-items-center ${
        !memo.is_read && currentBox === "inbox" ? "unread" : ""
      }`;
      memoItem.innerHTML = `
                <div>
                    <input type="checkbox" class="form-check-input me-3 memo-checkbox" data-id="${
                      memo.id
                    }">
                    <span>${memo.sender_name || "Kepada: Admin"}</span> - 
                    <span class="text-white-50">${memo.subject}</span>
                </div>
                <small>${new Date(memo.sent_at).toLocaleString("id-ID")}</small>
            `;
      memoListContainer.appendChild(memoItem);
    });
  }

  memoNavButtons.forEach((button) => {
    button.addEventListener("click", function () {
      memoNavButtons.forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");

      const box = this.dataset.box;
      if (box === "compose") {
        switchView("compose");
      } else {
        switchView("list");
        loadMemos(box);
      }
    });
  });

  composeForm.addEventListener("submit", async function (e) {
    e.preventDefault();
    const subject = document.getElementById("subject").value;
    const body = document.getElementById("body").value;

    const response = await fetch("process_memo.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "send", subject, body }),
    });
    const result = await response.json();

    if (result.success) {
      alert("Memo berhasil terkirim!");
      composeForm.reset();
      document.querySelector('.btn-memo-nav[data-box="sent"]').click();
    } else {
      alert(result.message || "Gagal mengirim memo.");
    }
  });

  deleteBtn.addEventListener("click", async function () {
    const selectedCheckboxes = document.querySelectorAll(
      ".memo-checkbox:checked"
    );
    if (selectedCheckboxes.length === 0) {
      alert("Pilih setidaknya satu memo untuk dihapus.");
      return;
    }

    const memoIds = Array.from(selectedCheckboxes).map((cb) => cb.dataset.id);

    if (confirm(`Anda yakin ingin menghapus ${memoIds.length} memo?`)) {
      const response = await fetch("process_memo.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "delete",
          ids: memoIds,
          box: currentBox,
        }),
      });
      const result = await response.json();
      if (result.success) {
        loadMemos(currentBox);
      } else {
        alert(result.message || "Gagal menghapus memo");
      }
    }
  });

  // Inisialisasi: Muat inbox saat pertama kali halaman dibuka
  loadMemos("inbox");
});
