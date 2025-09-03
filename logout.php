<?php
// File: hokiraja/logout.php (REVISI FINAL: Logout Bersih)

session_start();

// Hapus semua variabel session
$_SESSION = array(); // Menghapus data dari array $_SESSION

// Jika session diatur menggunakan cookie, hapus juga cookie session.
// Ini akan menghancurkan session, dan bukan hanya data session.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman utama (index.php) setelah logout
header("Location: index");
exit();
