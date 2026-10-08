<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'db_gereja_pembangunan';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>