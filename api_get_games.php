<?php
// File: hokiraja/api_get_games.php (REVISI PROFESIONAL: Memastikan Filter Kategori Bekerja)
header('Content-Type: application/json');
require_once 'includes/db_connect.php';

// Ambil parameter filter dari request JavaScript
$category = isset($_GET['category']) && $_GET['category'] !== 'all' ? $_GET['category'] : null;
$provider = isset($_GET['provider']) && $_GET['provider'] !== 'all' ? $_GET['provider'] : null;
$search = isset($_GET['search']) && !empty($_GET['search']) ? $_GET['search'] : null;

// Bangun query SQL secara dinamis berdasarkan filter
$sql = "SELECT nama_game, provider, gambar_thumbnail, game_url FROM games WHERE is_active = 1";
$params = [];
$types = "";

// Tambahkan filter kategori jika ada
if ($category) {
    $sql .= " AND kategori = ?";
    $params[] = $category;
    $types .= "s";
}
// Tambahkan filter provider jika ada
if ($provider) {
    $sql .= " AND provider = ?";
    $params[] = $provider;
    $types .= "s";
}
// Tambahkan filter pencarian jika ada
if ($search) {
    $sql .= " AND nama_game LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0 ? (int)$_GET['per_page'] : 200;
$offset = ($page - 1) * $per_page;

// Hitung total data
$count_sql = "SELECT COUNT(*) FROM games WHERE is_active = 1";
if ($category) $count_sql .= " AND kategori = '" . $conn->real_escape_string($category) . "'";
if ($provider) $count_sql .= " AND provider = '" . $conn->real_escape_string($provider) . "'";
if ($search) $count_sql .= " AND nama_game LIKE '%" . $conn->real_escape_string($search) . "%'";
$total_games = $conn->query($count_sql)->fetch_row()[0];

$sql .= " ORDER BY id ASC LIMIT $per_page OFFSET $offset"; // Batasi hasil untuk performa

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    // Gunakan splat operator (...) untuk unpacking array $params
    // Pastikan urutan parameter sesuai dengan urutan '?' di $sql
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$games = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Deteksi apakah gambar_thumbnail adalah URL eksternal atau file lokal
        $gambar_thumbnail = $row['gambar_thumbnail'];
        if (filter_var($gambar_thumbnail, FILTER_VALIDATE_URL)) {
            // Jika URL eksternal, gunakan langsung
            $row['gambar_thumbnail_url'] = $gambar_thumbnail;
        } else {
            // Jika file lokal, tambahkan path folder
            $row['gambar_thumbnail_url'] = 'assets/images/games/' . htmlspecialchars($gambar_thumbnail);
        }
        $games[] = $row;
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    'games' => $games,
    'total_games' => $total_games
]);
