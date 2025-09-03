<?php
// File: admin/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Penjaga Halaman
if (!isset($_SESSION['admin_id'])) {
    header("Location: index");
    exit();
}

// Memanggil koneksi dan pengaturan dengan path absolut yang aman
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/site_config.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?> - <?php echo htmlspecialchars($settings['site_title'] ?? 'HOKIRAJA'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- CSS khusus admin (dalam folder admin) -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/admin_style.css">
    <!-- Favicon jika ada -->
    <link rel="icon" href="<?php echo $base_url; ?>assets/images/favicon.ico" type="image/x-icon">
</head>

<body>
    <div class="d-flex" id="wrapper">