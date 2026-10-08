<?php
include '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['judul'])) {
    echo json_encode([]);
    exit();
}

$judul = mysqli_real_escape_string($conn, $_GET['judul']);
$query = mysqli_query($conn, "SELECT id_galeri, gambar, keterangan, urutan FROM galeri WHERE judul='$judul' ORDER BY urutan ASC, id_galeri ASC");
$data = [];
while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;
}
echo json_encode($data);
exit();
?>