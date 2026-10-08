<?php
include 'config/database.php';
include 'config/session.php';
include 'core/functions.php';
include 'includes/navbar_user.php';

// Ambil semua informasi berdasarkan tipe
$query = mysqli_query($conn, "SELECT * FROM informasi_gereja WHERE tipe IN ('sejarah','letak','umat','tabel_umat') ORDER BY FIELD(tipe, 'sejarah', 'letak', 'umat', 'tabel_umat')");
$informasi = [];
while ($row = mysqli_fetch_assoc($query)) {
    $informasi[$row['tipe']] = $row;
}

// Data untuk grafik
$tahun = [1968, 1978, 1989, 2017, 2020, 2021, 2022, 2023, 2024]; 
$jumlah = [38, 180, 280, 446, 508, 522, 529, 543, 555, 595, 764];
?>

<div class="container mt-5 pt-3">
    <div class="text-center mb-5" data-aos="fade-down">
        <h1 class="fw-bold" style="color: #1e3c72;">Informasi Gereja</h1>
        <p class="text-muted">Sejarah, letak, pertumbuhan umat, dan struktur organisasi</p>
    </div>

    <!-- Sejarah -->
    <div class="row mb-5" data-aos="fade-up" data-aos-duration="800">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-card">
                <div class="card-body p-4">
                    <h3 class="fw-bold"><i class="fas fa-history me-2 text-primary"></i> <?php echo htmlspecialchars($informasi['sejarah']['judul'] ?? 'Sejarah Gereja'); ?></h3>
                    <div class="mt-3 content-text">
                        <?php echo $informasi['sejarah']['konten'] ?? '<p class="text-muted">Belum ada data.</p>'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Letak Administratif & Peta -->
    <div class="row mb-5 g-4">
        <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-card">
                <div class="card-body">
                    <h4 class="fw-bold"><i class="fas fa-map-marker-alt me-2 text-primary"></i> Letak Administratif</h4>
                    <?php if (!empty($informasi['letak']['gambar'])): ?>
                        <div class="text-center mb-3">
                            <img src="uploads/informasi/<?php echo htmlspecialchars($informasi['letak']['gambar']); ?>" 
                                 class="img-fluid rounded shadow-sm" style="max-height: 250px; width: 100%; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    <div class="content-text">
                        <?php echo $informasi['letak']['konten'] ?? '<p class="text-muted">Belum ada data.</p>'; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-card">
                <div class="card-body">
                    <h4 class="fw-bold"><i class="fas fa-church me-2 text-primary"></i> Lokasi Gereja</h4>
                    <div class="ratio ratio-16x9 mt-2">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3981.691181471162!2d98.61010331465878!3d3.558357797402893!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x303131ab4a7e2b0b%3A0x4e5b3b2c7f8a5d6e!2sJl.%20Jamin%20Ginting%20No.20%2C%20Medan%2C%20Sumatera%20Utara!5e0!3m2!1sid!2sid!4v1640000000000!5m2!1sid!2sid" 
                            allowfullscreen="" loading="lazy" title="Google Maps"></iframe>
                    </div>
                    <p class="mt-2 small text-muted">Jl. Jamin Ginting No.20, Kemenangan Tani, Kec. Medan Tuntungan, Kota Medan, Sumatera Utara 20135</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Jumlah Umat dan Grafik -->
    <div class="row mb-5 g-4">
        <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-card">
                <div class="card-body">
                    <h4 class="fw-bold"><i class="fas fa-users me-2 text-primary"></i> Pertumbuhan Umat & Struktur</h4>
                    <div class="content-text">
                        <?php echo $informasi['umat']['konten'] ?? '<p class="text-muted">Belum ada data.</p>'; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-card">
                <div class="card-body">
                    <h4 class="fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i> Grafik Pertumbuhan</h4>
                    <canvas id="growthChart" width="400" height="300" style="max-width:100%; height:auto;"></canvas>
                    <div class="mt-3 content-text">
                        <?php echo $informasi['tabel_umat']['konten'] ?? '<p class="text-muted">Belum ada data tabel.</p>'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tahun = <?php echo json_encode($tahun); ?>;
        const jumlah = <?php echo json_encode($jumlah); ?>;
        const ctx = document.getElementById('growthChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: tahun,
                datasets: [{
                    label: 'Jumlah Keluarga (KK)',
                    data: jumlah,
                    borderColor: '#1e3c72',
                    backgroundColor: 'rgba(30, 60, 114, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#1e3c72',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top', labels: { font: { family: 'Inter' } } },
                    tooltip: { 
                        callbacks: { label: (ctx) => `${ctx.raw.toLocaleString()} KK` },
                        backgroundColor: '#1e3c72'
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        title: { display: true, text: 'Jumlah KK', font: { weight: 'bold' } },
                        grid: { color: '#e9ecef' }
                    },
                    x: { 
                        title: { display: true, text: 'Tahun', font: { weight: 'bold' } },
                        grid: { display: false }
                    }
                },
                interaction: { mode: 'index', intersect: false },
                hover: { mode: 'nearest', intersect: true }
            }
        });
    });
</script>

<style>
    /* Smooth transition untuk card */
    .transition-card {
        transition: transform 0.3s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.3s ease;
        will-change: transform;
    }
    .transition-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.15) !important;
    }
    /* Konten teks rapi */
    .content-text p, .content-text ul, .content-text ol {
        line-height: 1.6;
        color: #2c3e50;
    }
    .content-text ul, .content-text ol {
        padding-left: 1.5rem;
    }
    .content-text li {
        margin-bottom: 0.5rem;
    }
    /* Gambar responsif */
    .img-fluid {
        max-width: 100%;
        height: auto;
    }
    /* AOS override untuk smooth */
    [data-aos] {
        transition-timing-function: cubic-bezier(0.2, 0.9, 0.4, 1.1);
    }
</style>

<?php include 'includes/footer.php'; ?>