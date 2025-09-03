<?php
// File: includes/site_config.php

if (!isset($conn) || !$conn) {
    // __DIR__ adalah path absolut ke folder saat ini (includes), jadi lebih aman
    require_once __DIR__ . '/db_connect.php';
}

$settings_result = $conn->query("SELECT setting_name, setting_value FROM site_settings");
$settings = [];
if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
}

// BASE URL OTOMATIS
$base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
if (substr($base_url, -1) !== '/') $base_url .= '/';
