// File: Hokiraja/assets/js/referral_script.js
document.addEventListener("DOMContentLoaded", function () {
  const navButtons = document.querySelectorAll(".referral-nav .nav-link");
  const views = document.querySelectorAll(".referral-view");
  const copyBtn = document.getElementById("copy-ref-link-btn");
  const linkInput = document.getElementById("referral-link-input");

  // Fungsi untuk beralih tampilan
  function switchView(viewToShow) {
    navButtons.forEach((btn) => {
      if (btn.dataset.view === viewToShow) {
        btn.classList.add("active");
      } else {
        btn.classList.remove("active");
      }
    });

    views.forEach((view) => {
      if (view.id === `view-${viewToShow}`) {
        view.style.display = "block";
      } else {
        view.style.display = "none";
      }
    });

    // Muat data jika perlu
    if (viewToShow === "anggota") {
      loadAnggotaData();
    } else if (viewToShow === "bonus") {
      loadBonusData();
    }
  }

  // Event listener untuk tombol navigasi
  navButtons.forEach((button) => {
    button.addEventListener("click", () => switchView(button.dataset.view));
  });

  // Fungsi untuk menyalin link
  copyBtn.addEventListener("click", function () {
    linkInput.select();
    document.execCommand("copy");
    this.innerHTML = '<i class="fas fa-check me-1"></i> Disalin!';
    setTimeout(() => {
      this.innerHTML = '<i class="fas fa-copy me-1"></i> Copy';
    }, 2000);
  });

  // Fungsi untuk memuat data anggota referral
  async function loadAnggotaData() {
    const tableBody = document.getElementById("anggota-referral-table");
    const totalText = document.getElementById("total-referral-text");
    tableBody.innerHTML =
      '<tr><td colspan="3" class="text-center">Memuat...</td></tr>';

    const response = await fetch("get_referral_data.php?view=anggota");
    const data = await response.json();

    totalText.textContent = `Total Referral Anda: ${data.length}`;
    tableBody.innerHTML = "";
    if (data.length === 0) {
      tableBody.innerHTML =
        '<tr><td colspan="3" class="text-center">Belum ada anggota.</td></tr>';
      return;
    }
    data.forEach((item, index) => {
      const date = new Date(item.registration_date).toLocaleDateString("id-ID");
      tableBody.innerHTML += `<tr><td>${index + 1}</td><td>${
        item.username
      }</td><td>${date}</td></tr>`;
    });
  }

  // Fungsi untuk memuat data bonus referral
  async function loadBonusData() {
    const tableBody = document.getElementById("bonus-referral-table");
    tableBody.innerHTML =
      '<tr><td colspan="3" class="text-center">Memuat...</td></tr>';

    const response = await fetch("get_referral_data.php?view=bonus");
    const data = await response.json();

    tableBody.innerHTML = "";
    if (data.length === 0) {
      tableBody.innerHTML =
        '<tr><td colspan="3" class="text-center">Belum ada bonus.</td></tr>';
      return;
    }
    data.forEach((item, index) => {
      const date = new Date(item.created_at).toLocaleDateString("id-ID");
      const amount = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
      }).format(item.amount);
      tableBody.innerHTML += `<tr><td>${
        index + 1
      }</td><td>${date}</td><td>${amount}</td></tr>`;
    });
  }
});
