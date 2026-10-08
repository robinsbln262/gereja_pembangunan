<?php
function rupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}

function uploadFile($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'])
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $namaFile = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
    $ext = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedTypes)) {
        return false;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $targetPath = rtrim($targetDir, '/') . '/' . $namaFile;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $namaFile;
    }

    return false;
}

function getProgresPersen($conn)
{
    $result = mysqli_query($conn, "SELECT AVG(progres_persen) as avg FROM pembangunan");
    $row = mysqli_fetch_assoc($result);
    return round($row['avg'] ?? 0);
}

function getTotalDonasi($conn)
{
    $result = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM donasi WHERE status='verified'");
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

// ========== PERBAIKAN UTAMA ==========
function getRingkasanKeuangan($conn)
{
    // Total donasi terverifikasi (pemasukan)
    $queryDonasi = mysqli_query($conn, "SELECT SUM(jumlah) as total_masuk FROM donasi WHERE status='verified'");
    $totalMasuk = $queryDonasi ? (mysqli_fetch_assoc($queryDonasi)['total_masuk'] ?? 0) : 0;

    // Total kebutuhan dana (hanya info, tidak digunakan untuk saldo)
    $queryKebutuhan = mysqli_query($conn, "SELECT SUM(kebutuhan_dana) as total_kebutuhan FROM pembangunan");
    $totalKebutuhan = $queryKebutuhan ? (mysqli_fetch_assoc($queryKebutuhan)['total_kebutuhan'] ?? 0) : 0;

    // Total dana terpakai dari tabel PENGELUARAN (sumber utama)
    $queryPengeluaran = mysqli_query($conn, "SELECT SUM(jumlah) as total_terpakai FROM pengeluaran");
    $totalTerpakai = $queryPengeluaran ? (mysqli_fetch_assoc($queryPengeluaran)['total_terpakai'] ?? 0) : 0;

    $sisaDana = $totalMasuk - $totalTerpakai;

    return [
        'total_masuk' => $totalMasuk,
        'total_kebutuhan' => $totalKebutuhan,
        'total_terpakai' => $totalTerpakai,
        'sisa_dana' => $sisaDana
    ];
}
?>