<?php
// File: api_get_providers.php (REVISI FINAL: Memastikan SEMUA provider aktif tampil)

header('Content-Type: application/json');
require_once 'includes/db_connect.php';

// Ambil parameter kategori dari request JavaScript
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

$sql = "";
$stmt = null;

$special_external_categories = ['casino', 'sports', 'arcade', 'sabungayam', 'crashgame', 'crash-game'];
if (in_array(strtolower($category), $special_external_categories)) {
    // Tampilkan provider yang punya external_url dan kategori sesuai
    $sql = "SELECT p.nama_provider, p.logo_provider, p.sort_order, p.external_url
            FROM providers p
            WHERE p.external_url IS NOT NULL AND p.external_url != '' AND LOWER(p.kategori) = ?
            ORDER BY p.sort_order ASC, p.id ASC";
    $stmt = $conn->prepare($sql);
    $cat_lower = strtolower($category);
    $stmt->bind_param("s", $cat_lower);
} else if ($category === 'all' || empty($category)) {
    $sql = "SELECT p.nama_provider, p.logo_provider, p.sort_order
            FROM providers p
            ORDER BY p.sort_order ASC, p.id ASC";
    $stmt = $conn->prepare($sql);
} else {
    // Untuk kategori lain, tetap tampilkan provider meski belum ada game aktif (tanpa syarat join ke games), filter kategori
    $sql = "SELECT p.nama_provider, p.logo_provider, p.sort_order
            FROM providers p
            WHERE LOWER(p.kategori) = ?
            ORDER BY p.sort_order ASC, p.id ASC";
    $stmt = $conn->prepare($sql);
    $cat_lower = strtolower($category);
    $stmt->bind_param("s", $cat_lower);
}

$stmt->execute();
$result = $stmt->get_result();

$providers = [];
// Ganti base_url agar selalu benar ke root project
$base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
if (substr($base_url, -1) !== '/') $base_url .= '/';
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Ambil URL logo dengan path yang benar (absolut dari root)
        $row['logo_provider_url'] = '/assets/images/providers/' . htmlspecialchars($row['logo_provider']);
        if (isset($row['external_url'])) {
            $row['external_url'] = $row['external_url'];
        }
        $providers[] = $row;
    }
}

$stmt->close();
$conn->close();

// Kembalikan data dalam format JSON
echo json_encode($providers);
