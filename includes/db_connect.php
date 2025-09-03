<?php
// File: includes/db_connect.php

// Konfigurasi Database
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Password sudah diisi sesuai permintaan user
$db_name = 'hokiraja';

// Membuat koneksi  
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    // Tambahkan logging untuk debugging
    error_log("Database connection failed: " . $conn->connect_error);

    // Tampilkan error yang lebih informatif
    if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        // Development environment - tampilkan error detail
        die("Koneksi ke database gagal: " . $conn->connect_error);
    } else {
        // Production environment - tampilkan error umum
        die("Maaf, terjadi kesalahan sistem. Silakan coba lagi nanti.");
    }
}

// Set karakter set
$conn->set_charset("utf8mb4");

// Test query untuk memastikan database berfungsi
try {
    $test_query = $conn->query("SELECT 1");
    if (!$test_query) {
        throw new Exception("Database test query failed");
    }
} catch (Exception $e) {
    error_log("Database test failed: " . $e->getMessage());
    if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        die("Database test failed: " . $e->getMessage());
    } else {
        die("Maaf, terjadi kesalahan sistem. Silakan coba lagi nanti.");
    }
}
