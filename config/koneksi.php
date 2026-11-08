<?php
$host   = 'localhost';
$user   = 'root';
$pass   = '';
$db     = 'db_sppd_bakesbangpol';

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}
