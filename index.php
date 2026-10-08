<?php
include 'config/database.php';
include 'config/session.php';
include 'core/functions.php';

// Kemudian include navbar
include 'includes/navbar_user.php';

// ========== FUNGSI HITUNG PROGRES (sama dengan di admin) ==========
function hitungProgresDariDana($dana_terpakai, $kebutuhan_dana)
{
    if ($kebutuhan_dana <= 0) return 0;
    $progres = ($dana_terpakai / $kebutuhan_dana) * 100;
    $progres = round($progres, 2);
    return min($progres, 100);
}

// Total donasi terverifikasi (gunakan fungsi yang sudah ada)
$totalDonasi = getTotalDonasi($conn);

// Hitung total kebutuhan dana dari semua kegiatan pembangunan
$totalKebutuhan = 0;
$queryKebutuhan = mysqli_query($conn, "SELECT SUM(kebutuhan_dana) as total FROM pembangunan");
if ($queryKebutuhan) {
    $row = mysqli_fetch_assoc($queryKebutuhan);
    $totalKebutuhan = $row['total'] ?? 0;
}

// Hitung total realisasi pengeluaran riil (dari tabel pengeluaran)
$totalRealisasi = 0;
$queryRealisasi = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran");
if ($queryRealisasi) {
    $row = mysqli_fetch_assoc($queryRealisasi);
    $totalRealisasi = $row['total'] ?? 0;
}

// Progres pembangunan (keseluruhan)
$progres = hitungProgresDariDana($totalRealisasi, $totalKebutuhan);

// Total donatur unik
$totalDonatur = 0;
$queryDonatur = mysqli_query($conn, "SELECT COUNT(DISTINCT nama_donatur) as count FROM donasi");
if ($queryDonatur) {
    $row = mysqli_fetch_assoc($queryDonatur);
    $totalDonatur = $row['count'] ?? 0;
}
?>

<div class="container mt-5">
    <!-- Hero Section -->
    <div class="row align-items-center" data-aos="fade-up">
        <div class="col-md-5">
            <h1 class="display-4 fw-bold" style="color: #1e3c72;">Sistem Informasi <br>Pembangunan Gereja</h1>
            <p class="lead mt-3">Mari bersama membangun rumah Tuhan yang lebih layak. Transparan, akuntabel, dan partisipatif.</p>
            <a href="donasi.php" class="btn btn-donasi mt-2 px-4 py-2 rounded-pill">
                <i class="fas fa-hand-holding-heart me-2"></i> Donasi Sekarang <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <div class="col-md-7">
            <img src="uploads/informasi/gereja.png" class="img-fluid rounded-4 shadow w-100" alt="Gereja St. Laurensius" style="max-height: 400px; object-fit: cover;">
        </div>
    </div>

    <!-- Statistik Cepat -->
    <div class="row g-4 mt-5 text-center">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card border-0 shadow-sm p-3 rounded-4">
                <i class="fas fa-donate fa-3x text-primary"></i>
                <h3 class="fw-bold mt-2"><?php echo rupiah($totalDonasi); ?></h3>
                <p>Total Donasi Terkumpul</p>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card border-0 shadow-sm p-3 rounded-4">
                <i class="fas fa-chart-line fa-3x text-warning"></i>
                <h3 class="fw-bold mt-2"><?php echo number_format($progres, 2); ?>%</h3>
                <p>Progres Pembangunan</p>
                <div class="progress">
                    <div class="progress-bar bg-warning" style="width: <?php echo $progres; ?>%"></div>
                </div>
                <small class="text-muted mt-2 d-block">
                    Dana terpakai: <?php echo rupiah($totalRealisasi); ?> dari <?php echo rupiah($totalKebutuhan); ?>
                </small>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card border-0 shadow-sm p-3 rounded-4">
                <i class="fas fa-users fa-3x text-success"></i>
                <h3 class="fw-bold mt-2"><?php echo $totalDonatur; ?></h3>
                <p>Total Donatur</p>
            </div>
        </div>
    </div>

    <!-- Informasi Singkat -->
    <div class="row mt-5" data-aos="fade-up">
        <div class="col-12">
            <h2 class="text-center fw-bold">Tentang Pembangunan</h2>
            <p class="text-center text-muted">Gereja Stasi St. Laurensius sedang berusaha membangun tempat ibadah yang lebih representatif.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>