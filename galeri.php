<?php
include 'config/database.php';
include 'config/session.php';
include 'core/functions.php';
include 'includes/navbar_user.php';

// Ambil data galeri: kelompokkan berdasarkan judul, hanya 3 grup terbaru
$query = mysqli_query($conn, "
    SELECT 
        judul,
        MAX(tanggal_upload) as tgl_terbaru
    FROM galeri
    GROUP BY judul
    ORDER BY tgl_terbaru ASC
    LIMIT 3
");

$galeriTerbaru = [];
while ($row = mysqli_fetch_assoc($query)) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM galeri WHERE judul = ? ORDER BY urutan ASC, id_galeri ASC");
    mysqli_stmt_bind_param($stmt, "s", $row['judul']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $galeriTerbaru[$row['judul']] = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Ambil konten rencana pembangunan dari tabel informasi_gereja
$queryRencana = mysqli_query($conn, "SELECT konten FROM informasi_gereja WHERE tipe='rencana_pembangunan' LIMIT 1");
$rencana = mysqli_fetch_assoc($queryRencana);
$konten_rencana = $rencana['konten'] ?? '<p class="text-muted">Konten rencana pembangunan belum tersedia.</p>';
?>

<div class="container mt-5 pt-3">
    <div class="text-center mb-5" data-aos="fade-down" data-aos-duration="800">
        <h1 class="fw-bold" style="color: #1e3c72;">Galeri Pembangunan</h1>
        <p class="text-muted">Dokumentasi kegiatan, desain gereja, dan momen kebersamaan</p>
    </div>

    <div class="row g-4 mb-5" id="galeri-grid">
        <?php if (count($galeriTerbaru) > 0): ?>
            <?php $delay = 100;
            foreach ($galeriTerbaru as $judul => $gambarArray): ?>
                <div class="col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="<?php echo $delay; ?>">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 transition-card">
                        <?php $first = $gambarArray[0]; ?>
                        <div class="position-relative overflow-hidden" style="height: 250px; cursor: pointer;"
                            data-bs-toggle="modal" data-bs-target="#grupModal"
                            data-judul="<?php echo htmlspecialchars($judul); ?>"
                            data-gambar='<?php echo json_encode($gambarArray); ?>'>
                            <img src="uploads/galeri/<?php echo htmlspecialchars($first['gambar']); ?>"
                                class="card-img-top w-100 h-100" style="object-fit: cover; transition: transform 0.4s ease;"
                                alt="<?php echo htmlspecialchars($judul); ?>">
                            <?php if (count($gambarArray) > 1): ?>
                                <div class="position-absolute bottom-0 end-0 bg-dark text-white px-2 py-1 m-2 rounded-pill small">
                                    +<?php echo count($gambarArray) - 1; ?> foto
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold"><?php echo htmlspecialchars($judul); ?></h5>
                            <?php
                            $rawDesc = $first['deskripsi'] ?? '';
                            $plainDesc = strip_tags($rawDesc);
                            $shortDesc = strlen($plainDesc) > 80 ? substr($plainDesc, 0, 80) . '...' : $plainDesc;
                            ?>
                            <?php if (!empty($shortDesc)): ?>
                                <p class="text-muted small"><?php echo htmlspecialchars($shortDesc); ?></p>
                            <?php endif; ?>
                            <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y', strtotime($first['tanggal_upload'])); ?></small>
                        </div>
                    </div>
                </div>
            <?php $delay += 100;
            endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-images fa-4x text-muted mb-3"></i>
                <p class="text-muted">Belum ada foto galeri.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Rencana Pembangunan (dinamis) -->
    <div class="row mt-5" data-aos="fade-up" data-aos-delay="500">
        <div class="col-12">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h3 class="fw-bold mb-0"><i class="fas fa-drafting-compass me-2"></i> Rencana Pembangunan</h3>
                </div>
                <div class="card-body p-4">
                    <?php echo $konten_rencana; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lightbox untuk grup gambar -->
<div class="modal fade" id="grupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <div id="grupCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators" id="carouselIndicators"></div>
                    <div class="carousel-inner" id="carouselInner"></div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#grupCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#grupCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <div class="bg-dark text-white rounded-bottom p-3 mt-2" id="modalCaption">
                    <h5 id="modalTitle" class="fw-bold"></h5>
                    <div id="modalDesc" class="mb-1"></div>
                    <p id="modalKeterangan" class="mb-1 small fst-italic"></p>
                    <small id="modalDate" class="text-white-50"></small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.card .position-relative[data-bs-toggle="modal"]').forEach(el => {
        el.addEventListener('click', function() {
            const judul = this.dataset.judul;
            const gambarArray = JSON.parse(this.dataset.gambar);
            const carouselInner = document.getElementById('carouselInner');
            const indicators = document.getElementById('carouselIndicators');
            carouselInner.innerHTML = '';
            indicators.innerHTML = '';

            gambarArray.forEach((img, idx) => {
                const indicator = document.createElement('button');
                indicator.type = 'button';
                indicator.setAttribute('data-bs-target', '#grupCarousel');
                indicator.setAttribute('data-bs-slide-to', idx);
                if (idx === 0) indicator.classList.add('active');
                indicators.appendChild(indicator);

                const item = document.createElement('div');
                item.className = 'carousel-item' + (idx === 0 ? ' active' : '');
                item.innerHTML = `
                    <img src="uploads/galeri/${img.gambar}" class="d-block w-100" style="max-height: 80vh; object-fit: contain;">
                    <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                        <p>${img.keterangan ? img.keterangan.replace(/"/g, '&quot;') : ''}</p>
                    </div>
                `;
                carouselInner.appendChild(item);
            });

            const first = gambarArray[0];
            document.getElementById('modalTitle').innerText = judul;
            const descDiv = document.getElementById('modalDesc');
            if (first.deskripsi) {
                // Tambahkan ikon dan HTML deskripsi (biarkan inline style bekerja)
                descDiv.innerHTML = '<i class="fas fa-align-left me-1"></i> ' + first.deskripsi;
            } else {
                descDiv.innerHTML = '';
            }
            document.getElementById('modalKeterangan').innerHTML = first.keterangan ? '<i class="fas fa-pen-alt me-1"></i> ' + first.keterangan.replace(/</g, '&lt;') : '';
            document.getElementById('modalDate').innerHTML = '<i class="far fa-calendar-alt me-1"></i> ' + new Date(first.tanggal_upload).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });

            const carousel = new bootstrap.Carousel(document.getElementById('grupCarousel'));
            carousel.to(0);
        });
    });
</script>

<style>
    .transition-card {
        transition: transform 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1), box-shadow 0.3s ease;
        will-change: transform;
    }

    .transition-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.15) !important;
    }

    [data-aos] {
        transition-timing-function: cubic-bezier(0.2, 0.9, 0.4, 1.1);
    }

    .btn-close-white {
        filter: invert(1);
        z-index: 1050;
    }

    .carousel-caption {
        bottom: 20px;
    }

    /* Biarkan style inline dari Summernote (misal font-family) bekerja tanpa di-override */
    #modalDesc {
        /* jangan set font-family agar inline style menang */
    }
</style>

<?php include 'includes/footer.php'; ?>