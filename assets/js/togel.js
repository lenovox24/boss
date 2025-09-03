document.addEventListener("DOMContentLoaded", function () {
  // Step navigation
  const stepPasaran = document.getElementById("step-pasaran");
  const stepJenis = document.getElementById("step-jenis");
  const stepInput = document.getElementById("step-input");
  let selectedPasaran = "";
  let selectedJenis = "";

  // Step 1: Pilih pasaran
  document.querySelectorAll(".togel-pasaran-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      selectedPasaran = this.dataset.pasaran;
      stepPasaran.classList.add("d-none");
      stepJenis.classList.remove("d-none");
      document
        .querySelectorAll(".togel-pasaran-btn")
        .forEach((b) => b.classList.remove("active"));
      this.classList.add("active");
    });
  });
  // Step 2: Pilih jenis bet
  document.querySelectorAll(".togel-jenis-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      selectedJenis = this.dataset.jenis;
      stepJenis.classList.add("d-none");
      stepInput.classList.remove("d-none");
      document
        .querySelectorAll(".togel-jenis-btn")
        .forEach((b) => b.classList.remove("active"));
      this.classList.add("active");
    });
  });
  // Tombol kembali dari step jenis
  document
    .getElementById("btn-back-jenis")
    .addEventListener("click", function () {
      stepJenis.classList.add("d-none");
      stepPasaran.classList.remove("d-none");
    });
  // Tombol kembali dari step input
  document
    .getElementById("btn-back-input")
    .addEventListener("click", function () {
      stepInput.classList.add("d-none");
      stepJenis.classList.remove("d-none");
    });

  // QuickBet: isi semua input game dengan nominal yang sama
  document
    .getElementById("btn-quickbet")
    .addEventListener("click", function () {
      const val = document
        .querySelector(".input-quickbet")
        .value.replace(/[^\d]/g, "");
      document
        .querySelectorAll(".input-game")
        .forEach((inp) => (inp.value = val));
      updateTotalBet();
    });

  // Copy baris: copy isi baris ke baris berikutnya
  document.querySelectorAll(".btn-copy-row").forEach((btn, idx, arr) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const tr = btn.closest("tr");
      const nextTr = tr.nextElementSibling;
      if (nextTr) {
        const inputs = tr.querySelectorAll("input");
        const nextInputs = nextTr.querySelectorAll("input");
        inputs.forEach((inp, i) => {
          if (nextInputs[i]) nextInputs[i].value = inp.value;
        });
      }
    });
  });

  // Update total bet saat input berubah
  document.querySelectorAll(".input-game").forEach((inp) => {
    inp.addEventListener("input", updateTotalBet);
  });
  // Update total bet dari kolom Bayar
  function updateTotalBet() {
    let total = 0;
    document.querySelectorAll(".input-bayar").forEach((inp) => {
      let v = inp.value.replace(/[^\d]/g, "");
      v = v ? parseInt(v) : 0;
      if (!isNaN(v) && v > 0) total += v;
    });
    document.getElementById("input-total-bet").textContent =
      total.toLocaleString();
  }

  // Update total bet dan kolom potongan/bayar saat input-bet berubah
  document.querySelectorAll(".input-bet").forEach((inp) => {
    inp.addEventListener("input", function () {
      updateTotalBet();
      const tr = inp.closest("tr");
      const potonganInput = tr.querySelector(".input-potongan");
      const bayarInput = tr.querySelector(".input-bayar");
      let bet = parseInt(inp.value.replace(/[^\d]/g, "")) || 0;
      let potongan = Math.round(bet * 0.66);
      let bayar = bet - potongan;
      if (bet > 0) {
        potonganInput.value = potongan.toLocaleString() + " (66%)";
        bayarInput.value = bayar.toLocaleString();
      } else {
        potonganInput.value = "";
        bayarInput.value = "";
      }
    });
  });

  // Update Game kolom otomatis sesuai digit input Nomor
  document.querySelectorAll(".input-nomor").forEach((inp) => {
    inp.addEventListener("input", function () {
      const tr = inp.closest("tr");
      const gameInput = tr.querySelector(".input-game");
      let val = inp.value.replace(/[^\d]/g, "");
      if (val.length === 2) gameInput.value = "2D";
      else if (val.length === 3) gameInput.value = "3D";
      else if (val.length === 4) gameInput.value = "4D";
      else gameInput.value = "";
    });
  });

  // Konfirmasi bet
  document
    .getElementById("btn-konfirmasi")
    .addEventListener("click", function () {
      let saldo =
        typeof window.USER_SALDO !== "undefined"
          ? parseInt(window.USER_SALDO)
          : 0;
      let total = 0;
      document.querySelectorAll(".input-game").forEach((inp) => {
        let v = parseInt(inp.value.replace(/[^\d]/g, ""));
        if (!isNaN(v)) total += v;
      });
      if (saldo === 0) {
        Swal.fire({
          icon: "error",
          title: "Saldo 0",
          text: "Saldo Anda 0, silakan deposit terlebih dahulu.",
          confirmButtonColor: "#ff006e",
          background: "#23272f",
          color: "#fff",
        });
        return;
      }
      if (total === 0) {
        Swal.fire({
          icon: "warning",
          title: "Isi Nominal Bet!",
          text: "Masukkan nominal bet minimal pada satu baris.",
          confirmButtonColor: "#ff006e",
          background: "#23272f",
          color: "#fff",
        });
        return;
      }
      Swal.fire({
        icon: "question",
        title: "Konfirmasi Bet",
        html: `<div>Pasang bet total <b>Rp ${total.toLocaleString()}</b> untuk pasaran <b>${selectedPasaran}</b> jenis <b>${selectedJenis}</b>?</div>`,
        showCancelButton: true,
        confirmButtonText: "Ya, Pasang!",
        cancelButtonText: "Batal",
        confirmButtonColor: "#ff006e",
        background: "#23272f",
        color: "#fff",
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            icon: "success",
            title: "Bet Berhasil!",
            text: "Bet Anda sudah tercatat.",
            confirmButtonColor: "#ff006e",
            background: "#23272f",
            color: "#fff",
          });
        }
      });
    });
});
