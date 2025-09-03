<?php
// File: includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mendapatkan nama file halaman saat ini
$current_page = basename($_SERVER['PHP_SELF']);

// ========================================================
// === LOGIKA PENJAGA AKSES HALAMAN UNTUK PENGGUNA ===
// ========================================================

// Daftar halaman yang membutuhkan user login
$pages_requiring_login = [
    'beranda',
    'profil',
    'deposit',
    'withdraw',
    'rekening',
    'transaksi',
    'memo',
    'referral',
    'bantuan',
];

// Daftar halaman yang TIDAK BOLEH diakses setelah user login (hanya untuk guest)
$pages_for_logged_out_only = [
    'index',
    'login',
    'daftar',
];

// Logika redirect
if (isset($_SESSION['user_id'])) {
    // Validasi user_id di database
    require_once __DIR__ . '/db_connect.php';
    $check_user_stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check_user_stmt->bind_param("i", $_SESSION['user_id']);
    $check_user_stmt->execute();
    $check_user_stmt->store_result();
    if ($check_user_stmt->num_rows === 0) {
        // User tidak ditemukan, hapus session dan redirect ke index.php
        session_unset();
        session_destroy();
        header("Location: index");
        exit();
    }
    $check_user_stmt->close();
    // User SUDAH login
    if (in_array(str_replace('.php', '', $current_page), $pages_for_logged_out_only)) {
        header("Location: beranda"); // Redirect ke beranda jika mencoba akses halaman khusus logout
        exit();
    }
} else {
    // User BELUM login
    if (in_array(str_replace('.php', '', $current_page), $pages_requiring_login)) {
        header("Location: login"); // Redirect ke login jika mencoba akses halaman yang butuh login
        exit();
    }
}

// Memanggil koneksi database
require_once __DIR__ . '/db_connect.php';
// Memanggil pengaturan situs (untuk $settings array)
require_once __DIR__ . '/site_config.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1a1611">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- SEO Meta Tags -->
    <title><?php 
        $page_title = 'BOSSCUAN69';
        $meta_description = 'BOSSCUAN69 adalah situs slot online terpercaya #1 di Indonesia dengan RTP tinggi, slot gacor maxwin, dan layanan 24/7. Daftar sekarang dan raih jackpot!';
        
        if ($current_page == 'index.php') {
            $page_title = 'BOSSCUAN69 - Situs Slot Online Terpercaya #1 Indonesia | RTP 98%';
            $meta_description = 'Situs slot online terpercaya BOSSCUAN69 dengan RTP 98%, slot gacor maxwin, pragmatic play, pg soft. Bonus new member 100%, deposit 10rb. Daftar sekarang!';
        } elseif ($current_page == 'daftar.php') {
            $page_title = 'Daftar BOSSCUAN69 - Slot Online Gacor Maxwin | Bonus 100%';
            $meta_description = 'Daftar akun BOSSCUAN69 gratis! Bonus new member 100%, minimal deposit 10rb, slot gacor maxwin. Proses cepat, aman terpercaya.';
        } elseif ($current_page == 'login.php') {
            $page_title = 'Login BOSSCUAN69 - Akses Slot Gacor | Masuk Member';
            $meta_description = 'Login member BOSSCUAN69 untuk akses slot gacor, live casino, togel online. Layanan 24/7, withdraw tanpa pending.';
        } elseif ($current_page == 'promo.php') {
            $page_title = 'Promo BOSSCUAN69 - Bonus Slot Online Terbesar | Event Jackpot';
            $meta_description = 'Promo bonus BOSSCUAN69 terbesar! Bonus deposit harian, cashback mingguan, event jackpot. Syarat mudah, withdraw cepat.';
        } elseif ($current_page == 'togel.php') {
            $page_title = 'Togel Online BOSSCUAN69 - Hasil Keluaran Terlengkap | Live Draw';
            $meta_description = 'Togel online BOSSCUAN69 dengan hasil keluaran terlengkap. Live draw hongkong, singapore, sidney. Diskon terbesar, hadiah terbesar.';
        }
        echo htmlspecialchars($page_title);
    ?></title>
    
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="BOSSCUAN69, slot online, slot gacor, situs slot terpercaya, slot maxwin, slot online indonesia, judi online, casino online, slot88, pragmatic play, pg soft, rtp slot, bonus slot, daftar slot, togel online">
    <meta name="author" content="BOSSCUAN69">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="language" content="Indonesian">
    <meta name="geo.region" content="ID">
    <meta name="geo.country" content="Indonesia">
    <meta name="revisit-after" content="1 day">
    <meta name="rating" content="general">
    <meta name="distribution" content="global">
    <meta name="target" content="all">
    <link rel="canonical" href="https://cuanss.web.id<?php echo $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Google Site Verification -->
    <meta name="google-site-verification" content="VpL75gpVsOHffFvJ9KsOOn5P3b6E_0Au3r2BI2pRLls" />
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://cuanss.web.id<?php echo $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="https://cuanss.web.id/assets/images/og-image.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="BOSSCUAN69">
    <meta property="og:locale" content="id_ID">
    <meta property="article:author" content="BOSSCUAN69">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@bosscuan69">
    <meta name="twitter:creator" content="@bosscuan69">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="twitter:image" content="https://cuanss.web.id/assets/images/og-image.jpg">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://cuanss.web.id<?php echo $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $base_url; ?>assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="<?php echo $base_url; ?>assets/images/apple-touch-icon.png">
    
    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    
    <!-- Structured Data JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "BOSSCUAN69",
        "alternateName": "BOSSCUAN69 Slot Online",
        "url": "https://cuanss.web.id",
        "description": "Situs slot online terpercaya #1 di Indonesia dengan RTP tinggi, slot gacor maxwin, dan layanan 24/7",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://cuanss.web.id/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "BOSSCUAN69",
        "alternateName": "Boss Cuan 69",
        "url": "https://cuanss.web.id",
        "logo": "https://cuanss.web.id/assets/images/logo.png",
        "image": "https://cuanss.web.id/assets/images/og-image.jpg",
        "description": "Situs slot online terpercaya #1 di Indonesia dengan RTP tinggi, slot gacor maxwin, dan layanan 24/7",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "ID",
            "addressRegion": "Indonesia"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+62-812-3456-7890",
            "contactType": "customer service",
            "availableLanguage": ["Indonesian", "English"],
            "areaServed": "ID",
            "hoursAvailable": "24/7"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "2547",
            "bestRating": "5",
            "worstRating": "1"
        },
        "foundingDate": "2023",
        "sameAs": [
            "https://www.facebook.com/bosscuan69",
            "https://www.instagram.com/bosscuan69",
            "https://t.me/bosscuan69"
        ]
    }
    </script>
    
    <?php if ($current_page == 'index.php'): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "BOSSCUAN69",
        "url": "https://cuanss.web.id",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://cuanss.web.id/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <?php endif; ?>
    
    <?php if ($current_page !== 'index.php'): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": "https://cuanss.web.id/"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "<?php 
                    switch($current_page) {
                        case 'daftar.php': echo 'Daftar'; break;
                        case 'login.php': echo 'Login'; break;
                        case 'promo.php': echo 'Promo'; break;
                        case 'togel.php': echo 'Togel'; break;
                        case 'bantuan.php': echo 'Bantuan'; break;
                        case 'livechat.php': echo 'Live Chat'; break;
                        default: echo 'Page';
                    }
                ?>",
                "item": "https://cuanss.web.id<?php echo $_SERVER['REQUEST_URI']; ?>"
            }
        ]
    }
    </script>
    <?php endif; ?>
    
    <?php if ($current_page == 'index.php' || $current_page == 'beranda.php'): ?>
        <div class="floating-social-menu">
            <div class="social-icons-container">
                <a href="https://www.facebook.com/profile.php?id=61578159999579" class="social-icon" style="--i:1;" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/cuanss69?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" class="social-icon" style="--i:2;" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                <a href="livechat.php" class="social-icon" style="--i:4;"><i class="fas fa-comments"></i></a>
            </div>
            <button class="menu-toggle-button">
                <i class="fas fa-share-alt"></i>
            </button>
        </div>
    <?php endif; ?>
</head>

<body class="dark-theme">
    <?php
    // ========================================================
    // === PUSAT KONTROL NOTIFIKASI (SWEETALERT) ===
    // ========================================================

    // Notifikasi untuk LOGIN BERHASIL
    if (isset($_SESSION['login_success'])) {
        $username = $_SESSION['login_username'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Login Berhasil!',
                    html: 'Selamat datang kembali, <strong style=\"color: #ff006e;\">" . htmlspecialchars($username) . "</strong>!',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false,
                    background: '#212529',
                    color: '#fff'
                });
            });
        </script>";
        unset($_SESSION['login_success']);
        unset($_SESSION['login_username']);
    }

    // Notifikasi untuk PENDAFTARAN BERHASIL
    if (isset($_SESSION['registration_success'])) {
        $username = $_SESSION['username'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Pendaftaran Berhasil!',
                    html: 'Selamat datang, <strong style=\"color: #ff006e;\">" . htmlspecialchars($username) . "</strong>! Akun Anda telah dibuat.',
                    icon: 'success',
                    timer: 4000,
                    showConfirmButton: false,
                    background: '#212529',
                    color: '#fff'
                });
            });
        </script>";
        unset($_SESSION['registration_success']);
    }
    if (isset($_SESSION['pending_deposit_alert'])) {
        $message = $_SESSION['pending_deposit_alert'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Akses Dibatasi',
                    text: '" . htmlspecialchars($message) . "',
                    icon: 'warning',
                    background: '#212529',
                    color: '#fff',
                    confirmButtonColor: '#ff006e'
                });
            });
        </script>";
        unset($_SESSION['pending_deposit_alert']);
    }

    // === BARU: Notifikasi setelah BERHASIL mengirim permintaan deposit ===
    if (isset($_SESSION['deposit_submitted_alert'])) {
        $message = $_SESSION['deposit_submitted_alert'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Permintaan Terkirim!',
                    text: '" . htmlspecialchars($message) . "',
                    icon: 'success',
                    timer: 4000,
                    showConfirmButton: false,
                    background: '#212529',
                    color: '#fff'
                });
            });
        </script>";
        unset($_SESSION['deposit_submitted_alert']);
    }

    // === BARU: Notifikasi setelah BERHASIL mengirim permintaan WITHDRAW ===
    if (isset($_SESSION['withdraw_submitted_alert'])) {
        $message = $_SESSION['withdraw_submitted_alert'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Permintaan Terkirim!',
                    text: '" . htmlspecialchars($message) . "',
                    icon: 'success',
                    timer: 4000,
                    showConfirmButton: false,
                    background: '#212529',
                    color: '#fff'
                });
            });
        </script>";
        unset($_SESSION['withdraw_submitted_alert']);
    }

    // === BARU: PENJAGA HALAMAN WITHDRAW ===
    // Cek hanya jika pengguna mencoba mengakses halaman withdraw.php
    if ($current_page == 'withdraw.php' && isset($_SESSION['user_id'])) {
        $check_pending_wd_stmt = $conn->prepare("SELECT id FROM transactions WHERE user_id = ? AND type = 'withdraw' AND status = 'pending'");
        $check_pending_wd_stmt->bind_param("i", $_SESSION['user_id']);
        $check_pending_wd_stmt->execute();
        $has_pending_wd = $check_pending_wd_stmt->get_result()->num_rows > 0;
        $check_pending_wd_stmt->close();

        if ($has_pending_wd) {
            // Atur notifikasi dan redirect ke beranda
            $_SESSION['pending_withdraw_alert'] = "Anda masih memiliki transaksi penarikan yang sedang diproses. Mohon tunggu hingga selesai sebelum membuat permintaan baru.";
            header("Location: beranda.php");
            exit();
        }
    }

    // Notifikasi untuk PENDING WITHDRAW (ditampilkan di beranda.php)
    if (isset($_SESSION['pending_withdraw_alert'])) {
        $message = $_SESSION['pending_withdraw_alert'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Akses Dibatasi',
                    text: '" . htmlspecialchars($message) . "',
                    icon: 'warning',
                    background: '#212529',
                    color: '#fff',
                    confirmButtonColor: '#ff006e'
                });
            });
        </script>";
        unset($_SESSION['pending_withdraw_alert']);
    }
    ?>
    <?php

    // Memanggil navigasi (header) berdasarkan status login
    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/nav-user.php'; // Header untuk user yang login
        require_once __DIR__ . '/sidebar-user.php'; // Sidebar Offcanvas untuk user yang login
    } else {
        require_once __DIR__ . '/nav-guest.php'; // Header untuk guest
    }
    // === BARU: Notifikasi untuk Halaman Profil ===
    if (isset($_SESSION['profil_success'])) {
        $message = $_SESSION['profil_success'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Berhasil!', text: '" . htmlspecialchars($message) . "', icon: 'success',
                    background: '#212529', color: '#fff', confirmButtonColor: '#ff006e'
                });
            });
        </script>";
        unset($_SESSION['profil_success']);
    }
    if (isset($_SESSION['profil_error'])) {
        $message = $_SESSION['profil_error'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Gagal!', text: '" . htmlspecialchars($message) . "', icon: 'error',
                    background: '#212529', color: '#fff', confirmButtonColor: '#ff006e'
                });
            });
        </script>";
        unset($_SESSION['profil_error']);
    }
    ?>
