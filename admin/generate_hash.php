<?php
// File: generate_hash.php
// Letakkan file ini di dalam folder admin Anda, misalnya: hokiraja/admin/generate_hash.php

// --- UBAH PASSWORD DI SINI ---
$password_yang_mau_dihash = 'admin';
// ------------------------------


// Proses hashing menggunakan algoritma standar dan aman dari PHP
$hash_yang_dihasilkan = password_hash($password_yang_mau_dihash, PASSWORD_DEFAULT);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Password Hash Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            max-width: 800px;
            width: 100%;
        }

        textarea {
            font-family: monospace;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="card shadow-sm">
        <div class="card-header">
            <h3>Password Hash Generator</h3>
        </div>
        <div class="card-body p-4">
            <p>Gunakan skrip ini untuk membuat hash password yang aman untuk database Anda.</p>
            <hr>
            <div class="mb-3">
                <label class="form-label">Password Asli:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($password_yang_mau_dihash); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="hashedPassword" class="form-label"><strong>Hash yang Dihasilkan (Aman):</strong></label>
                <textarea class="form-control" id="hashedPassword" rows="3" readonly onclick="this.select();"><?php echo htmlspecialchars($hash_yang_dihasilkan); ?></textarea>
                <div class="form-text">Klik pada kotak di atas untuk memilih semua teks, lalu salin (Ctrl+C).</div>
            </div>
            <div class="alert alert-warning mt-4" role="alert">
                <strong>PENTING:</strong> Setelah Anda menyalin hash dan memperbarui database, segera hapus file <strong>generate_hash.php</strong> ini dari server Anda untuk menjaga keamanan.
            </div>
        </div>
    </div>
</body>

</html>