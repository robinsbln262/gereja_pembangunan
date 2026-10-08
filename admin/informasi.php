<?php
ob_start();
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

include '../config/database.php';
include '../core/functions.php';

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

function setFlash($status, $message)
{
    $_SESSION['flash'] = ['status' => $status, 'message' => $message];
}

// Update informasi (termasuk gambar)
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $judul = trim($_POST['judul']);
    $konten = trim($_POST['konten']);
    $tipe = $_POST['tipe'];

    // Proses upload gambar baru jika ada
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $targetDir = '../uploads/informasi/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $gambar = uploadFile($_FILES['gambar'], $targetDir, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        if (!$gambar) {
            setFlash('error', 'Gagal upload gambar. Format harus JPG/PNG/GIF/WEBP, max 5MB.');
            header("Location: informasi.php");
            exit();
        }
    }

    if ($gambar) {
        $stmt = mysqli_prepare($conn, "UPDATE informasi_gereja SET judul=?, konten=?, tipe=?, gambar=? WHERE id_info=?");
        mysqli_stmt_bind_param($stmt, "ssssi", $judul, $konten, $tipe, $gambar, $id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE informasi_gereja SET judul=?, konten=?, tipe=? WHERE id_info=?");
        mysqli_stmt_bind_param($stmt, "sssi", $judul, $konten, $tipe, $id);
    }

    if ($stmt && mysqli_stmt_execute($stmt)) {
        setFlash('success', 'Informasi berhasil diperbarui');
    } else {
        setFlash('error', 'Gagal memperbarui: ' . ($stmt ? mysqli_stmt_error($stmt) : mysqli_error($conn)));
    }
    if ($stmt) mysqli_stmt_close($stmt);
    header("Location: informasi.php");
    exit();
}

// Ambil semua informasi
$informasi = mysqli_query($conn, "SELECT * FROM informasi_gereja ORDER BY tipe");
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/navbar_admin.php';
include '../includes/sidebar_admin.php';
?>

<!-- CDN jQuery (wajib untuk Summernote) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<!-- Summernote CSS + JS dari CDN -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<div class="content-wrapper">
    <div class="container-fluid px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pt-2">
            <h2 class="fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i> Kelola Informasi Gereja</h2>
        </div>

        <?php if ($flash): ?>
            <script>
                Swal.fire({
                    icon: '<?php echo $flash['status']; ?>',
                    title: '<?php echo $flash['status'] === 'success' ? 'Berhasil' : 'Gagal'; ?>',
                    text: '<?php echo addslashes($flash['message']); ?>',
                    timer: 2000,
                    showConfirmButton: true
                });
            </script>
        <?php endif; ?>

        <!-- Accordion untuk setiap tipe informasi -->
        <div class="accordion" id="accordionInformasi">
            <?php $counter = 0;
            while ($row = mysqli_fetch_assoc($informasi)): $counter++; ?>
                <div class="accordion-item mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
                    <h2 class="accordion-header" id="heading<?php echo $counter; ?>">
                        <button class="accordion-button <?php echo ($counter != 1) ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $counter; ?>" aria-expanded="<?php echo ($counter == 1) ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $counter; ?>">
                            <i class="fas fa-<?php echo match ($row['tipe']) {
                                                    'sejarah' => 'history',
                                                    'letak' => 'map-marker-alt',
                                                    'umat' => 'users',
                                                    'tabel_umat' => 'table',
                                                    'rencana_pembangunan' => 'drafting-compass',
                                                    default => 'info-circle'
                                                }; ?> me-2 text-primary"></i>
                            <strong class="ms-2"><?php echo ucfirst(str_replace('_', ' ', $row['tipe'])); ?></strong>
                            <span class="ms-2 text-muted small">(<?php echo htmlspecialchars($row['judul']); ?>)</span>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $counter; ?>" class="accordion-collapse collapse <?php echo ($counter == 1) ? 'show' : ''; ?>" data-bs-parent="#accordionInformasi">
                        <div class="accordion-body p-4">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $row['id_info']; ?>">
                                <input type="hidden" name="tipe" value="<?php echo $row['tipe']; ?>">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Judul</label>
                                    <input type="text" name="judul" class="form-control rounded-3"
                                        value="<?php echo htmlspecialchars($row['judul']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Konten</label>
                                    <textarea name="konten" class="form-control rounded-3 summernote" rows="12"><?php echo htmlspecialchars($row['konten']); ?></textarea>
                                    <small class="text-muted">Gunakan editor ini untuk memformat teks, membuat tabel, daftar, atau menyisipkan gambar. (Seperti Microsoft Word)</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Gambar</label>
                                    <?php if ($row['gambar']): ?>
                                        <div class="mb-2">
                                            <img src="../uploads/informasi/<?php echo $row['gambar']; ?>" class="img-fluid rounded" style="max-height: 150px;">
                                            <br><small class="text-muted">Gambar saat ini. Upload baru jika ingin mengganti.</small>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="gambar" class="form-control rounded-3" accept="image/*">
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar. Format: JPG, PNG, GIF, WEBP, max 5MB.</small>
                                </div>
                                <button type="submit" name="update" class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-save me-2"></i> Update <?php echo ucfirst(str_replace('_', ' ', $row['tipe'])); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.summernote').summernote({
            height: 400,
            minHeight: 300,
            maxHeight: 500,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            placeholder: 'Tulis konten di sini... Anda bisa membuat tabel, daftar, menyisipkan gambar, dll.'
        });
    });
</script>

<style>
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #0d6efd;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0, 0, 0, 0.125);
    }

    .accordion-item {
        border: none;
    }

    .btn {
        transition: all 0.2s;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .note-editor {
        border-radius: 0.75rem !important;
        border-color: #dee2e6 !important;
    }

    .note-toolbar {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
        border-radius: 0.75rem 0.75rem 0 0 !important;
    }
</style>

<?php include '../includes/footer_admin.php';
ob_end_flush(); ?>