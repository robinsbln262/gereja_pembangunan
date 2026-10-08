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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$filter_date = $tahun . '-' . sprintf("%02d", $bulan);
$bulan_indo = date('F Y', strtotime($filter_date . '-01'));

// ========== DATA PEMASUKAN ==========
$sql_pemasukan = "SELECT id_donasi as id, created_at as tanggal, nama_donatur as sumber, jumlah, metode_pembayaran as tipe 
                  FROM donasi 
                  WHERE status='verified' AND DATE_FORMAT(created_at, '%Y-%m') = '$filter_date' 
                  ORDER BY created_at DESC";
$pemasukan = mysqli_query($conn, $sql_pemasukan);
if (!$pemasukan) die("Error query pemasukan: " . mysqli_error($conn));

// ========== DATA PENGELUARAN (dengan join ke pembangunan) ==========
$sql_pengeluaran = "SELECT p.*, pb.judul as nama_kegiatan 
                    FROM pengeluaran p
                    LEFT JOIN pembangunan pb ON p.id_pembangunan = pb.id_pembangunan
                    WHERE DATE_FORMAT(p.tanggal, '%Y-%m') = '$filter_date' 
                    ORDER BY p.tanggal DESC";
$pengeluaran = mysqli_query($conn, $sql_pengeluaran);
if (!$pengeluaran) die("Error query pengeluaran: " . mysqli_error($conn));

// Hitung total untuk ringkasan
$total_masuk = 0;
while ($row = mysqli_fetch_assoc($pemasukan)) $total_masuk += $row['jumlah'];
mysqli_data_seek($pemasukan, 0);

$total_keluar = 0;
while ($row = mysqli_fetch_assoc($pengeluaran)) $total_keluar += $row['jumlah'];
mysqli_data_seek($pengeluaran, 0);

$saldo = $total_masuk - $total_keluar;

// ========== BUAT SPREADSHEET ==========
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Keuangan');

// Style definitions
$headerStyle = [
    'font' => ['name' => 'Times New Roman', 'size' => 18, 'bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
];
$subtitleStyle = [
    'font' => ['name' => 'Times New Roman', 'size' => 12, 'italic' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
];
$addressStyle = [
    'font' => ['name' => 'Times New Roman', 'size' => 9],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
];
$periodeStyle = [
    'font' => ['name' => 'Times New Roman', 'size' => 12, 'bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
];
$sectionHeaderStyle = [
    'font' => ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2F7']]
];
$tableHeaderStyle = [
    'font' => ['name' => 'Times New Roman', 'size' => 10, 'bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$tableCellStyle = [
    'font' => ['name' => 'Times New Roman', 'size' => 10],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

// Header utama
$sheet->setCellValue('A1', 'LAPORAN KEUANGAN PEMBANGUNAN');
$sheet->mergeCells('A1:F1');
$sheet->getStyle('A1')->applyFromArray($headerStyle);

$sheet->setCellValue('A2', 'Gereja Katolik Stasi St. Laurensius Simpang Selayang');
$sheet->mergeCells('A2:F2');
$sheet->getStyle('A2')->applyFromArray($subtitleStyle);

$sheet->setCellValue('A3', 'Jl. Jamin Ginting No.20, Kemenangan Tani, Kec. Medan Tuntungan, Kota Medan, Sumatera Utara 20135');
$sheet->mergeCells('A3:F3');
$sheet->getStyle('A3')->applyFromArray($addressStyle);

$sheet->setCellValue('A4', 'Periode: ' . $bulan_indo);
$sheet->mergeCells('A4:F4');
$sheet->getStyle('A4')->applyFromArray($periodeStyle);

$sheet->getRowDimension(5)->setRowHeight(5);

// Ringkasan Keuangan
$sheet->setCellValue('A6', 'Ringkasan Keuangan');
$sheet->mergeCells('A6:D6');
$sheet->getStyle('A6')->applyFromArray($sectionHeaderStyle);

$sheet->setCellValue('A7', 'Total Pemasukan');
$sheet->setCellValue('B7', 'Total Pengeluaran');
$sheet->setCellValue('C7', 'Saldo');
$sheet->getStyle('A7:C7')->applyFromArray($tableHeaderStyle);
$sheet->setCellValue('A8', rupiah($total_masuk));
$sheet->setCellValue('B8', rupiah($total_keluar));
$sheet->setCellValue('C8', rupiah($saldo));
$sheet->getStyle('A8:C8')->applyFromArray($tableCellStyle);
$sheet->getStyle('A8:C8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->getRowDimension(9)->setRowHeight(10);

// Detail Pemasukan
$sheet->setCellValue('A10', 'Detail Pemasukan');
$sheet->mergeCells('A10:E10');
$sheet->getStyle('A10')->applyFromArray($sectionHeaderStyle);

$sheet->setCellValue('A11', 'Tanggal');
$sheet->setCellValue('B11', 'Donatur');
$sheet->setCellValue('C11', 'Metode');
$sheet->setCellValue('D11', 'Jumlah');
$sheet->getStyle('A11:D11')->applyFromArray($tableHeaderStyle);

$row = 12;
if (mysqli_num_rows($pemasukan) > 0) {
    mysqli_data_seek($pemasukan, 0);
    while ($d = mysqli_fetch_assoc($pemasukan)) {
        $sheet->setCellValue('A' . $row, date('d/m/Y H:i', strtotime($d['tanggal'])));
        $sheet->setCellValue('B' . $row, htmlspecialchars($d['sumber']));
        $sheet->setCellValue('C' . $row, ucfirst($d['tipe']));
        $sheet->setCellValue('D' . $row, rupiah($d['jumlah']));
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($tableCellStyle);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;
    }
} else {
    $sheet->setCellValue('A' . $row, 'Tidak ada data pemasukan pada periode ini.');
    $sheet->mergeCells('A' . $row . ':D' . $row);
    $sheet->getStyle('A' . $row)->applyFromArray($tableCellStyle);
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;
}

$row++;
$sheet->getRowDimension($row)->setRowHeight(10);
$row++;

// Detail Pengeluaran (dengan kolom Kegiatan)
$sheet->setCellValue('A' . $row, 'Detail Pengeluaran');
$sheet->mergeCells('A' . $row . ':E' . $row);
$sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
$row++;

$sheet->setCellValue('A' . $row, 'Tanggal');
$sheet->setCellValue('B' . $row, 'Deskripsi');
$sheet->setCellValue('C' . $row, 'Kategori');
$sheet->setCellValue('D' . $row, 'Kegiatan');
$sheet->setCellValue('E' . $row, 'Jumlah');
$sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($tableHeaderStyle);
$row++;

if (mysqli_num_rows($pengeluaran) > 0) {
    mysqli_data_seek($pengeluaran, 0);
    while ($k = mysqli_fetch_assoc($pengeluaran)) {
        $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($k['tanggal'])));
        $sheet->setCellValue('B' . $row, htmlspecialchars($k['deskripsi']));
        $sheet->setCellValue('C' . $row, htmlspecialchars($k['kategori']));
        $sheet->setCellValue('D' . $row, htmlspecialchars($k['nama_kegiatan'] ?? '-'));
        $sheet->setCellValue('E' . $row, rupiah($k['jumlah']));
        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($tableCellStyle);
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;
    }
} else {
    $sheet->setCellValue('A' . $row, 'Tidak ada data pengeluaran pada periode ini.');
    $sheet->mergeCells('A' . $row . ':E' . $row);
    $sheet->getStyle('A' . $row)->applyFromArray($tableCellStyle);
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;
}

// Tanda tangan
$row++;
$stampRow = $row;
$sheet->setCellValue('E' . $stampRow, 'Medan, ' . date('d F Y'));
$sheet->mergeCells('E' . $stampRow . ':F' . $stampRow);
$sheet->getStyle('E' . $stampRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->setCellValue('E' . ($stampRow + 1), 'Panitia Pembangunan');
$sheet->mergeCells('E' . ($stampRow + 1) . ':F' . ($stampRow + 1));
$sheet->getStyle('E' . ($stampRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
for ($i = 0; $i < 3; $i++) {
    $sheet->setCellValue('E' . ($stampRow + 2 + $i), '(_________________________)');
    $sheet->mergeCells('E' . ($stampRow + 2 + $i) . ':F' . ($stampRow + 2 + $i));
    $sheet->getStyle('E' . ($stampRow + 2 + $i))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
}

// Footer
$lastRow = $stampRow + 6;
$sheet->setCellValue('A' . $lastRow, 'Laporan Keuangan - Sistem Informasi Pembangunan Gereja | Dicetak: ' . date('d/m/Y H:i:s'));
$sheet->mergeCells('A' . $lastRow . ':F' . $lastRow);
$sheet->getStyle('A' . $lastRow)->getFont()->setSize(8)->setItalic(true);
$sheet->getStyle('A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// Auto size columns A-F
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set orientasi landscape
$spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

// Output file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="laporan_keuangan_' . $filter_date . '.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>