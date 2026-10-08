<?php
include 'config/database.php';
include 'config/session.php';
include 'core/functions.php';
include 'includes/navbar_user.php';

function hitungProgresDariRealisasi($total_realisasi, $kebutuhan_dana)
{
    if ($kebutuhan_dana <= 0) return 0;
    $progres = ($total_realisasi / $kebutuhan_dana) * 100;
    $progres = round($progres, 2);
    return min($progres, 100);
}

$ringkasan      = getRingkasanKeuangan($conn);
$totalMasuk     = $ringkasan['total_masuk'];
$totalKebutuhan = $ringkasan['total_kebutuhan'];
$totalTerpakai  = $ringkasan['total_terpakai'];

// Ambil semua tahap yang sudah digabung (urutan ASC)
$query = mysqli_query($conn, "SELECT * FROM pembangunan ORDER BY urutan ASC");
?>

<div class="container mt-5 pt-3">
    <div class="text-center mb-5" data-aos="fade-down">
        <h1 class="fw-bold" style="color: #1e3c72;">Progres Pembangunan</h1>
        <p class="text-muted">Rencana dan realisasi pembangunan gereja</p>
    </div>

    <!-- Ringkasan Keuangan (sama seperti sebelumnya) -->
    <div class="row g-4 mb-5">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-donate fa-2x mb-2 opacity-75"></i>
                            <h5 class="fw-semibold mt-2">Total Donasi Masuk</h5>
                            <h3 class="fw-bold mb-0"><?php echo rupiah($totalMasuk); ?></h3>
                        </div>
                        <i class="fas fa-arrow-right fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-chart-line fa-2x mb-2 opacity-75"></i>
                            <h5 class="fw-semibold mt-2">Total Kebutuhan</h5>
                            <h3 class="fw-bold mb-0"><?php echo rupiah($totalKebutuhan); ?></h3>
                        </div>
                        <i class="fas fa-arrow-right fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-hand-holding-usd fa-2x mb-2 opacity-75"></i>
                            <h5 class="fw-semibold mt-2">Dana Terpakai (riil)</h5>
                            <h3 class="fw-bold mb-0"><?php echo rupiah($totalTerpakai); ?></h3>
                        </div>
                        <i class="fas fa-arrow-right fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Tahap Pembangunan (urutan otomatis) -->
    <div class="row g-4">
        <div class="col-12">
            <h2 class="text-center fw-bold mb-4" style="color: #1e3c72;">Rencana & Realisasi Tahapan</h2>
            <p class="text-center text-muted small">* "Dana Terpakai (rencana)" adalah total pengeluaran riil yang sudah dialokasikan ke tahap ini.</p>
        </div>
        <?php if (mysqli_num_rows($query) > 0): ?>
            <?php $delay = 100;
            while ($row = mysqli_fetch_assoc($query)):
                $id_kegiatan = $row['id_pembangunan'];
                $queryRealisasi = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_pembangunan = $id_kegiatan");
                $totalRealisasi = 0;
                if ($queryRealisasi && $res = mysqli_fetch_assoc($queryRealisasi)) {
                    $totalRealisasi = $res['total'] ?? 0;
                }
                $kebutuhan = $row['kebutuhan_dana'] ?? 0;
                $progres_numeric = hitungProgresDariRealisasi($totalRealisasi, $kebutuhan);
                $progres_tampil = number_format($progres_numeric, 2);

                // Ambil daftar pengeluaran untuk modal detail
                $queryPengeluaranDetail = mysqli_query($conn, "SELECT * FROM pengeluaran WHERE id_pembangunan = $id_kegiatan ORDER BY tanggal DESC");
                $pengeluaranList = [];
                while ($pl = mysqli_fetch_assoc($queryPengeluaranDetail)) {
                    $pengeluaranList[] = $pl;
                }

                // Status badge
                $statusBadge = '';
                if ($row['status'] == 'selesai') $statusBadge = '<span class="badge bg-success ms-2">Selesai</span>';
                elseif ($row['status'] == 'berlangsung') $statusBadge = '<span class="badge bg-warning text-dark ms-2">Berlangsung</span>';
                else $statusBadge = '<span class="badge bg-secondary ms-2">Direncanakan</span>';
            ?>
                <div class="col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="card border-0 shadow-sm rounded-4 h-100 transition-card overflow-hidden">
                        <?php if ($row['gambar']): ?>
                            <div class="position-relative overflow-hidden" style="height: 220px;">
                                <img src="uploads/pembangunan/<?php echo htmlspecialchars($row['gambar']); ?>" class="card-img-top w-100 h-100" style="object-fit: cover; transition: transform 0.5s ease;" alt="<?php echo htmlspecialchars($row['judul']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start">
                                <h4 class="fw-bold"><?php echo htmlspecialchars($row['judul']); ?></h4>
                                <?php echo $statusBadge; ?>
                            </div>
                            <p class="text-muted flex-grow-1"><?php echo nl2br(htmlspecialchars(substr($row['deskripsi'], 0, 150))); ?><?php echo strlen($row['deskripsi']) > 150 ? '...' : ''; ?></p>

                            <?php if ($row['tgl_mulai'] && $row['tgl_selesai']): ?>
                                <div class="small text-muted mb-2">
                                    <i class="far fa-calendar-alt me-1"></i> <?php echo date('M Y', strtotime($row['tgl_mulai'])); ?> - <?php echo date('M Y', strtotime($row['tgl_selesai'])); ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Progres Pembangunan</span>
                                    <span class="fw-bold"><?php echo $progres_tampil; ?>%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info progress-bar-striped" style="width: 0%; transition: width 1s ease;" data-progress="<?php echo $progres_numeric; ?>"></div>
                                </div>
                            </div>

                            <?php if ($kebutuhan > 0): ?>
                                <div class="row mt-3 g-2">
                                    <div class="col-6">
                                        <div class="bg-light rounded-3 p-2 text-center">
                                            <small class="text-muted">Kebutuhan Dana</small>
                                            <h6 class="fw-bold mb-0 text-success"><?php echo rupiah($kebutuhan); ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light rounded-3 p-2 text-center">
                                            <small class="text-muted">Dana Terpakai (rencana)</small>
                                            <h6 class="fw-bold mb-0 text-warning"><?php echo rupiah($totalRealisasi); ?></h6>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Tombol detail pengeluaran -->
                            <div class="mt-3 pt-2 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Realisasi Pengeluaran (riil)</span>
                                    <span class="fw-bold text-info"><?php echo rupiah($totalRealisasi); ?></span>
                                </div>
                                <?php if (count($pengeluaranList) > 0): ?>
                                    <button class="btn btn-sm btn-outline-secondary mt-2 w-100 btn-detail-pengeluaran"
                                        data-list='<?php echo htmlspecialchars(json_encode($pengeluaranList)); ?>'>
                                        <i class="fas fa-receipt me-1"></i> Lihat Rincian Pengeluaran
                                    </button>
                                <?php else: ?>
                                    <small class="text-muted">Belum ada pengeluaran riil yang dialokasikan ke tahap ini.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php $delay += 100;
            endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5"><i class="fas fa-hard-hat fa-4x text-muted mb-3"></i>
                <p class="text-muted">Belum ada data pembangunan.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail Pengeluaran (sama seperti sebelumnya) -->
<div class="modal fade" id="modalPengeluaranDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-receipt me-2"></i> Rincian Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Deskripsi</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody id="pengeluaranModalBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate progress bars
        const progressBars = document.querySelectorAll('.progress-bar');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const progress = bar.getAttribute('data-progress');
                    if (progress && bar.style.width !== progress + '%') {
                        setTimeout(() => {
                            bar.style.width = progress + '%';
                        }, 100);
                    }
                    observer.unobserve(bar);
                }
            });
        }, {
            threshold: 0.3
        });
        progressBars.forEach(bar => observer.observe(bar));

        // Detail pengeluaran
        document.querySelectorAll('.btn-detail-pengeluaran').forEach(btn => {
            btn.addEventListener('click', function() {
                const list = JSON.parse(this.dataset.list);
                const tbody = document.getElementById('pengeluaranModalBody');
                tbody.innerHTML = '';
                if (list.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">Tidak ada data.ERB</td>';
                    return;
                }
                list.forEach(p => {
                    const row = `
                        <tr>
                            <td>${new Date(p.tanggal).toLocaleDateString('id-ID')}</td>
                            <td>${p.deskripsi}</td>
                            <td>${p.kategori}</td>
                            <td class="text-danger fw-bold">${formatRupiah(p.jumlah)}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
                new bootstrap.Modal(document.getElementById('modalPengeluaranDetail')).show();
            });
        });
    });

    function formatRupiah(angka) {
        return 'Rp ' + angka.toLocaleString('id-ID');
    }
</script>

<style>
    .transition-card {
        transition: transform 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1), box-shadow 0.3s ease;
        will-change: transform;
    }

    .transition-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 40px -12px rgba(0, 0, 0, 0.2) !important;
    }

    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
    }

    .progress-bar {
        border-radius: 10px;
        background-image: linear-gradient(45deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);
        background-size: 1rem 1rem;
        transition: width 0.8s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    }

    [data-aos] {
        transition-timing-function: cubic-bezier(0.2, 0.9, 0.4, 1.1);
    }

    .badge {
        font-weight: 500;
        padding: 0.4em 0.8em;
    }
</style>

<?php include 'includes/footer.php'; ?>