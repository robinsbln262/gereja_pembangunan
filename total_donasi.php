<?php
include 'config/database.php';
include 'config/session.php';
include 'core/functions.php';
include 'includes/navbar_user.php';

// ========== FUNGSI SENSOR NAMA ==========
function sensorNama($nama)
{
    $kata = explode(' ', $nama);
    $hasil = [];
    foreach ($kata as $k) {
        $panjang = strlen($k);
        if ($panjang <= 2) {
            $hasil[] = $k;
        } elseif ($panjang == 3) {
            $hasil[] = $k[0] . '**';
        } else {
            $awal = $k[0];
            $akhir = $k[$panjang - 1];
            $bintang = str_repeat('*', $panjang - 2);
            $hasil[] = $awal . $bintang . $akhir;
        }
    }
    return implode(' ', $hasil);
}

// Target dana
$queryTarget = mysqli_query($conn, "SELECT target_dana FROM total_donasi LIMIT 1");
if ($queryTarget && mysqli_num_rows($queryTarget) > 0) {
    $target = mysqli_fetch_assoc($queryTarget)['target_dana'];
} else {
    $target = 500000000;
}

$totalTerkumpul = getTotalDonasi($conn);
$persen = ($totalTerkumpul / $target) * 100;
$persen = min(100, round($persen, 1));

// ========== AMBIL DONATUR UNIK (BERDASARKAN EMAIL) ==========
// Jika email tidak ada, fallback ke GROUP BY nama_donatur (tidak disarankan, tapi aman)
// Pastikan kolom email ada di tabel donasi.
$queryDonaturUnik = mysqli_query($conn, "
    SELECT 
        nama_donatur, 
        email,
        SUM(jumlah) AS total_jumlah,
        COUNT(*) AS jumlah_donasi,
        MAX(created_at) AS tgl_terbaru
    FROM donasi
    WHERE status = 'verified'
    GROUP BY email
    ORDER BY tgl_terbaru DESC
    LIMIT 10
");

if (!$queryDonaturUnik) {
    // Fallback jika kolom email tidak ada (GROUP BY nama)
    $queryDonaturUnik = mysqli_query($conn, "
        SELECT 
            nama_donatur, 
            SUM(jumlah) AS total_jumlah,
            COUNT(*) AS jumlah_donasi,
            MAX(created_at) AS tgl_terbaru
        FROM donasi
        WHERE status = 'verified'
        GROUP BY nama_donatur
        ORDER BY tgl_terbaru DESC
        LIMIT 10
    ");
}
?>

<div class="container mt-4" data-aos="fade-up">
    <div class="row g-4">
        <!-- Kolom Kiri: Ringkasan Total Donasi -->
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 text-center h-100 p-3">
                <h4 class="fw-bold" style="color: #1e3c72;">Total Donasi Terkumpul</h4>
                <div class="display-4 fw-bold text-primary my-2"><?php echo rupiah($totalTerkumpul); ?></div>
                <p class="mb-1">Dari target <strong><?php echo rupiah($target); ?></strong></p>
                <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: <?php echo $persen; ?>%;"><?php echo $persen; ?>%</div>
                </div>
                <p class="text-muted small mb-0">Terima kasih atas partisipasi dan doa umat. Tuhan memberkati.</p>
            </div>
        </div>

        <!-- Kolom Kanan: Daftar Donatur Terbaru (UNIK, tidak duplikat) -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0 fw-semibold">
                    <i class="fas fa-users me-2 text-primary"></i> Donatur Terbaru
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (mysqli_num_rows($queryDonaturUnik) > 0): ?>
                            <?php while ($d = mysqli_fetch_assoc($queryDonaturUnik)):
                                // Buat tampilan jumlah donasi
                                $jumlahDonasiInfo = '';
                                if ($d['jumlah_donasi'] > 1) {
                                    $jumlahDonasiInfo = ' (' . $d['jumlah_donasi'] . 'x donasi)';
                                }
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-user-circle me-2 text-secondary"></i>
                                        <span><?php echo sensorNama(htmlspecialchars($d['nama_donatur'])); ?></span>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-success"><?php echo rupiah($d['total_jumlah']); ?></span>
                                        <span class="text-muted small ms-1"><?php echo $jumlahDonasiInfo; ?></span>
                                        <small class="text-muted ms-2"><?php echo date('d M Y', strtotime($d['tgl_terbaru'])); ?></small>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center text-muted">Belum ada donasi terverifikasi.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>