<?php
ob_start();
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'bendahara') {
    header("Location: ../admin/login");
    exit();
}

include '../config/database.php';
include '../core/functions.php';
include '../includes/navbar_bendahara.php';
include '../includes/sidebar_bendahara.php';

$totalDonasiVerified = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM donasi WHERE status='verified'"))['total'] ?? 0;
$totalDonasiPending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donasi WHERE status='pending'"))['count'] ?? 0;
$totalPengeluaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran"))['total'] ?? 0;
$donasiTerbaru = mysqli_query($conn, "SELECT * FROM donasi ORDER BY created_at DESC LIMIT 5");
?>

<div class="content-wrapper">
    <div class="container-fluid px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pt-2">
            <h2 class="fw-bold"><i class="fas fa-tachometer-alt me-2 text-primary"></i> Dashboard Bendahara</h2>
            <div><span class="badge bg-light text-dark p-2 shadow-sm"><i class="fas fa-user-circle me-1 text-primary"></i> <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span></div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 bg-success text-white"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">Total Donasi Masuk</h6><h3 class="fw-bold mb-0"><?php echo rupiah($totalDonasiVerified); ?></h3></div><div class="rounded-circle bg-white bg-opacity-10 p-3"><i class="fas fa-donate fa-2x"></i></div></div></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 bg-warning text-dark"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">Donasi Pending</h6><h3 class="fw-bold mb-0"><?php echo $totalDonasiPending; ?></h3></div><div class="rounded-circle bg-white bg-opacity-10 p-3"><i class="fas fa-clock fa-2x"></i></div></div></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 bg-danger text-white"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">Total Pengeluaran</h6><h3 class="fw-bold mb-0"><?php echo rupiah($totalPengeluaran); ?></h3></div><div class="rounded-circle bg-white bg-opacity-10 p-3"><i class="fas fa-hand-holding-usd fa-2x"></i></div></div></div></div></div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold"><i class="fas fa-hand-holding-usd me-2 text-primary"></i> Donasi Terbaru</h5></div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light"><tr><th>Donatur</th><th>Jumlah</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php if (mysqli_num_rows($donasiTerbaru) > 0): ?>
                                <?php while ($d = mysqli_fetch_assoc($donasiTerbaru)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($d['nama_donatur']); ?></td>
                                        <td class="text-success fw-bold"><?php echo rupiah($d['jumlah']); ?></td>
                                        <td><span class="badge bg-<?php echo match($d['status']){'verified'=>'success','pending'=>'warning',default=>'danger'}; ?> px-3 py-2 rounded-pill"><?php echo ucfirst($d['status']); ?></span></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($d['created_at'])); ?></td>
                                        <td><a href="laporan_keuangan" class="btn btn-sm btn-outline-primary rounded-pill px-3">Detail</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada donasi</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer_admin.php'; ob_end_flush(); ?>