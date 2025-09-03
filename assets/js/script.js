document.addEventListener("DOMContentLoaded", () => {
  // === TOGGLE SHOW/HIDE PASSWORD UNTUK SEMUA HALAMAN ===
  document.querySelectorAll(".toggle-password").forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.previousElementSibling;
      const icon = this.querySelector("i");
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    });
  });

  // =======================================================
  // == BAGIAN 1: LOGIKA UNTUK MENU GAME INTERAKTIF (index.php & beranda.php) ==
  // =======================================================
  const loginToast = document.getElementById("login-toast");

  let toastTimeout;
  function showToast(message) {
    if (!loginToast) return;
    const toastMessage = loginToast.querySelector("#toast-message");
    toastMessage.textContent = message;
    loginToast.classList.add("show");
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
      loginToast.classList.remove("show");
    }, 3000);
  }

  async function fetchProviders(category = "all", container) {
    if (!container) return;
    container.innerHTML = `<div class="p-5 text-center"><div class="spinner-border text-warning" role="status"></div></div>`;
    try {
      const response = await fetch(
        `api_get_providers.php?category=${encodeURIComponent(category)}`
      );
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);
      const providers = await response.json();
      displayProviders(providers, container);
    } catch (error) {
      container.innerHTML = `<p class="text-center text-danger p-3">Gagal memuat provider.</p>`;
      console.error("Fetch error:", error);
    }
  }

  function displayProviders(providers, container) {
    if (!container) return;
    container.innerHTML = ""; // Kosongkan kontainer

    if (!providers || providers.length === 0) {
      container.innerHTML = `<div class="p-3 text-center text-white-50">Tidak ada provider untuk kategori ini.</div>`;
      return;
    }

    const isDesktop = container.id.includes("desktop");
    const gridContainer = isDesktop ? document.createElement("div") : container;
    if (isDesktop) gridContainer.className = "provider-grid-desktop-inner";

    providers.forEach((provider) => {
      const providerItem = document.createElement("div");
      providerItem.className = "provider-logo-item";
      // Tambahkan badge promo/hot jika provider sesuai
      let badge = "";
      const name = provider.nama_provider
        ? provider.nama_provider.toLowerCase()
        : "";
      if (name.includes("pragmatic")) {
        badge = '<span class="promo-badge">PROMO</span>';
      } else if (name.includes("pgsoft") || name.includes("microgaming")) {
        badge = '<span class="hot-badge">HOT</span>';
      }
      // Perbaikan pada path gambar dan fallback text
      providerItem.innerHTML = `
              ${badge}
              <img src="${provider.logo_provider_url}" alt="${provider.nama_provider}" 
                   onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
              <span class="fallback-text" style="display:none;">${provider.nama_provider}</span>
          `;

      providerItem.addEventListener("click", (e) => {
        e.preventDefault();
        // Notifikasi untuk login
        window.Swal.fire({
          title: "Akses Dibatasi",
          text: "Silakan login terlebih dahulu untuk bermain!",
          icon: "warning",
          confirmButtonText: "Login Sekarang",
          showCancelButton: true,
          cancelButtonText: "Nanti Saja",
          background: "#212529",
          color: "#fff",
          confirmButtonColor: "#ff006e",
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = "login";
          }
        });
      });
      gridContainer.appendChild(providerItem);
    });

    if (isDesktop) {
      container.appendChild(gridContainer);
    }
  }

  function initializeGameMenu(menuId, containerId) {
    const menu = document.getElementById(menuId);
    const container = document.getElementById(containerId);

    if (menu && container) {
      menu.addEventListener("click", (e) => {
        const targetItem = e.target.closest(".menu-item-img");
        if (!targetItem) return;

        if (menu.querySelector(".active")) {
          menu.querySelector(".active").classList.remove("active");
        }
        targetItem.classList.add("active");

        const categoryFilter = targetItem.dataset.filter;
        fetchProviders(categoryFilter, container);
      });
      fetchProviders("all", container);
    }
  }

  initializeGameMenu("category-menu-mobile", "provider-list-container-mobile");
  initializeGameMenu(
    "category-menu-desktop",
    "provider-list-container-desktop"
  );

  // =======================================================
  // == BAGIAN 3: LOGIKA UNTUK HALAMAN PENDAFTARAN (daftar.php) ==
  // =======================================================
  const registrationForm = document.getElementById("registration-form");
  if (registrationForm) {
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirm_password");
    const passwordMatchMessage = document.getElementById(
      "password-match-message"
    );

    function validatePasswordMatch() {
      if (passwordInput.value === "" && confirmPasswordInput.value === "") {
        passwordMatchMessage.textContent = "";
        return;
      }
      if (passwordInput.value === confirmPasswordInput.value) {
        passwordMatchMessage.textContent = "✓ Password cocok!";
        passwordMatchMessage.className = "text-success form-text mt-1";
      } else {
        passwordMatchMessage.textContent = "✗ Password tidak cocok!";
        passwordMatchMessage.className = "text-danger form-text mt-1";
      }
    }
    if (passwordInput)
      passwordInput.addEventListener("keyup", validatePasswordMatch);
    if (confirmPasswordInput)
      confirmPasswordInput.addEventListener("keyup", validatePasswordMatch);

    const bankSelect = document.getElementById("bank_name");
    const accountNumberInput = document.getElementById("account_number");

    function applyAccountNumberValidation() {
      if (!bankSelect || !accountNumberInput) return;
      const selectedOption = bankSelect.options[bankSelect.selectedIndex];
      const minLength = selectedOption.getAttribute("data-minlength");
      const maxLength = selectedOption.getAttribute("data-maxlength");

      if (minLength && maxLength && minLength > 0) {
        accountNumberInput.setAttribute("minlength", minLength);
        accountNumberInput.setAttribute("maxlength", maxLength);
        accountNumberInput.placeholder = `Masukkan ${minLength}-${maxLength} digit nomor`;
        accountNumberInput.pattern = `\\d{${minLength},${maxLength}}`;
      } else {
        accountNumberInput.removeAttribute("minlength");
        accountNumberInput.removeAttribute("maxlength");
        accountNumberInput.removeAttribute("pattern");
        accountNumberInput.placeholder = "Nomor rekening / HP";
      }
    }

    if (bankSelect)
      bankSelect.addEventListener("change", applyAccountNumberValidation);
    if (accountNumberInput) {
      accountNumberInput.addEventListener("input", function () {
        const maxLength = this.getAttribute("maxlength");
        if (maxLength && this.value.length > maxLength) {
          this.value = this.value.slice(0, maxLength);
        }
      });
    }
    applyAccountNumberValidation();
  }

  // =======================================================
  // == BAGIAN 4: LOGIKA UNTUK MENU DROPDOWN HEADER (nav-guest.php) ==
  // =======================================================
  const providerLinks = document.querySelectorAll(".provider-link");
  providerLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      window.Swal.fire({
        title: "Akses Dibatasi",
        text: "Silakan login terlebih dahulu untuk melanjutkan!",
        icon: "warning",
        confirmButtonText: "Login Sekarang",
        showCancelButton: true,
        cancelButtonText: "Nanti Saja",
        background: "#212529",
        color: "#fff",
        confirmButtonColor: "#ff006e",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "login";
        }
      });
    });
  });

  const mobileDropdownToggles = document.querySelectorAll(
    ".mobile-dropdown-toggle"
  );
  mobileDropdownToggles.forEach((toggle) => {
    toggle.addEventListener("click", function (e) {
      e.preventDefault();
      this.classList.toggle("active");
      const menu = this.nextElementSibling;
      if (menu.style.display === "block") {
        menu.style.display = "none";
      } else {
        menu.style.display = "block";
      }
    });
  });

  // ========================================================
  // == BAGIAN 5: LOGIKA BARU UNTUK HALAMAN BERANDA (beranda.php) ==
  // ========================================================
  const berandaCategoryMenu = document.getElementById("beranda-category-menu");
  const gameDisplayContainer = document.getElementById(
    "game-display-container"
  );
  const gameSectionTitle = document.getElementById("game-section-title");
  const searchGameInput = document.getElementById("search-game-input");

  if (berandaCategoryMenu && gameDisplayContainer) {
    let currentBerandaCategory = "all";
    let currentBerandaProvider = "all"; // Menyimpan provider yang sedang aktif untuk filtering game
    let currentSearchQuery = "";
    let viewMode = "providers"; // 'providers' atau 'games'
    let currentPage = 1;
    const perPage = 200;
    let totalGames = 0;
    let totalPages = 1;
    let paginationContainer = null;

    // --- Tambahan: Auto-select kategori dari URL ---
    const urlParams = new URLSearchParams(window.location.search);
    const urlCategory = urlParams.get("category");
    if (urlCategory) {
      // Cari elemen kategori yang sesuai dan trigger klik
      const catItem = Array.from(
        berandaCategoryMenu.querySelectorAll(".category-item")
      ).find(
        (el) =>
          el.dataset.category &&
          el.dataset.category.replace(/\s+/g, "").toLowerCase() ===
            urlCategory.toLowerCase()
      );
      if (catItem) {
        // Nonaktifkan semua dulu
        berandaCategoryMenu
          .querySelectorAll(".category-item.active")
          .forEach((el) => el.classList.remove("active"));
        catItem.classList.add("active");
        currentBerandaCategory = catItem.dataset.category;
      }
    }
    // --- END Tambahan ---

    // Fungsi untuk memuat konten (providers atau games) di beranda.php
    async function loadGameContentBeranda() {
      if (!gameDisplayContainer) return;
      gameDisplayContainer.innerHTML = `<div class="p-5 text-center w-100"><div class="spinner-border text-warning"></div></div>`;
      if (!paginationContainer) {
        paginationContainer = document.createElement("div");
        paginationContainer.id = "game-pagination";
        paginationContainer.style =
          "display:flex;justify-content:center;gap:4px;margin:12px 0 0 0;flex-wrap:wrap;";
        gameDisplayContainer.parentNode.appendChild(paginationContainer);
      }

      let apiUrl = "";
      // Logika penentuan API URL dan judul bagian
      if (viewMode === "providers") {
        apiUrl = `api_get_providers.php?category=${encodeURIComponent(
          currentBerandaCategory
        )}`;
        gameSectionTitle.textContent =
          currentBerandaCategory === "all"
            ? "Semua Provider"
            : `Provider ${currentBerandaCategory}`;
      } else {
        // viewMode === "games"
        // Penting: pastikan currentBerandaProvider tidak "all" jika ingin memfilter game berdasarkan provider
        // Kalau "all", berarti user mungkin belum klik provider spesifik tapi langsung cari game
        // atau klik kategori dan ingin melihat semua game dari semua provider di kategori itu.
        // API get_games.php sudah menangani "all" provider dengan mengabaikan filter provider.
        apiUrl = `api_get_games.php?provider=${encodeURIComponent(
          currentBerandaProvider
        )}&category=${encodeURIComponent(
          currentBerandaCategory
        )}&search=${encodeURIComponent(
          currentSearchQuery
        )}&page=${currentPage}&per_page=${perPage}`;

        // Atur judul berdasarkan provider dan kategori
        let title = "Semua Game";
        if (currentBerandaProvider !== "all") {
          title = `Game ${currentBerandaProvider}`;
        }
        if (currentBerandaCategory !== "all") {
          title += ` (${currentBerandaCategory})`;
        }
        if (currentSearchQuery !== "") {
          title += ` - Hasil Pencarian "${currentSearchQuery}"`;
        }
        gameSectionTitle.textContent = title;
      }

      try {
        const response = await fetch(apiUrl);
        if (!response.ok) {
          // Tambahkan cek untuk respons HTTP yang tidak OK
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json(); // Coba parsing JSON

        // Pastikan respons adalah objek dan bukan string error dari PHP
        if (typeof data !== "object" || data === null) {
          throw new Error("Respons API bukan JSON yang valid atau kosong.");
        }

        if (viewMode === "providers") {
          displayProvidersForBeranda(data);
          paginationContainer.style.display = "none";
        } else {
          // viewMode === "games"
          displayGamesForBeranda(data.games);
          totalGames = data.total_games;
          totalPages = Math.ceil(totalGames / perPage);
          renderPagination();
        }
      } catch (error) {
        // Ini akan menangkap error jaringan, error parsing JSON, atau error HTTP
        gameDisplayContainer.innerHTML = `<p class="text-center text-danger p-3 w-100">Gagal memuat konten. ${error.message}. Coba refresh halaman.</p>`;
        if (paginationContainer) paginationContainer.style.display = "none";
        console.error("Fetch error for beranda game content:", error);
      }
    }

    // Fungsi untuk menampilkan daftar provider di beranda.php
    function displayProvidersForBeranda(providers) {
      gameDisplayContainer.innerHTML = "";
      if (!providers || providers.length === 0) {
        gameDisplayContainer.innerHTML = `<p class="text-center text-white-50 p-3 w-100">Tidak ada provider untuk kategori ini.</p>`;
        return;
      }
      // Deteksi kategori aktif
      const activeCat = (
        berandaCategoryMenu.querySelector(".category-item.active")?.dataset
          .category || ""
      ).toLowerCase();
      const externalLinkCategories = [
        "casino",
        "sports",
        "arcade",
        "crashgame",
        "crash-game",
        "poker",
        "esport",
        "sabungayam",
        "sabung ayam",
      ];
      providers.forEach((provider) => {
        let providerCard;
        if (
          externalLinkCategories.includes(activeCat) &&
          provider.external_url
        ) {
          providerCard = document.createElement("a");
          providerCard.href = provider.external_url;
          providerCard.target = "_blank";
          providerCard.rel = "noopener noreferrer";
        } else {
          providerCard = document.createElement("a");
          providerCard.href = "#";
        }
        providerCard.className = "provider-card d-block";
        providerCard.style.width = "100%";
        providerCard.dataset.provider = provider.nama_provider;
        // Tambahkan badge promo/hot jika provider sesuai
        let badge = "";
        const name = provider.nama_provider
          ? provider.nama_provider.toLowerCase()
          : "";
        if (name.includes("pragmatic")) {
          badge = '<span class="promo-badge">PROMO</span>';
        } else if (name.includes("pgsoft") || name.includes("microgaming")) {
          badge = '<span class="hot-badge">HOT</span>';
        }
        providerCard.innerHTML = `
                ${badge}
                <img src="${
                  provider.logo_provider_url
                }" class="img-fluid rounded" alt="${provider.nama_provider}"
                    onerror="this.src='https://placehold.co/100x70/222/FFF?text=${encodeURIComponent(
                      provider.nama_provider
                    )}';
                             this.style.backgroundColor='#333';
                             this.style.objectFit='contain';">
            `;
        if (
          !(externalLinkCategories.includes(activeCat) && provider.external_url)
        ) {
          providerCard.addEventListener("click", (e) => {
            e.preventDefault();
            viewMode = "games";
            currentBerandaProvider = provider.nama_provider;
            currentSearchQuery = "";
            if (searchGameInput) searchGameInput.value = "";
            loadGameContentBeranda();
          });
        }
        gameDisplayContainer.appendChild(providerCard);
      });
    }

    // Fungsi untuk menampilkan daftar game di beranda.php
    function displayGamesForBeranda(games) {
      gameDisplayContainer.innerHTML = "";
      if (!games || games.length === 0) {
        gameDisplayContainer.innerHTML = `<p class="text-center text-white-50 p-3 w-100">Tidak ada game ditemukan untuk pilihan ini.</p>`;
        return;
      }
      games.forEach((game) => {
        const gameCard = document.createElement("div");
        gameCard.className = "game-card-v2";
        // Menghasilkan persentase RTP awal secara acak (antara 20% dan 98%)
        const initialRtp = Math.floor(Math.random() * (98 - 20 + 1)) + 20;
        const rtpLevel = initialRtp < 40 ? 'low' : initialRtp < 70 ? 'medium' : 'high';
        
        gameCard.innerHTML = `
            <div class="game-image-container">
                <img src="${game.gambar_thumbnail_url}" alt="${
          game.nama_game
        }" class="img-fluid"
                    onerror="this.src='https://placehold.co/300x200/222/FFF?text=${encodeURIComponent(
                      game.nama_game
                    )}';">
            </div>
            <div class="rtp-progress-section">
                <div class="rtp-live-indicator">LIVE</div>
                <div class="rtp-progress-bar">
                    <i class="fas fa-chart-line rtp-icon"></i>
                    <div class="rtp-bar-container">
                        <div class="rtp-label">Live RTP</div>
                        <div class="rtp-bar-bg">
                            <div class="rtp-bar-fill" data-rtp-level="${rtpLevel}" style="width: ${initialRtp}%;"></div>
                        </div>
                    </div>
                    <span class="rtp-percentage">${initialRtp}%</span>
                </div>
            </div>
            <div class="game-card-overlay">
                <span>${game.nama_game}</span>
                <button class="btn btn-warning btn-sm play-now-btn" data-game-name="${
                  game.nama_game
                }" data-game-image="${
          game.gambar_thumbnail_url
        }" data-game-url="${
          game.game_url ? game.game_url : ""
        }">PLAY NOW</button>
            </div>
        `;
        
        // Set unique ID untuk game card agar bisa diupdate RTP-nya
        gameCard.setAttribute('data-game-id', `game-${Math.random().toString(36).substr(2, 9)}`);

        // Add click event for the play button
        const playBtn = gameCard.querySelector(".play-now-btn");
        playBtn.addEventListener("click", function (e) {
          showGamePopup(
            this.dataset.gameName,
            this.dataset.gameImage,
            this.dataset.gameUrl
          );
        });

        gameDisplayContainer.appendChild(gameCard);
      });
      
      // Mulai update RTP otomatis setelah semua game cards dibuat
      startLiveRTPUpdates();
    }

    function renderPagination() {
      if (!paginationContainer) return;
      paginationContainer.innerHTML = "";
      if (totalPages <= 1) {
        paginationContainer.style.display = "none";
        return;
      }
      paginationContainer.style.display = "flex";
      // Previous button
      const prevBtn = document.createElement("button");
      prevBtn.innerHTML = "&laquo;";
      prevBtn.className = "btn btn-sm btn-secondary";
      prevBtn.disabled = currentPage === 1;
      prevBtn.onclick = () => {
        currentPage--;
        loadGameContentBeranda();
      };
      paginationContainer.appendChild(prevBtn);
      // Page numbers (max 7 shown, with ... if needed)
      let start = Math.max(1, currentPage - 3);
      let end = Math.min(totalPages, currentPage + 3);
      if (currentPage <= 4) {
        end = Math.min(7, totalPages);
      }
      if (currentPage > totalPages - 4) {
        start = Math.max(1, totalPages - 6);
      }
      if (start > 1) {
        const firstBtn = document.createElement("button");
        firstBtn.textContent = "1";
        firstBtn.className = "btn btn-sm btn-outline-warning";
        firstBtn.onclick = () => {
          currentPage = 1;
          loadGameContentBeranda();
        };
        paginationContainer.appendChild(firstBtn);
        if (start > 2) {
          const dots = document.createElement("span");
          dots.textContent = "...";
          dots.style = "padding:0 4px;color:#ff006e;";
          paginationContainer.appendChild(dots);
        }
      }
      for (let i = start; i <= end; i++) {
        const btn = document.createElement("button");
        btn.textContent = i;
        btn.className =
          "btn btn-sm " +
          (i === currentPage ? "btn-warning" : "btn-outline-warning");
        btn.disabled = i === currentPage;
        btn.onclick = () => {
          currentPage = i;
          loadGameContentBeranda();
        };
        paginationContainer.appendChild(btn);
      }
      if (end < totalPages) {
        if (end < totalPages - 1) {
          const dots = document.createElement("span");
          dots.textContent = "...";
          dots.style = "padding:0 4px;color:#ff006e;";
          paginationContainer.appendChild(dots);
        }
        const lastBtn = document.createElement("button");
        lastBtn.textContent = totalPages;
        lastBtn.className = "btn btn-sm btn-outline-warning";
        lastBtn.onclick = () => {
          currentPage = totalPages;
          loadGameContentBeranda();
        };
        paginationContainer.appendChild(lastBtn);
      }
      // Next button
      const nextBtn = document.createElement("button");
      nextBtn.innerHTML = "&raquo;";
      nextBtn.className = "btn btn-sm btn-secondary";
      nextBtn.disabled = currentPage === totalPages;
      nextBtn.onclick = () => {
        currentPage++;
        loadGameContentBeranda();
      };
      paginationContainer.appendChild(nextBtn);
    }

    // Function to show game popup
    function showGamePopup(gameName, gameImage, gameUrl) {
      window.Swal.fire({
        title: `
          <div style="text-align: center; margin-bottom: 0; padding-bottom: 0;">
            <div style="font-size: 1.15rem; font-weight: bold; color: #ff006e; margin-bottom: 2px; margin-top: 0;">${gameName}</div>
            <div style="font-size: 0.92rem; color: #ccc; margin-bottom: 2px; margin-top: 0;">Siap untuk bermain?</div>
          </div>
        `,
        html: `
          <div style="text-align: center; margin: 0; padding: 0;">
            <img src="${gameImage}" alt="${gameName}" style="width: 140px; height: 140px; border-radius: 16px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.18); margin-bottom: 4px; margin-top: 0; display: block; margin-left: auto; margin-right: auto;">
            <div style="font-size: 1rem; color: #ff006e; margin-bottom: 0; margin-top: 0;">Klik Mulai Bermain Untuk Melanjutkan</div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-play"></i> Mulai Bermain',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        confirmButtonColor: "#ff006e",
        cancelButtonColor: "#6c757d",
        background: "#212529",
        color: "#fff",
        customClass: {
          popup: "game-popup-custom",
          title: "game-popup-title",
          confirmButton: "game-popup-confirm",
          cancelButton: "game-popup-cancel",
        },
        showClass: {
          popup: "animate__animated animate__zoomIn animate__faster",
        },
        hideClass: {
          popup: "animate__animated animate__zoomOut animate__faster",
        },
        didOpen: function () {
          var img = document.querySelector(
            ".game-popup-custom .popup-game-img"
          );
          if (img) {
            img.onerror = function () {
              this.src = "https://placehold.co/140x140/222/FFF?text=Game";
            };
            img.src = gameImage;
          }
        },
      }).then((result) => {
        if (result.isConfirmed) {
          // CEK SALDO USER
          if (typeof window.USER_SALDO !== 'undefined' && window.USER_SALDO <= 0) {
            window.Swal.fire({
              title: 'Saldo 0',
              text: 'Saldo 0 silahkan deposit terlebih dahulu untuk memulai game.',
              icon: 'warning',
              confirmButtonText: 'OK',
              confirmButtonColor: '#ff006e',
              background: '#212529',
              color: '#fff',
            }).then(() => {
              window.location.href = 'deposit';
            });
            return;
          }
          if (gameUrl && gameUrl !== "" && gameUrl !== "NULL") {
            window.open(gameUrl, "_blank");
          } else {
            window.Swal.fire({
              title: "Game Belum Tersedia",
              text: "Game ini belum memiliki link atau sedang dalam pengembangan.",
              icon: "info",
              confirmButtonText: "OK",
              confirmButtonColor: "#ff006e",
              background: "#212529",
              color: "#fff",
            });
          }
        }
      });
    }
    // Event listener untuk menu kategori di beranda.php
    berandaCategoryMenu.addEventListener("click", (e) => {
      if (e.target.classList.contains("category-item")) {
        // Jika kategori Togel, biarkan browser redirect ke href="togel"
        if (e.target.dataset.category === "Togel") {
          return; // Tidak preventDefault, biarkan redirect
        }
        e.preventDefault();

        if (berandaCategoryMenu.querySelector(".active")) {
          berandaCategoryMenu
            .querySelector(".active")
            .classList.remove("active");
        }
        e.target.classList.add("active");

        currentBerandaCategory = e.target.dataset.category;
        viewMode = "providers"; // Selalu kembali ke tampilan provider saat kategori berubah
        currentBerandaProvider = "all"; // Reset provider saat kategori berubah
        currentSearchQuery = ""; // Reset pencarian saat kategori berubah
        currentPage = 1; // Reset halaman saat kategori berubah
        if (searchGameInput) searchGameInput.value = "";

        loadGameContentBeranda(); // Muat ulang konten
      }
    });

    // Event listener untuk pencarian game di beranda.php
    if (searchGameInput) {
      let searchTimeout;
      searchGameInput.addEventListener("input", () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          currentSearchQuery = searchGameInput.value.trim();
          // Saat user mengetik pencarian, kita paksa mode ke 'games'
          viewMode = "games";
          currentPage = 1; // Reset halaman saat pencarian berubah
          // Jika belum ada provider spesifik yang dipilih (masih "all"),
          // pencarian akan berlaku untuk semua game dalam kategori aktif.
          // currentBerandaProvider akan tetap "all" jika tidak pernah diklik provider.
          loadGameContentBeranda();
        }, 500); // Debounce input pencarian untuk performa
      });
    }

    // Panggil pertama kali saat halaman beranda dimuat
    loadGameContentBeranda();

    // --- FUNGSI BARU UNTUK UPDATE RTP REAL-TIME ---
    function updateRandomRTP() {
        const gameCards = document.querySelectorAll('.game-card-v2');
        if (gameCards.length === 0) return;

        gameCards.forEach(card => {
            const newRtp = Math.floor(Math.random() * (90 - 20 + 1)) + 20;
            const rtpFill = card.querySelector('.rtp-bar-fill');
            const rtpText = card.querySelector('.rtp-percentage');

            if (rtpFill && rtpText) {
                rtpFill.style.width = `${newRtp}%`;
                rtpText.textContent = `RTP ${newRtp}%`;
            }
        });
    }

    // Set interval untuk update RTP setiap 20 menit (20 * 60 * 1000 ms)
    setInterval(updateRandomRTP, 20 * 60 * 1000);
  }
});

/**
 * File: assets/js/script.js (REVISI FINAL FULL - Penambahan Transaksi.php JS)
 */

document.addEventListener("DOMContentLoaded", () => {
  // ... (SEMUA KODE JAVASCRIPT document.addEventListener('DOMContentLoaded') SEBELUMNYA TETAP SAMA) ...

  // ========================================================
  // == BAGIAN BARU: LOGIKA UNTUK HALAMAN TRANSAKSI (transaksi.php) ==
  // ========================================================
  const transactionTabs = document.getElementById("transactionTabs");
  if (transactionTabs) {
    const dateRangeWalletInput = document.getElementById("date_range_wallet");
    const typeWalletSelect = document.getElementById("type_wallet");
    const searchWalletSummaryBtn = document.getElementById(
      "search_wallet_summary"
    );
    const walletSummaryResultsBody = document.getElementById(
      "wallet-summary-results"
    );

    // Fungsi untuk mengambil dan menampilkan data Wallet Summary
    async function fetchWalletSummary() {
      if (
        !dateRangeWalletInput ||
        !typeWalletSelect ||
        !walletSummaryResultsBody
      )
        return;

      const dateRange = dateRangeWalletInput.value;
      const type = typeWalletSelect.value;

      walletSummaryResultsBody.innerHTML = `<tr><td colspan="3" class="text-center text-muted"><div class="spinner-border spinner-border-sm" role="status"></div> Loading...</td></tr>`;

      try {
        const response = await fetch(
          `api_get_wallet_transactions.php?type=${type}&date_range=${dateRange}`
        );
        const transactions = await response.json();

        displayWalletTransactions(transactions);
      } catch (error) {
        console.error("Error fetching wallet summary:", error);
        walletSummaryResultsBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">Gagal memuat data. Silakan coba lagi.</td></tr>`;
      }
    }

    // Fungsi untuk menampilkan data ke dalam tabel
    function displayWalletTransactions(transactions) {
      if (!walletSummaryResultsBody) return; // Tambahkan penjaga
      walletSummaryResultsBody.innerHTML = ""; // Kosongkan tabel

      if (!transactions || transactions.length === 0) {
        walletSummaryResultsBody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">Tidak ada data transaksi untuk filter ini.</td></tr>`;
        return;
      }

      transactions.forEach((trx) => {
        const row = document.createElement("tr");

        // Format tanggal
        const date = new Date(trx.created_at);
        const formattedDate = date.toLocaleString("id-ID", {
          year: "numeric",
          month: "short",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        });

        // Format status dengan badge
        let statusBadge;
        switch (trx.status) {
          case "approved":
            statusBadge = '<span class="badge bg-success">Approved</span>';
            break;
          case "rejected":
            statusBadge = '<span class="badge bg-danger">Rejected</span>';
            break;
          default: // 'pending'
            statusBadge =
              '<span class="badge bg-warning text-dark">Pending</span>';
            break;
        }

        // Format jumlah
        const formattedAmount = new Intl.NumberFormat("id-ID", {
          style: "currency",
          currency: "IDR",
          minimumFractionDigits: 0,
        }).format(trx.amount);

        // === PERUBAHAN UTAMA DI SINI ===
        let amountCell;
        if (trx.transaction_type === "Deposit") {
          amountCell = `<td class="text-success fw-bold">+ ${formattedAmount}</td>`;
        } else {
          // Ini akan menangani 'Withdraw'
          amountCell = `<td class="text-danger fw-bold">- ${formattedAmount}</td>`;
        }

        row.innerHTML = `
    <td><span class="fw-bold">${trx.transaction_type}</span></td>
    <td><small>${formattedDate}</small></td>
    ${amountCell}
    <td>${statusBadge}</td>
`;
        walletSummaryResultsBody.appendChild(row);
      });
    }

    // Event listener untuk tombol cari
    if (searchWalletSummaryBtn) {
      searchWalletSummaryBtn.addEventListener("click", fetchWalletSummary);
    }

    // Langsung panggil saat halaman dimuat pertama kali
    fetchWalletSummary();
    function initializeDateRangePicker(elementId) {
      const element = document.getElementById(elementId);
      if (element) {
        window.$(element).daterangepicker({
          startDate: window.moment(), // Set tanggal mulai ke hari ini (real-time)
          endDate: window.moment(), // Set tanggal selesai ke hari ini (real-time)
          locale: {
            format: "YYYY-MM-DD",
            separator: " - ",
          },
          ranges: {
            "Hari Ini": [window.moment(), window.moment()],
            Kemarin: [
              window.moment().subtract(1, "days"),
              window.moment().subtract(1, "days"),
            ],
            "7 Hari Terakhir": [
              window.moment().subtract(6, "days"),
              window.moment(),
            ],
            "30 Hari Terakhir": [
              window.moment().subtract(29, "days"),
              window.moment(),
            ],
            "Bulan Ini": [
              window.moment().startOf("month"),
              window.moment().endOf("month"),
            ],
            "Bulan Lalu": [
              window.moment().subtract(1, "month").startOf("month"),
              window.moment().subtract(1, "month").endOf("month"),
            ],
          },
        });
      }
    }

    initializeDateRangePicker("date_range_wallet");
    initializeDateRangePicker("date_range_bet");
  }

  // ... (Sisa kode JS Anda yang sudah ada sebelumnya)
});

/**
 * File: assets/js/script.js (REVISI FINAL FULL - Fungsionalitas Deposit.php Lengkap & Salin Rekening)
 */

// ... (SEMUA KODE JAVASCRIPT SEBELUMNYA TETAP SAMA) ...

document.addEventListener("DOMContentLoaded", () => {
  // ... (SEMUA KODE JAVASCRIPT document.addEventListener('DOMContentLoaded') SEBELUMNYA TETAP SAMA) ...

  // ========================================================
  // == BAGIAN BARU: LOGIKA UNTUK HALAMAN DEPOSIT (deposit.php) ==
  // ========================================================
  const depositChannelButtonsContainer = document.getElementById(
    "deposit-channel-buttons"
  );
  const qrisFormSection = document.getElementById("qris-form-section");
  const bankTransferFormSection = document.getElementById(
    "bank-transfer-form-section"
  );
  const depositAmountInputs = document.querySelectorAll(
    ".deposit-amount-input"
  );

  const purposeBankSelect = document.getElementById("purpose_bank");
  const selectedPurposeInfo = document.getElementById("selected_purpose_info");
  const depositBankForm = document.getElementById("deposit-bank-form"); // Untuk event listener submit
  const depositQrisForm = document.getElementById("deposit-qris-form"); // Untuk event listener submit QRIS

  // Accordion elements untuk Bank Transfer
  const bankDepositAccordion = document.getElementById("bankDepositAccordion"); // Wrapper accordion
  const collapseNotesBank = document.getElementById("collapseNotesBank");
  const collapseBankStatusBank = document.getElementById(
    "collapseBankStatusBank"
  );

  if (
    depositChannelButtonsContainer &&
    qrisFormSection &&
    bankTransferFormSection
  ) {
    // Fungsi untuk menginisialisasi atau memicu channel terpilih
    function setActiveDepositChannel(channel) {
      document.querySelectorAll(".deposit-channels button").forEach((btn) => {
        if (btn.dataset.channel === channel) {
          btn.classList.add("active");
          // Trigger Bootstrap collapse untuk show form yang aktif
          const targetElement = document.querySelector(btn.dataset.bsTarget);
          if (targetElement) {
            const bsCollapse = new window.bootstrap.Collapse(targetElement, {
              toggle: false,
            });
            bsCollapse.show();
          }
        } else {
          btn.classList.remove("active");
          // Trigger Bootstrap collapse untuk hide form yang tidak aktif
          const targetElement = document.querySelector(btn.dataset.bsTarget);
          if (targetElement) {
            const bsCollapse = new window.bootstrap.Collapse(targetElement, {
              toggle: false,
            });
            bsCollapse.hide();
          }
        }
      });

      // Logika visibilitas accordion Catatan & Status Bank berdasarkan channel
      if (channel === "qris") {
        // Sembunyikan accordion di channel Bank Transfer
        if (bankDepositAccordion) bankDepositAccordion.style.display = "none";
      } else {
        // channel === 'bank' (Transfer Bank, E-Wallet, Pulsa)
        // Tampilkan accordion di channel Bank Transfer
        if (bankDepositAccordion) bankDepositAccordion.style.display = "block";

        // Tutup accordion catatan dan status bank secara default saat tab ini aktif
        // Pastikan Bootstrap Collapse instance dibuat HANYA JIKA accordion belum diinisialisasi
        // atau selalu panggil hide() agar selalu tersembunyi defaultnya
        if (collapseNotesBank)
          new window.bootstrap.Collapse(collapseNotesBank, {
            toggle: false,
          }).hide();
        if (collapseBankStatusBank)
          new window.bootstrap.Collapse(collapseBankStatusBank, {
            toggle: false,
          }).hide();
      }
    }

    depositChannelButtonsContainer.addEventListener("click", (e) => {
      const clickedBtn = e.target.closest("button");
      if (clickedBtn && clickedBtn.dataset.channel) {
        setActiveDepositChannel(clickedBtn.dataset.channel);
      }
    });

    // Memicu inisialisasi default saat halaman dimuat
    setActiveDepositChannel("qris");

    // === FUNGSI FORMAT ANGKA (50000 -> 50.000) & INFO MIN/MAX DEPOSIT ===
    function formatAndValidateAmountInput(inputElement) {
      let value = inputElement.value.replace(/\D/g, ""); // Hapus semua non-digit
      if (value === "") {
        inputElement.value = "";
        return;
      }
      value = Number.parseInt(value, 10).toLocaleString("id-ID"); // Format sebagai ribuan dengan titik
      inputElement.value = value;

      // Dapatkan info bonus dan info rekening tujuan (jika ada)
      const bonusSelect = inputElement.id.includes("_qris")
        ? document.getElementById("bonus_qris")
        : document.getElementById("bonus_bank");
      const selectedBonusOption =
        bonusSelect.options[bonusSelect.selectedIndex];
      const minDepositBonus = Number.parseFloat(
        selectedBonusOption.dataset.minDeposit || 0
      );
      const maxBonusAmount = Number.parseFloat(
        selectedBonusOption.dataset.maxBonus || 0
      );
      const percentage = Number.parseFloat(
        selectedBonusOption.dataset.percentage || 0
      );
      const turnover = Number.parseFloat(
        selectedBonusOption.dataset.turnover || 1
      );

      const purposeSelect = inputElement.id.includes("_qris")
        ? null
        : document.getElementById("purpose_bank");
      let minDepositAcc = 0;
      let maxDepositAcc = null;
      if (purposeSelect && purposeSelect.value !== "") {
        const selectedPurposeOption =
          purposeSelect.options[purposeSelect.selectedIndex];
        minDepositAcc = Number.parseFloat(
          selectedPurposeOption.dataset.minDepositAcc || 0
        );
        maxDepositAcc = Number.parseFloat(
          selectedPurposeOption.dataset.maxDepositAcc || 0
        );
      }

      const overallMinDeposit = Math.max(minDepositBonus, minDepositAcc);

      const infoDiv = inputElement.nextElementSibling; // div form-text setelah input

      let infoText = `Min: IDR ${overallMinDeposit.toLocaleString("id-ID")}`;

      if (maxDepositAcc && maxDepositAcc > 0) {
        // Jika ada max deposit dari rekening
        infoText += ` | Max: IDR ${maxDepositAcc.toLocaleString("id-ID")}`;
      }

      if (percentage > 0 && selectedBonusOption.value !== "0") {
        // Tampilkan bonus info hanya jika bonus bukan "Tanpa Bonus" (id=0)
        infoText += ` | Bonus: ${percentage}% (TO ${turnover}x)`;
      }

      if (infoDiv && infoDiv.classList.contains("form-text")) {
        infoDiv.textContent = infoText;
      }
    }

    depositAmountInputs.forEach((input) => {
      input.addEventListener("input", function () {
        formatAndValidateAmountInput(this);
      });
      // Pemicu format saat load jika ada nilai default
      formatAndValidateAmountInput(input);
    });

    // === LOGIKA UNTUK DROPDOWN TUJUAN BANK / E-WALLET (purpose_bank) & TOMBOL SALIN ===
    if (purposeBankSelect) {
      purposeBankSelect.addEventListener("change", function () {
        const selectedOption = this.options[this.selectedIndex];
        const accountName = selectedOption.dataset.accountName;
        const accountNumber = selectedOption.dataset.accountNumber;
        const methodName = selectedOption.dataset.methodName;

        if (selectedPurposeInfo) {
          if (accountName && accountNumber) {
            selectedPurposeInfo.innerHTML = `
                          Transfer ke: <strong>${methodName}</strong><br>
                          Nama: <strong>${accountName}</strong><br>
                          Nomor: <strong><span id="admin-account-number">${accountNumber}</span></strong> 
                          <button type="button" class="btn btn-sm btn-outline-warning ms-2 copy-button" data-clipboard-target="#admin-account-number" title="Salin Nomor Rekening">
                              <i class="fas fa-copy"></i> Salin
                          </button>
                      `;
            // Inisialisasi event listener untuk tombol salin baru
            const copyButton =
              selectedPurposeInfo.querySelector(".copy-button");
            if (copyButton) {
              copyButton.addEventListener("click", function () {
                const targetId = this.dataset.clipboardTarget;
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                  window.navigator.clipboard
                    .writeText(targetElement.textContent)
                    .then(() => {
                      window.Swal.fire({
                        title: "Berhasil Disalin!",
                        text: targetElement.textContent,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 1500,
                        background: "#212529",
                        color: "#fff",
                      });
                    })
                    .catch((err) => {
                      console.error("Gagal menyalin: ", err);
                      window.Swal.fire({
                        title: "Gagal Salin!",
                        text: "Silakan salin manual.",
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 1500,
                        background: "#212529",
                        color: "#fff",
                      });
                    });
                }
              });
            }
          } else {
            selectedPurposeInfo.innerHTML = "";
          }
        }
        // Setelah memilih tujuan, pemicu update info amount
        const amountInput = document.getElementById("amount_bank");
        if (amountInput) formatAndValidateAmountInput(amountInput);
      });
      // Pemicu change saat load jika ada nilai default
      purposeBankSelect.dispatchEvent(new Event("change"));
    }

    // === VALIDASI JUMLAH DEPOSIT SEBELUM SUBMIT ===
    function validateDepositAmount(
      amountInputId,
      bonusSelectId,
      purposeSelectId = null
    ) {
      const amountInput = document.getElementById(amountInputId);
      const bonusSelect = document.getElementById(bonusSelectId);

      if (!amountInput || !bonusSelect) return true; // Tidak ada elemen, lewati validasi

      const selectedBonusOption =
        bonusSelect.options[bonusSelect.selectedIndex];
      const minDepositBonus = Number.parseFloat(
        selectedBonusOption.dataset.minDeposit || 0
      );

      let minDepositAcc = 0;
      let maxDepositAcc = null;
      if (purposeSelectId) {
        const purposeSelect = document.getElementById(purposeSelectId);
        if (purposeSelect && purposeSelect.value !== "") {
          const selectedPurposeOption =
            purposeSelect.options[purposeSelect.selectedIndex];
          minDepositAcc = Number.parseFloat(
            selectedPurposeOption.dataset.minDepositAcc || 0
          );
          maxDepositAcc = Number.parseFloat(
            selectedPurposeOption.dataset.maxDepositAcc || 0
          );
        }
      }

      const overallMinDeposit = Math.max(minDepositBonus, minDepositAcc);
      const cleanAmount =
        Number.parseFloat(amountInput.value.replace(/\D/g, "")) || 0;

      if (cleanAmount < overallMinDeposit) {
        window.Swal.fire({
          title: "Deposit Kurang!",
          text: `Minimal deposit adalah IDR ${overallMinDeposit.toLocaleString(
            "id-ID"
          )}.`,
          icon: "warning",
          background: "#212529",
          color: "#fff",
          confirmButtonColor: "#ff006e",
        });
        return false;
      }
      if (maxDepositAcc && maxDepositAcc > 0 && cleanAmount > maxDepositAcc) {
        window.Swal.fire({
          title: "Deposit Berlebihan!",
          text: `Maksimal deposit untuk rekening ini adalah IDR ${maxDepositAcc.toLocaleString(
            "id-ID"
          )}.`,
          icon: "warning",
          background: "#212529",
          color: "#fff",
          confirmButtonColor: "#ff006e",
        });
        return false;
      }
      return true;
    }
  }
  function handleRefreshBalance(buttonId, balanceElementId) {
    const refreshButton = document.getElementById(buttonId);
    const balanceElement = document.querySelector(balanceElementId); // Menggunakan querySelector untuk fleksibilitas

    if (refreshButton && balanceElement) {
      refreshButton.addEventListener("click", async function () {
        const icon = this.querySelector("i");

        // Tambahkan animasi putar pada ikon
        icon.classList.add("fa-spin");

        try {
          const response = await fetch("api_get_balance.php");
          const data = await response.json();

          if (data.status === "success") {
            // Update tampilan saldo
            balanceElement.textContent = data.formatted_balance;
          } else {
            // Tampilkan pesan error jika gagal (opsional)
            console.error("Gagal refresh saldo:", data.message);
          }
        } catch (error) {
          console.error("Error koneksi saat refresh saldo:", error);
        } finally {
          // Hentikan animasi putar setelah 1 detik
          setTimeout(() => {
            icon.classList.remove("fa-spin");
          }, 1000);
        }
      });
    }
  }

  // Panggil fungsi untuk kedua tombol
  handleRefreshBalance("refresh-balance", "#user-balance span");
  handleRefreshBalance("sidebar-refresh-balance", "#sidebar-balance span");
});

// Logika untuk Floating Social Menu
const floatingMenu = document.querySelector(".floating-social-menu");
if (floatingMenu) {
  const toggleButton = floatingMenu.querySelector(".menu-toggle-button");
  toggleButton.addEventListener("click", () => {
    floatingMenu.classList.toggle("active");
  });
}
// === FLOATING WITHDRAW NOTIF ===
(() => {
  const usernames = [
    "superpr",
    "jackpot88",
    "winnerku",
    "sultan99",
    "cuanmax",
    "pragmatic",
    "pgsoftid",
    "microwin",
    "luckyspin",
    "megajp",
    "hotplayer",
    "maxwin",
    "gacor88",
    "slotboss",
    "kingluck",
    "queenjp",
    "bigwin",
    "luckypr",
    "sugarrush",
    "wildprag",
    "pgsoftku",
    "mgaming",
    "microslot",
    "pragmax",
    "pgsoft88",
    "mgjack",
    "sugarmg",
    "pragmaticid",
    "pgsoftpro",
    "mgpro",
    "pragmaticjp",
  ];
  function maskUsername(name) {
    if (name.length <= 4) return name[0] + "**" + name[name.length - 1];
    return name.slice(0, 2) + "**" + name.slice(-2);
  }
  function randomJackpot() {
    // 5-7 digit, kelipatan 1.000
    const min = 2000000,
      max = 99999999;
    const n = Math.floor(Math.random() * (max - min + 1)) + min;
    return "IDR " + n.toLocaleString("id-ID");
  }
  function randomNotif() {
    const user = usernames[Math.floor(Math.random() * usernames.length)];
    const masked = maskUsername(user);
    const jackpot = randomJackpot();
    return { user: masked, jackpot };
  }
  function showWithdrawNotif() {
    let notif = document.querySelector(".withdraw-float-notif");
    if (!notif) {
      notif = document.createElement("div");
      notif.className = "withdraw-float-notif";
      notif.innerHTML =
        '<span class="notif-icon"><i class="fas fa-money-bill-wave"></i></span>' +
        '<span class="notif-user"></span> berhasil withdraw <span class="notif-jackpot"></span>';
      document.body.appendChild(notif);
    }
    const { user, jackpot } = randomNotif();
    notif.querySelector(".notif-user").textContent = user;
    notif.querySelector(".notif-jackpot").textContent = jackpot;
    notif.classList.add("show");
    setTimeout(() => notif.classList.remove("show"), 3500);
  }
  // Muncul pertama kali setelah 1.5 detik, lalu random interval 12-20 detik
  setTimeout(function loop() {
    showWithdrawNotif();
    setTimeout(loop, 12000 + Math.random() * 8000);
  }, 1500);
})();

// === PROMO SLIDER OTOMATIS ===
document.addEventListener("DOMContentLoaded", () => {
  const slider = document.querySelector(".promo-slider");
  if (!slider) return;
  
  const track = slider.querySelector(".slider-track");
  const items = slider.querySelectorAll(".slider-item");
  const dotsContainer = slider.querySelector(".slider-dots");
  
  if (!track || !items.length || !dotsContainer) return;
  
  let current = 0;
  let interval = null;
  
  // Setup dots if they don't exist or need updating
  if (dotsContainer.children.length !== items.length) {
    dotsContainer.innerHTML = "";
    items.forEach((_, i) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = i === 0 ? "active" : "";
      btn.setAttribute('data-slide', i);
      btn.addEventListener("click", () => goTo(i));
      dotsContainer.appendChild(btn);
    });
  }
  
  const dots = dotsContainer.querySelectorAll("button");
  
  // Add click handlers to existing dots
  dots.forEach((dot, i) => {
    dot.addEventListener("click", () => goTo(i));
  });
  
  function goTo(idx) {
    if (idx < 0 || idx >= items.length) return;
    current = idx;
    track.style.transform = `translateX(-${idx * 100}%)`;
    dots.forEach((d, i) => d.classList.toggle("active", i === idx));
  }
  
  function next() {
    goTo((current + 1) % items.length);
  }
  
  function prev() {
    goTo((current - 1 + items.length) % items.length);
  }
  
  function startAuto() {
    if (interval) clearInterval(interval);
    if (items.length > 1) {
      interval = setInterval(next, 4000); // Slightly longer for better UX
    }
  }
  
  // Enhanced touch/swipe support for mobile
  let startX = null;
  let startY = null;
  let isDragging = false;
  
  track.addEventListener("touchstart", (e) => {
    startX = e.touches[0].clientX;
    startY = e.touches[0].clientY;
    isDragging = true;
    if (interval) clearInterval(interval);
  }, { passive: true });
  
  track.addEventListener("touchmove", (e) => {
    if (!isDragging || !startX) return;
    
    const currentX = e.touches[0].clientX;
    const currentY = e.touches[0].clientY;
    const diffX = Math.abs(currentX - startX);
    const diffY = Math.abs(currentY - startY);
    
    // Only handle horizontal swipes
    if (diffX > diffY && diffX > 10) {
      e.preventDefault(); // Prevent vertical scrolling
    }
  }, { passive: false });
  
  track.addEventListener("touchend", (e) => {
    if (!isDragging || startX === null) return;
    
    const dx = e.changedTouches[0].clientX - startX;
    const threshold = 50; // Minimum swipe distance
    
    if (Math.abs(dx) > threshold) {
      if (dx > 0) {
        prev(); // Swipe right - go to previous
      } else {
        next(); // Swipe left - go to next
      }
    }
    
    startX = null;
    startY = null;
    isDragging = false;
    startAuto();
  }, { passive: true });
  
  // Mouse drag support for desktop
  let isMouseDown = false;
  let mouseStartX = null;
  
  track.addEventListener("mousedown", (e) => {
    isMouseDown = true;
    mouseStartX = e.clientX;
    track.style.cursor = "grabbing";
    if (interval) clearInterval(interval);
  });
  
  track.addEventListener("mousemove", (e) => {
    if (!isMouseDown || mouseStartX === null) return;
    e.preventDefault();
  });
  
  track.addEventListener("mouseup", (e) => {
    if (!isMouseDown || mouseStartX === null) return;
    
    const dx = e.clientX - mouseStartX;
    const threshold = 50;
    
    if (Math.abs(dx) > threshold) {
      if (dx > 0) {
        prev();
      } else {
        next();
      }
    }
    
    isMouseDown = false;
    mouseStartX = null;
    track.style.cursor = "grab";
    startAuto();
  });
  
  track.addEventListener("mouseleave", () => {
    if (isMouseDown) {
      isMouseDown = false;
      mouseStartX = null;
      track.style.cursor = "grab";
      startAuto();
    }
  });
  
  // Pause on hover
  slider.addEventListener("mouseenter", () => {
    if (interval) clearInterval(interval);
  });
  
  slider.addEventListener("mouseleave", startAuto);
  
  // Initialize
  track.style.cursor = "grab";
  goTo(0);
  startAuto();
  
  // Handle window resize
  let resizeTimeout;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
      goTo(current); // Refresh position after resize
    }, 250);
  });
});

document.addEventListener("DOMContentLoaded", () => {
  var logo = document.querySelector(".main-logo-animated");
  if (logo) {
    setTimeout(() => {
      logo.classList.add("in");
    }, 200);
  }
});

// Add custom CSS for game popup
const gamePopupCSS = `
<style>
.game-popup-custom {
  border-radius: 16px !important;
  border: 2px solid #ff006e !important;
}
.game-popup-title {
  padding: 0 !important;
  margin-bottom: 0 !important;
}
.game-popup-confirm {
  border-radius: 8px !important;
  font-weight: bold !important;
  padding: 10px 20px !important;
  font-size: 1rem !important;
}
.game-popup-cancel {
  border-radius: 8px !important;
  font-weight: bold !important;
  padding: 10px 20px !important;
  font-size: 1rem !important;
}
</style>
`;

// Inject the CSS
if (!document.querySelector("#game-popup-styles")) {
  const styleElement = document.createElement("div");
  styleElement.id = "game-popup-styles";
  styleElement.innerHTML = gamePopupCSS;
  document.head.appendChild(styleElement);
}

// ===== LIVE RTP UPDATE SYSTEM =====
let rtpUpdateInterval = null;

function startLiveRTPUpdates() {
  // Clear existing interval jika ada
  if (rtpUpdateInterval) {
    clearInterval(rtpUpdateInterval);
  }
  
  // Update RTP setiap 25 menit (1500000 ms)
  rtpUpdateInterval = setInterval(updateAllRTP, 1500000);
  
  // Juga update setiap 30 detik untuk demo (uncomment untuk testing)
  // setInterval(updateAllRTP, 30000);
}

function updateAllRTP() {
  const gameCards = document.querySelectorAll('.game-card-v2');
  
  if (gameCards.length === 0) return;
  
  gameCards.forEach((gameCard, index) => {
    // Delay update untuk setiap card agar terlihat natural
    setTimeout(() => {
      updateSingleRTP(gameCard);
    }, index * 200); // 200ms delay antar card
  });
  
  console.log(`Updated RTP for ${gameCards.length} games at ${new Date().toLocaleTimeString()}`);
}

function updateSingleRTP(gameCard) {
  const rtpBarFill = gameCard.querySelector('.rtp-bar-fill');
  const rtpPercentage = gameCard.querySelector('.rtp-percentage');
  
  if (!rtpBarFill || !rtpPercentage) return;
  
  // Generate RTP baru (20% - 98%)
  const newRtp = Math.floor(Math.random() * (98 - 20 + 1)) + 20;
  const rtpLevel = newRtp < 40 ? 'low' : newRtp < 70 ? 'medium' : 'high';
  
  // Animasi transisi yang smooth
  rtpBarFill.style.transition = 'width 2s cubic-bezier(0.4, 0, 0.2, 1)';
  
  // Update nilai dan visual
  setTimeout(() => {
    rtpBarFill.style.width = newRtp + '%';
    rtpBarFill.setAttribute('data-rtp-level', rtpLevel);
    rtpPercentage.textContent = newRtp + '%';
    
    // Tambahkan efek flash saat update
    const rtpSection = gameCard.querySelector('.rtp-progress-section');
    if (rtpSection) {
      rtpSection.style.animation = 'rtp-update-flash 0.5s ease';
      
      setTimeout(() => {
        rtpSection.style.animation = '';
      }, 500);
    }
  }, 100);
}

// Manual RTP update untuk testing (bisa dipanggil dari console)
function manualRTPUpdate() {
  updateAllRTP();
  console.log('Manual RTP update executed!');
}

// Expose functions untuk debugging
window.manualRTPUpdate = manualRTPUpdate;
window.startLiveRTPUpdates = startLiveRTPUpdates;

// CSS untuk flash animation
const rtpAnimationStyle = document.createElement('style');
rtpAnimationStyle.textContent = `
  @keyframes rtp-update-flash {
    0% { border-color: rgba(255, 193, 7, 0.3); }
    50% { 
      border-color: rgba(255, 193, 7, 1);
      box-shadow: 0 0 20px rgba(255, 193, 7, 0.5);
      transform: translateY(-2px) scale(1.02);
    }
    100% { 
      border-color: rgba(255, 193, 7, 0.3);
      transform: translateY(-2px) scale(1);
    }
  }
`;
document.head.appendChild(rtpAnimationStyle);
