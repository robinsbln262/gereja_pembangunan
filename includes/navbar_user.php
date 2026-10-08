<?php
$base_url = '/gereja_pembangunan'; // Ubah jika berbeda

$current_page = basename($_SERVER['PHP_SELF']);
// Untuk URL bersih, nama file tanpa ekstensi (misal "galeri" bukan "galeri.php")
$current_page_clean = str_replace('.php', '', $current_page);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Pembangunan Gereja St. Laurensius</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="uploads/informasi/logo-st-laurensius.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Styles tetap sama seperti sebelumnya */
        * {
            font-family: 'Inter', sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        .navbar-brand {
            font-family: 'Poppins', sans-serif;
        }

        .navbar-custom {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 12px 0;
        }

        .navbar-brand {
            font-size: 1.4rem;
        }

        .nav-link-custom {
            color: white !important;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 8px;
            position: relative;
        }

        .nav-link-custom:hover {
            color: #FFD700 !important;
            transform: translateY(-2px);
        }

        .nav-link-custom.active {
            color: #FFD700 !important;
        }

        .nav-link-custom.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #FFD700;
            border-radius: 2px;
        }

        .btn-donasi {
            background: #FFD700;
            color: #1e3c72;
            border-radius: 40px;
            padding: 8px 24px;
            font-weight: bold;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-donasi:hover {
            background: #ffc107;
            transform: scale(1.05);
        }

        .navbar-toggler {
            border: none;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 12px;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            transition: opacity 0.5s ease;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(255, 255, 255, 0.2);
            border-top: 5px solid #FFD700;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loading-fadeout {
            opacity: 0;
            visibility: hidden;
        }

        footer {
            background: #1e3c72;
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="index">
                <img src="uploads/informasi/st-laurensius.png"
                    alt="Logo Gereja St. Laurensius"
                    style="height: 60px; width: 60px; border-radius: 50%; object-fit: cover; margin-right: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">
                Gereja St. Laurensius
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link nav-link-custom <?php echo ($current_page_clean == 'index') ? 'active' : ''; ?>" href="index"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom <?php echo ($current_page_clean == 'informasi') ? 'active' : ''; ?>" href="informasi"><i class="fas fa-info-circle me-1"></i> Informasi</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom <?php echo ($current_page_clean == 'pembangunan') ? 'active' : ''; ?>" href="pembangunan"><i class="fas fa-hard-hat me-1"></i> Pembangunan</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom <?php echo ($current_page_clean == 'galeri') ? 'active' : ''; ?>" href="galeri"><i class="fas fa-images me-1"></i> Galeri</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom <?php echo ($current_page_clean == 'donasi') ? 'active' : ''; ?>" href="donasi"><i class="fas fa-hand-holding-heart me-1"></i> Donasi</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom <?php echo ($current_page_clean == 'total_donasi') ? 'active' : ''; ?>" href="total_donasi"><i class="fas fa-chart-line me-1"></i> Total Donasi</a></li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0"><a class="btn btn-donasi" href="donasi"><i class="fas fa-donate me-1"></i> Donasi Sekarang</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="loading-screen">
        <div class="loading-spinner"></div>
        <p class="text-white mt-4 fw-semibold">Memuat Sistem Informasi Gereja...</p>
    </div>
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                var loader = document.getElementById('loading-screen');
                if (loader) {
                    loader.classList.add('loading-fadeout');
                    setTimeout(function() {
                        loader.style.display = 'none';
                    }, 500);
                }
            }, 500);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>