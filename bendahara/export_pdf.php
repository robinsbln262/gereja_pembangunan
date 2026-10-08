<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/functions.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$filter_date = $tahun . '-' . sprintf("%02d", $bulan);

// ========== DATA PEMASUKAN (donasi terverifikasi) ==========
$sql_pemasukan = "SELECT id_donasi as id, created_at as tanggal, nama_donatur as sumber, jumlah, metode_pembayaran as tipe 
                  FROM donasi 
                  WHERE status='verified' AND DATE_FORMAT(created_at, '%Y-%m') = '$filter_date' 
                  ORDER BY created_at DESC";
$pemasukan = mysqli_query($conn, $sql_pemasukan);
if (!$pemasukan) die("Error query pemasukan: " . mysqli_error($conn));

// ========== DATA PENGELUARAN (JOIN dengan pembangunan untuk nama kegiatan) ==========
$sql_pengeluaran = "SELECT p.*, pb.judul as nama_kegiatan 
                    FROM pengeluaran p
                    LEFT JOIN pembangunan pb ON p.id_pembangunan = pb.id_pembangunan
                    WHERE DATE_FORMAT(p.tanggal, '%Y-%m') = '$filter_date' 
                    ORDER BY p.tanggal DESC";
$pengeluaran = mysqli_query($conn, $sql_pengeluaran);
if (!$pengeluaran) die("Error query pengeluaran: " . mysqli_error($conn));

// Hitung total pemasukan & pengeluaran (untuk ringkasan)
$total_masuk = 0;
while ($row = mysqli_fetch_assoc($pemasukan)) $total_masuk += $row['jumlah'];
mysqli_data_seek($pemasukan, 0);

$total_keluar = 0;
while ($row = mysqli_fetch_assoc($pengeluaran)) $total_keluar += $row['jumlah'];
mysqli_data_seek($pengeluaran, 0);

$saldo = $total_masuk - $total_keluar;
$bulan_indo = date('F Y', strtotime($filter_date . '-01'));

// ========== LOGO BASE64 ==========
$logo_base64 = '';
$logo_path = __DIR__ . '/../uploads/informasi/st-laurensius.png';
if (file_exists($logo_path)) {
    $image_data = file_get_contents($logo_path);
    $logo_base64 = 'data:image/png;base64,' . base64_encode($image_data);
}

// ========== HTML UNTUK PDF (semua teks hitam) ==========
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <style>
        @page { margin: 1.5cm 1.2cm; footer: html_footer; }
        body { font-family: "Times New Roman", Times, serif; font-size: 11pt; line-height: 1.4; color: black; }
        .header { text-align: center; border-bottom: 2px solid black; margin-bottom: 20px; padding-bottom: 10px; position: relative; }
        .logo { position: absolute; left: 0; top: 0; width: 60px; height: 60px; border-radius: 50%; object-fit: cover; }
        .title { font-size: 18pt; font-weight: bold; color: black; margin: 0; }
        .subtitle { font-size: 12pt; font-style: italic; color: black; margin: 5px 0 0; }
        .address { font-size: 9pt; font-style: normal; color: black; margin-top: 4px; }
        .periode { font-size: 12pt; text-align: center; margin: 10px 0 20px; font-weight: bold; color: black; }
        h3 { font-size: 14pt; font-weight: bold; margin: 20px 0 10px; background-color: #eef2f7; padding: 5px 10px; border-left: 4px solid black; color: black; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10pt; }
        th, td { border: 1px solid black; padding: 6px 8px; vertical-align: top; color: black; }
        th { background-color: #d9e1f2; font-weight: bold; text-align: center; }
        .text-right { text-align: right; }
        .summary-table th, .summary-table td { text-align: center; background-color: #f8f9fc; font-weight: bold; color: black; }
        .summary-table td { background-color: #fff; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9pt; border-top: 1px solid black; padding-top: 5px; color: black; }
        .page-break { page-break-before: always; break-before: page; }
        .stamp { margin-top: 30px; text-align: right; font-size: 10pt; border-top: 1px dashed black; padding-top: 15px; color: black; }
    </style>
</head>
<body>

<div class="header">
    ' . ($logo_base64 ? '<img src="' . $logo_base64 . '" class="logo" alt="Logo Gereja St. Laurensius">' : '') . '
    <div class="title">LAPORAN KEUANGAN PEMBANGUNAN</div>
    <div class="subtitle">Gereja Katolik Stasi St. Laurensius Simpang Selayang</div>
    <div class="address">Jl. Jamin Ginting No.20, Kemenangan Tani, Kec. Medan Tuntungan, Kota Medan, Sumatera Utara 20135</div>
</div>
<div class="periode">Periode: ' . $bulan_indo . '</div>

<h3>Ringkasan Keuangan</h3>
<table class="summary-table">
    <thead><tr><th>Total Pemasukan</th><th>Total Pengeluaran</th><th>Saldo</th></tr></thead>
    <tbody><tr><td class="text-right"><strong>' . rupiah($total_masuk) . '</strong></td><td class="text-right"><strong>' . rupiah($total_keluar) . '</strong></td><td class="text-right"><strong>' . rupiah($saldo) . '</strong></td></tr></tbody>
</table>

<h3>Detail Pemasukan</h3>
<table>
    <thead><tr><th>Tanggal</th><th>Donatur</th><th>Metode</th><th>Jumlah</th></tr></thead>
    <tbody>';

if (mysqli_num_rows($pemasukan) > 0) {
    mysqli_data_seek($pemasukan, 0);
    while ($d = mysqli_fetch_assoc($pemasukan)) {
        $html .= '<tr>
            <td>' . date('d/m/Y H:i', strtotime($d['tanggal'])) . '</td>
            <td>' . htmlspecialchars($d['sumber']) . '</td>
            <td>' . ucfirst($d['tipe']) . '</td>
            <td class="text-right">' . rupiah($d['jumlah']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="4" style="text-align:center;">Tidak ada data pemasukan pada periode ini.ERB</td>';
}
$html .= '</tbody>
</table>

<div class="page-break"></div>

<h3>Detail Pengeluaran</h3>
<table>
    <thead><tr><th>Tanggal</th><th>Deskripsi</th><th>Kategori</th><th>Kegiatan</th><th>Jumlah</th></tr></thead>
    <tbody>';

if (mysqli_num_rows($pengeluaran) > 0) {
    mysqli_data_seek($pengeluaran, 0);
    while ($k = mysqli_fetch_assoc($pengeluaran)) {
        $html .= '<tr>
            <td>' . date('d/m/Y', strtotime($k['tanggal'])) . '</td>
            <td>' . htmlspecialchars($k['deskripsi']) . '</td>
            <td>' . htmlspecialchars($k['kategori']) . '</td>
            <td>' . htmlspecialchars($k['nama_kegiatan'] ?? '-') . '</td>
            <td class="text-right">' . rupiah($k['jumlah']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align:center;">Tidak ada data pengeluaran pada periode ini.ERB</td>';
}
$html .= '</tbody>
</table>

<div class="stamp">
    Medan, ' . date('d F Y') . '<br>
    Panitia Pembangunan<br><br><br>
    (_________________________)
</div>

<htmlpagefooter name="html_footer">
    <div class="footer">
        Laporan Keuangan - Sistem Informasi Pembangunan Gereja | Halaman {PAGE_NUM} dari {PAGE_COUNT} | Dicetak: ' . date('d/m/Y H:i:s') . '
    </div>
</htmlpagefooter>

</body>
</html>';

// ========== GENERATE PDF ==========
$options = new Options();
$options->set('defaultFont', 'Times New Roman');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("laporan_keuangan_$filter_date.pdf", array("Attachment" => false));
exit();
?>