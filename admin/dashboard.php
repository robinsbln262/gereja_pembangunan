<?php
ob_start();
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login");
    exit();
}

include '../config/database.php';
include '../core/functions.php';
include '../includes/navbar_admin.php';
include '../includes/sidebar_admin.php';

// ========== FUNGSI HITUNG PROGRES ==========
function hitungProgresDariDana($dana_terpakai, $kebutuhan_dana)
{
    if ($kebutuhan_dana <= 0) return 0;
    $progres = ($dana_terpakai / $kebutuhan_dana) * 100;
    $progres = round($progres, 2);
    return min($progres, 100);
}

// ========== STATISTIK UTAMA ==========
$totalDonasiVerified = 0;
$queryVerified = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM donasi WHERE status='verified'");
if ($queryVerified) {
    $row = mysqli_fetch_assoc($queryVerified);
    $totalDonasiVerified = $row['total'] ?? 0;
}

$totalDonasiPending = 0;
$queryPending = mysqli_query($conn, "SELECT COUNT(*) as count FROM donasi WHERE status='pending'");
if ($queryPending) {
    $row = mysqli_fetch_assoc($queryPending);
    $totalDonasiPending = $row['count'] ?? 0;
}

$totalPembangunan = 0;
$queryPembangunan = mysqli_query($conn, "SELECT * FROM pembangunan");
if ($queryPembangunan) {
    $totalPembangunan = mysqli_num_rows($queryPembangunan);
}

$totalKebutuhanAll = 0;
$queryKebutuhan = mysqli_query($conn, "SELECT SUM(kebutuhan_dana) as total_kebutuhan FROM pembangunan");
if ($queryKebutuhan) {
    $row = mysqli_fetch_assoc($queryKebutuhan);
    $totalKebutuhanAll = $row['total_kebutuhan'] ?? 0;
}

$totalTerpakaiAll = 0;
$queryTerpakai = mysqli_query($conn, "SELECT SUM(jumlah) as total_terpakai FROM pengeluaran");
if ($queryTerpakai) {
    $row = mysqli_fetch_assoc($queryTerpakai);
    $totalTerpakaiAll = $row['total_terpakai'] ?? 0;
}

$progres_keseluruhan = hitungProgresDariDana($totalTerpakaiAll, $totalKebutuhanAll);
$progres_keseluruhan_tampil = number_format($progres_keseluruhan, 2);

// 5 donasi terbaru (hanya untuk tampilan, tanpa aksi)
$donasiTerbaru = mysqli_query($conn, "SELECT * FROM donasi ORDER BY created_at DESC LIMIT 5");
?>

<div class="content-wrapper">
    <div class="container-fluid px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pt-2">
            <h2 class="fw-bold"><i class="fas fa-tachometer-alt me-2 text-primary"></i> Dashboard</h2>
            <div class="mt-2 mt-sm-0">
                <span class="badge bg-light text-dark p-2 shadow-sm">
                    <i class="fas fa-user-circle me-1 text-primary"></i> <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm rounded-4 h-100 transition-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Donasi Terverifikasi</h6>
                                <h3 class="fw-bold mb-0 text-success"><?php echo rupiah($totalDonasiVerified); ?></h3>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm rounded-4 h-100 transition-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Donasi Pending</h6>
                                <h3 class="fw-bold mb-0 text-warning"><?php echo $totalDonasiPending; ?></h3>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card border-0 shadow-sm rounded-4 h-100 transition-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="text-muted mb-1">Progres Pembangunan</h6>
                                <h3 class="fw-bold mb-0 text-info"><?php echo $progres_keseluruhan_tampil; ?>%</h3>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fas fa-hard-hat fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 10px;">
                            <div class="progress-bar bg-info progress-bar-striped" style="width: <?php echo $progres_keseluruhan; ?>%; border-radius: 10px;"></div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-chart-line me-1"></i>
                            Kebutuhan: <?php echo rupiah($totalKebutuhanAll); ?> |
                            Terpakai: <?php echo rupiah($totalTerpakaiAll); ?>
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="400">
                <div class="card border-0 shadow-sm rounded-4 h-100 transition-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Data Pembangunan</h6>
                                <h3 class="fw-bold mb-0 text-primary"><?php echo $totalPembangunan; ?></h3>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fas fa-building fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Donasi Terbaru (tanpa kolom Aksi, karena admin tidak mengelola donasi) -->
        <div class="card border-0 shadow-sm rounded-4" data-aos="fade-up" data-aos-delay="500">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-hand-holding-usd me-2 text-primary"></i> Donasi Terbaru</h5>
                <small class="text-muted">* Pengelolaan donasi sepenuhnya dilakukan oleh Bendahara.</small>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Donatur</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($donasiTerbaru) > 0): ?>
                                <?php while ($d = mysqli_fetch_assoc($donasiTerbaru)): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($d['nama_donatur']); ?></td>
                                        <td class="text-success fw-bold"><?php echo rupiah($d['jumlah']); ?></td>
                                        <td>
                                            <?php
                                            $status = $d['status'];
                                            $badgeClass = match ($status) {
                                                'verified' => 'success',
                                                'pending' => 'warning',
                                                default => 'danger'
                                            };
                                            ?>
                                            <span class="badge bg-<?php echo $badgeClass; ?> px-3 py-2 rounded-pill">
                                                <i class="fas <?php echo $status == 'verified' ? 'fa-check-circle' : ($status == 'pending' ? 'fa-clock' : 'fa-times-circle'); ?> me-1"></i>
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                        <td><i class="far fa-calendar-alt me-1 text-muted"></i> <?php echo date('d/m/Y H:i', strtotime($d['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada data donasi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .transition-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .transition-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
    }
    .progress-bar {
        transition: width 0.6s ease;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
        transition: background 0.2s;
    }
    .badge i {
        font-size: 0.75rem;
    }
</style>

<script>
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true
        });
    }
</script>

<?php include '../includes/footer_admin.php';
ob_end_flush(); ?>