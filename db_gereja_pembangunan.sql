-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2026 at 06:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_gereja_pembangunan`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(60) NOT NULL,
  `email` varchar(60) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','bendahara') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama_lengkap`, `email`, `created_at`, `role`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Panitia Pembangunan Gereja', 'pp.sls.selayang@gmail.com', '2026-04-03 13:27:55', 'admin'),
(2, 'bendahara', '62f7dec74b78ba0398e6a9f317f55126', 'Bendahara Gereja', 'pp.sls.selayang@gmail.com', '2026-05-17 01:21:01', 'bendahara');

-- --------------------------------------------------------

--
-- Table structure for table `donasi`
--

CREATE TABLE `donasi` (
  `id_donasi` int(11) NOT NULL,
  `nama_donatur` varchar(60) NOT NULL,
  `email` varchar(60) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `metode_pembayaran` enum('transfer','tunai','qris') DEFAULT 'transfer',
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','ditolak') DEFAULT 'pending',
  `alasan_ditolak` text DEFAULT NULL,
  `pesan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donasi`
--

INSERT INTO `donasi` (`id_donasi`, `nama_donatur`, `email`, `no_telepon`, `jumlah`, `metode_pembayaran`, `bukti_transfer`, `status`, `alasan_ditolak`, `pesan`, `created_at`) VALUES
(7, 'Mangolop Purba', 'olopp22@gmail.com', '081262346502', 500000.00, 'transfer', '1775498124_hasil3.png', 'verified', NULL, 'Tuhan Memberkati. Amin...', '2026-04-06 17:55:24'),
(8, 'Marta br. Sitepu', 'marta@gmail.com', '081234567890', 250000.00, 'transfer', 'dummy_bukti1.jpg', 'verified', NULL, 'Semoga pembangunan berjalan lancar', '2026-04-04 03:00:00'),
(9, 'Petrus Tarigan', 'petrus@yahoo.com', '081355667788', 750000.00, 'transfer', 'dummy_bukti2.jpg', 'verified', NULL, 'Tuhan Yesus memberkati', '2026-04-05 07:30:00'),
(10, 'Maria Simbolon', 'maria@gmail.com', '081277889900', 1000000.00, 'transfer', 'dummy_bukti3.jpg', 'verified', NULL, 'Doa dari keluarga kami', '2026-04-07 02:15:00'),
(12, 'Ara Tampubolon', 'aratpl16@gmail.com', '081262246315', 150000.00, 'transfer', '1776078560_tf-donasi.jpeg', 'verified', NULL, '', '2026-04-13 11:09:20'),
(35, 'S Simbolon', 'simbolonrimbang262@gmail.com', '081287201152', 150000.00, 'transfer', '1777240489_tf-donasi.jpeg', 'ditolak', 'Bukti transfer tidak jelas / tidak terbaca', '', '2026-04-26 21:54:49'),
(36, 'Ucok Pinem', 'ucokpinem66@gmail.com', '081278996520', 150000.00, 'transfer', '1779033227_tf-donasi.jpeg', 'pending', NULL, '', '2026-05-17 15:53:47');

-- --------------------------------------------------------

--
-- Table structure for table `galeri`
--

CREATE TABLE `galeri` (
  `id_galeri` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `gambar` varchar(255) NOT NULL,
  `tipe` enum('foto','desain','kegiatan') DEFAULT 'foto',
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `urutan` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `galeri`
--

INSERT INTO `galeri` (`id_galeri`, `judul`, `deskripsi`, `keterangan`, `gambar`, `tipe`, `tanggal_upload`, `urutan`) VALUES
(14, 'Alternatif 1: Pembangunan Gereja Baru', '<p style=\"text-align: justify;\"><span style=\"font-family: Inter;\">﻿</span><span style=\"font-family: Inter;\">Alternatif ini merupakan kelanjutan dari rencana pembangunan sebelumnya, di mana umat telah berhasil memperoleh sebidang tanah seluas ±2.100 m² di Jl. Bunga Pancur IX sebagai lokasi gereja baru. Gereja baru akan dirancang sebagai tempat ibadah utama dengan kapasitas yang lebih besar, aman, serta sesuai standar liturgi Gereja Katolik. Pembangunan di atas lahan baru juga memungkinkan kegiatan ibadah terus berlangsung di gereja lama tanpa gangguan selama proses konstruksi. Selain ruang misa utama, akan disiapkan fasilitas pendukung seperti:\r\n</span></p><p style=\"text-align: justify;\"><span style=\"font-family: Inter;\">a.	Aula serbaguna.</span><span style=\"font-family: Inter;\">﻿</span></p><p style=\"text-align: justify;\"><span style=\"font-family: Inter;\">b.	Ruang BIA &amp; Kategorial.</span></p><p style=\"text-align: justify;\"><span style=\"font-family: Inter;\">c.	Sakristi dan Pastoran Kecil.</span></p><p style=\"text-align: justify;\"><span style=\"font-family: Inter;\">d.	Area parker yang memadai</span></p>', 'Denah gereja baru yang rencananya akan dibangun di tanah aset sebagai alternatif 1', '1776208255_Denahgerejabaruyangdirencanakanakandibangunditanahasetsebagaialternatif1..jpg', 'desain', '2026-04-14 23:10:55', 0),
(15, 'Alternatif 1: Pembangunan Gereja Baru', '<p style=\"text-align: justify;\"><span style=\"font-family: Inter;\">﻿</span><span style=\"font-family: Inter;\">Alternatif ini merupakan kelanjutan dari rencana pembangunan sebelumnya, di mana umat telah berhasil memperoleh sebidang tanah seluas ±2.100 m² di Jl. Bunga Pancur IX sebagai lokasi gereja baru. Gereja baru akan dirancang sebagai tempat ibadah utama dengan kapasitas yang lebih besar, aman, serta sesuai standar liturgi Gereja Katolik. Pembangunan di atas lahan baru juga memungkinkan kegiatan ibadah terus berlangsung di gereja lama tanpa gangguan selama proses konstruksi. Selain ruang misa utama, akan disiapkan fasilitas pendukung seperti:\r\n</span></p><p style=\"text-align: justify;\"><span style=\"font-family: Inter;\">a.	Aula serbaguna.</span><span style=\"font-family: Inter;\">﻿</span></p><p style=\"text-align: justify;\"><span style=\"font-family: Inter;\">b.	Ruang BIA &amp; Kategorial.</span></p><p style=\"text-align: justify;\"><span style=\"font-family: Inter;\">c.	Sakristi dan Pastoran Kecil.</span></p><p style=\"text-align: justify;\"><span style=\"font-family: Inter;\">d.	Area parker yang memadai</span></p>', 'Ilustrasi tampak depan gereja baru', '1776208256_IlustrasiTampakDepanGerejaBaru.jpg', 'desain', '2026-04-14 23:10:56', 1),
(22, 'Alternatif 2: Pengembangan Gereja Lama Menjadi 3 Lantai', '<p style=\"text-align: justify;\">Alternatif ini memanfaatkan lokasi gereja lama saat ini, yang sudah dikenal umat dan menjadi pusat pelaksanaan kegiatan stasi. Pengembangan dilakukan secara vertikal hingga tiga lantai yang dirancang mencakup:</p><p style=\"text-align: justify;\">a.	Lantai 1 Ruang Aula dengan beberapa kantor pendukung.</p><p style=\"text-align: justify;\">b.	Lantai 2 Ruang Ibadah Utama.</p><p style=\"text-align: justify;\">c.	Lantai 3 Ruang Balkon yang menunjang ibadah di ruang utama.</p><p style=\"text-align: justify;\">Namun, terdapat tantangan utama yaitu keterbatasan area parkir, serta potensi gangguan aktivitas ibadah selama pembangunan berlangsung. Struktur gereja yang sudah tua juga membutuhkan penguatan tambahan agar mampu menopang konstruksi bertingkat. Meskipun demikian, alternatif ini tetap menjadi opsi realistis apabila pendanaan untuk gereja baru belum mencukupi dalam waktu dekat.</p>', 'Denah eksisting gereja saat ini', '1776210433_1.jpg', 'desain', '2026-04-14 23:47:13', 0),
(23, 'Alternatif 2: Pengembangan Gereja Lama Menjadi 3 Lantai', '<p style=\"text-align: justify;\">Alternatif ini memanfaatkan lokasi gereja lama saat ini, yang sudah dikenal umat dan menjadi pusat pelaksanaan kegiatan stasi. Pengembangan dilakukan secara vertikal hingga tiga lantai yang dirancang mencakup:</p><p style=\"text-align: justify;\">a.	Lantai 1 Ruang Aula dengan beberapa kantor pendukung.</p><p style=\"text-align: justify;\">b.	Lantai 2 Ruang Ibadah Utama.</p><p style=\"text-align: justify;\">c.	Lantai 3 Ruang Balkon yang menunjang ibadah di ruang utama.</p><p style=\"text-align: justify;\">Namun, terdapat tantangan utama yaitu keterbatasan area parkir, serta potensi gangguan aktivitas ibadah selama pembangunan berlangsung. Struktur gereja yang sudah tua juga membutuhkan penguatan tambahan agar mampu menopang konstruksi bertingkat. Meskipun demikian, alternatif ini tetap menjadi opsi realistis apabila pendanaan untuk gereja baru belum mencukupi dalam waktu dekat.</p>', 'Denah Lantai 1 dalam rencana renovasi gereja lama sebagai alternatif 2', '1776210434_2.jpg', 'desain', '2026-04-14 23:47:14', 1),
(24, 'Alternatif 2: Pengembangan Gereja Lama Menjadi 3 Lantai', '<p style=\"text-align: justify;\">Alternatif ini memanfaatkan lokasi gereja lama saat ini, yang sudah dikenal umat dan menjadi pusat pelaksanaan kegiatan stasi. Pengembangan dilakukan secara vertikal hingga tiga lantai yang dirancang mencakup:</p><p style=\"text-align: justify;\">a.	Lantai 1 Ruang Aula dengan beberapa kantor pendukung.</p><p style=\"text-align: justify;\">b.	Lantai 2 Ruang Ibadah Utama.</p><p style=\"text-align: justify;\">c.	Lantai 3 Ruang Balkon yang menunjang ibadah di ruang utama.</p><p style=\"text-align: justify;\">Namun, terdapat tantangan utama yaitu keterbatasan area parkir, serta potensi gangguan aktivitas ibadah selama pembangunan berlangsung. Struktur gereja yang sudah tua juga membutuhkan penguatan tambahan agar mampu menopang konstruksi bertingkat. Meskipun demikian, alternatif ini tetap menjadi opsi realistis apabila pendanaan untuk gereja baru belum mencukupi dalam waktu dekat.</p>', 'Denah Lantai 2 dalam rencana renovasi gereja lama sebagai alternatif 2', '1776210434_3.jpg', 'desain', '2026-04-14 23:47:14', 2),
(25, 'Alternatif 2: Pengembangan Gereja Lama Menjadi 3 Lantai', '<p style=\"text-align: justify;\">Alternatif ini memanfaatkan lokasi gereja lama saat ini, yang sudah dikenal umat dan menjadi pusat pelaksanaan kegiatan stasi. Pengembangan dilakukan secara vertikal hingga tiga lantai yang dirancang mencakup:</p><p style=\"text-align: justify;\">a.	Lantai 1 Ruang Aula dengan beberapa kantor pendukung.</p><p style=\"text-align: justify;\">b.	Lantai 2 Ruang Ibadah Utama.</p><p style=\"text-align: justify;\">c.	Lantai 3 Ruang Balkon yang menunjang ibadah di ruang utama.</p><p style=\"text-align: justify;\">Namun, terdapat tantangan utama yaitu keterbatasan area parkir, serta potensi gangguan aktivitas ibadah selama pembangunan berlangsung. Struktur gereja yang sudah tua juga membutuhkan penguatan tambahan agar mampu menopang konstruksi bertingkat. Meskipun demikian, alternatif ini tetap menjadi opsi realistis apabila pendanaan untuk gereja baru belum mencukupi dalam waktu dekat.</p>', 'Denah Lantai 3 dalam rencana renovasi gereja lama sebagai alternatif 2', '1776210434_4.jpg', 'desain', '2026-04-14 23:47:14', 3),
(26, 'Alternatif 2: Pengembangan Gereja Lama Menjadi 3 Lantai', '<p style=\"text-align: justify;\">Alternatif ini memanfaatkan lokasi gereja lama saat ini, yang sudah dikenal umat dan menjadi pusat pelaksanaan kegiatan stasi. Pengembangan dilakukan secara vertikal hingga tiga lantai yang dirancang mencakup:</p><p style=\"text-align: justify;\">a.	Lantai 1 Ruang Aula dengan beberapa kantor pendukung.</p><p style=\"text-align: justify;\">b.	Lantai 2 Ruang Ibadah Utama.</p><p style=\"text-align: justify;\">c.	Lantai 3 Ruang Balkon yang menunjang ibadah di ruang utama.</p><p style=\"text-align: justify;\">Namun, terdapat tantangan utama yaitu keterbatasan area parkir, serta potensi gangguan aktivitas ibadah selama pembangunan berlangsung. Struktur gereja yang sudah tua juga membutuhkan penguatan tambahan agar mampu menopang konstruksi bertingkat. Meskipun demikian, alternatif ini tetap menjadi opsi realistis apabila pendanaan untuk gereja baru belum mencukupi dalam waktu dekat.</p>', 'Ilustrasi potongan dalam rencana renovasi gereja lama sebagai alternatif 2', '1776210435_5.jpg', 'desain', '2026-04-14 23:47:15', 4),
(27, 'Kewajiban Pembangunan: Perbaikan Mimbar Gereja', '<div style=\"text-align: justify;\">Sebagai bentuk tanggung jawab pastoral dan liturgis yang mendesak, umat berkewajiban untuk segera memperbaiki bagian mimbar gereja lama. Perbaikan ini bertujuan untuk merenovasi mimbar yang saat ini sering kali basah karena atap yang bocor dan adanya tiang yang menggangu pandangan ke arah mimbar. Tindakan ini merupakan langkah awal untuk menjamin keamanan, kenyamanan, dan kualitas perayaan Ekaristi dalam masa transisi perencanaan pembangunan gereja yang baru.</div>', 'Ilustrasi desain renovasi mimbar gereja lama tampak depan', '1776210905_6.jpg', 'desain', '2026-04-14 23:55:05', 0),
(28, 'Kewajiban Pembangunan: Perbaikan Mimbar Gereja', '<div style=\"text-align: justify;\">Sebagai bentuk tanggung jawab pastoral dan liturgis yang mendesak, umat berkewajiban untuk segera memperbaiki bagian mimbar gereja lama. Perbaikan ini bertujuan untuk merenovasi mimbar yang saat ini sering kali basah karena atap yang bocor dan adanya tiang yang menggangu pandangan ke arah mimbar. Tindakan ini merupakan langkah awal untuk menjamin keamanan, kenyamanan, dan kualitas perayaan Ekaristi dalam masa transisi perencanaan pembangunan gereja yang baru.</div>', 'Ilustrasi desain renovasi mimbar gereja lama tampak samping', '1776210906_7.jpg', 'desain', '2026-04-14 23:55:06', 1),
(29, 'Kewajiban Pembangunan: Perbaikan Mimbar Gereja', '<div style=\"text-align: justify;\">Sebagai bentuk tanggung jawab pastoral dan liturgis yang mendesak, umat berkewajiban untuk segera memperbaiki bagian mimbar gereja lama. Perbaikan ini bertujuan untuk merenovasi mimbar yang saat ini sering kali basah karena atap yang bocor dan adanya tiang yang menggangu pandangan ke arah mimbar. Tindakan ini merupakan langkah awal untuk menjamin keamanan, kenyamanan, dan kualitas perayaan Ekaristi dalam masa transisi perencanaan pembangunan gereja yang baru.</div>', 'Ilustrasi desain renovasi mimbar gereja lama tampak samping', '1776210906_8.jpg', 'desain', '2026-04-14 23:55:06', 2);

-- --------------------------------------------------------

--
-- Table structure for table `informasi_gereja`
--

CREATE TABLE `informasi_gereja` (
  `id_info` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `konten` text NOT NULL,
  `tipe` enum('sejarah','letak','umat','tabel_umat','rencana_pembangunan') DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `informasi_gereja`
--

INSERT INTO `informasi_gereja` (`id_info`, `judul`, `konten`, `tipe`, `gambar`, `updated_at`) VALUES
(7, 'Sejarah Gereja Katolik Stasi St. Laurensius Simpang Selayang', '<p style=\"text-align: justify; \">Berdasarkan data sejarah diperoleh dari arsip Gereja Katolik Stasi St. Laurensius dan wawancara dengan pengurus Dewan Pastoral Stasi, Stasi Santo Laurensius Simpang Selayang berdiri sejak tahun 1966, di tengah situasi umat Katolik yang masih sangat terbatas di wilayah tersebut. Pada masa awal berdirinya, umat belum memiliki gereja sendiri sehingga kegiatan peribadatan dan perayaan Ekaristi harus dilaksanakan di gedung SD Negeri 060971 Medan. Meski jumlah umat saat itu hanya sekitar 38 kepala keluarga (KK), semangat mereka untuk memiliki tempat ibadah sendiri begitu besar. Dengan semangat gotong royong dan bimbingan para imam dari tarekat Ordo Fratrum Minorum Conventualium (OFM Conv.), umat mulai merintis pembangunan gereja sederhana sebagai pusat kehidupan iman mereka.</p>\r\n<p style=\"text-align: justify; \">Pada tahun 1968, umat berhasil mendirikan gereja semi permanen berukuran 9 × 12 meter dengan struktur setengah batu dan setengah kayu. Bangunan sederhana ini menjadi simbol persatuan dan harapan bagi umat Katolik di Simpang Selayang. Seiring waktu, jumlah umat terus bertambah pesat: tahun 1978 mencapai 180 KK, lalu meningkat menjadi 280 KK pada tahun 1989. Peningkatan ini mendorong beberapa kali renovasi besar di bawah bimbingan Pastor Antonio Murru, OFM Conv. dan Pastor Antonio Razoli, OFM Conv., hingga gereja mampu menampung sekitar 600 umat.</p>\r\n<p style=\"text-align: justify; \">Namun, perkembangan umat tidak berhenti di situ. Berdasarkan data tahun 2017, jumlah umat telah mencapai 446 KK (±1.875 jiwa) dan terus meningkat setiap tahun, hingga mencapai 555 KK pada tahun 2024. Pertumbuhan yang begitu pesat menyebabkan kapasitas gereja lama sudah tidak lagi memadai untuk menampung seluruh umat, terutama saat perayaan Ekaristi Minggu dan hari raya besar Gereja Katolik. Selain keterbatasan kapasitas, kondisi fisik bangunan juga sudah tidak sesuai dengan kebutuhan pastoral dan pelayanan kategorial masa kini.</p>\r\n<p style=\"text-align: justify; \">Melihat kenyataan tersebut, umat bersama para pemimpin Gereja memandang perlunya pembangunan Gereja Katolik Santo Laurensius yang baru sebagai sarana ibadah yang layak, aman, dan representatif. Panitia Pembangunan Gereja pun dibentuk untuk menindaklanjuti rencana ini. Berkat kerja sama dan komitmen seluruh umat, telah berhasil diperoleh lahan seluas ±2.100 m² di Jl. Bunga Pancur IX, Pokok Mangga, Medan, yang akan dijadikan lokasi pembangunan gereja baru beserta fasilitas pendukungnya.</p>\r\n<p style=\"text-align: justify; \">Pembangunan gereja baru ini tidak hanya menjawab kebutuhan ruang ibadah, tetapi juga menjadi langkah strategis dalam mendukung rencana pengembangan Stasi Santo Laurensius menjadi paroki mandiri di masa depan. Gereja ini diharapkan menjadi pusat pelayanan rohani, pembinaan iman, serta kegiatan sosial dan pendidikan umat. Gereja juga memiliki cita-cita besar jangka panjang untuk mendirikan sekolah Katolik di lingkungan yang sama. Dengan demikian, pembangunan Gereja Katolik Santo Laurensius Simpang Selayang merupakan kebutuhan mendesak dan bentuk nyata semangat iman umat dalam membangun Gereja Allah yang hidup, tempat umat bersekutu, berdoa, dan melayani dalam kasih Kristus.</p>', 'sejarah', NULL, '2026-04-25 22:48:25'),
(8, 'Letak Administratif dan Hubungan Paroki', '<p style=\"text-align: justify; \">Stasi Santo Laurensius merupakan salah satu stasi di bawah Paroki St. Fransiskus Assisi (Padang Bulan). Paroki ini mencakup 6 stasi, diataranya St. Paulus Pasar Baru, St. Fransiskus Assisi Pasar 6, St. Petrus Sp. Kuala, St. Yosep Gedung Johor, St. Theresia Simalingkar, dan St. Laurensius Simpang Selayang. Gereja Katolik Santo Laurensius saat ini secara administratif beralamat di Jl. Jamin Ginting No.20, Kemenangan Tani, Kec. Medan Tuntungan, Kota Medan, Sumatera Utara 20135.</p>\r\n<p style=\"text-align: justify; \">Gereja ini memiliki 5 wilayah dengan total 18 lingkungan:</p>\r\n<ul>\r\n<li style=\"text-align: justify; \"><strong>Wilayah 1 St. Filipus:</strong> Lingkungan St. Monika, St. Maximilianus Kolbe, St. Agustinus (Pales sekitarnya)</li>\r\n<li style=\"text-align: justify; \"><strong>Wilayah 2 St. Matius:</strong> Lingkungan St. Fransiskus Xaverius, St. Antonius, St. Elisabeth, St.Theresia (Pokok Mangga Jl. Jamin Ginting)</li>\r\n<li style=\"text-align: justify; \"><strong>Wilayah 3 St. Nikodemus:</strong> Lingkungan St. Petrus, St. Thomas, St. Carolus (Simpang Selayang)</li>\r\n<li style=\"text-align: justify; \"><strong>Wilayah 4 St. Yakobus:</strong> Lingkungan St. Paulus, St. Padre Pio, St. Anna (Jl. Bunga Rinte)</li>\r\n<li style=\"text-align: justify; \"><strong>Wilayah 5 St. Markus:</strong> Lingkungan St. Yoseph, St. Bonaventura, St. Pius, St. Maria, St. Martha (Pencawan hingga Laucih)</li>\r\n</ul>', 'letak', '1777157649_gereja.png', '2026-04-25 22:54:09'),
(9, 'Jumlah Umat dan Struktur Komunitas', '<p style=\"text-align: justify; \">Berdasarkan data historis yang tercatat, Stasi Santo Laurensius menunjukkan pertumbuhan terus-menerus sejak didirikan (1966). Data yang tercantum menunjukkan jumlah keluarga di tahun 2024 sebanyak 555 KK. Pertumbuhan umat di Gereja Katolik Santo Laurensius menunjukkan pertumbuhan linear dengan proyeksi jumlah umat di tahun 2030 dan 2050 secara berurutan mencapai 595 dan 764 KK (estimasi berdasarkan tren pertumbuhan historis).</p>\r\n<p style=\"text-align: justify; \">Hierarki organisasi dalam lingkup Stasi Gereja Santo Laurensius mencakup Dewan Pastoral Stasi (DPS) yang diketuai oleh Bapak Pedoman Sembiring dengan wakil ketua Bapak Petrus Mamanta Sembiring; Sekretaris Bapak Junias Sitepu dengan wakil sekretaris Ibu Helena Lumban Gaol; Bendahara Ibu Eni Sentiana Br. Sembiring dengan wakil sekretaris Ibu Rukun Br. Sembiring. Dalam DPS terdapat 5 bidang menangani panca tugas gereja, diantaranya: Liturgi, Kerygma, Koinonia, Diakonia, dan Martyria. Dibawah DPS ada Dewan Pastoral Lingkungan (DPL) yang ada di masing-masing lingkungan untuk mengoraganisir kegiatan-kegiatan yang dilaksanakan di setiap lingkungan.</p>\r\n<p style=\"text-align: justify; \">Selain itu ada beberapa komunitas umat yang diorganisir oleh Koinonia, diantaranya: Bina Iman Anak (BIA), Bina Iman Remaja (BIR), Orang Muda Katolik (OMK), Persekutuan Ibu-Ibu Katolik (PIK), Persekutuan Bapak-Bapak Katolik (PBK), St. Dunscotus (lansia), dan kelompok doa.</p>', 'umat', NULL, '2026-04-25 22:51:12'),
(10, 'Data Pertumbuhan Umat (Tabel)', '<table class=\"table table-bordered\">\r\n<thead>\r\n<tr><th>Tahun</th><th>Jumlah umat (KK)</th></tr>\r\n</thead>\r\n<tbody>\r\n<tr><td>1968</td><td>38</td></tr>\r\n<tr><td>1978</td><td>180</td></tr>\r\n<tr><td>1989</td><td>280</td></tr>\r\n<tr><td>2017</td><td>446</td></tr>\r\n<tr><td>2020</td><td>508</td></tr>\r\n<tr><td>2021</td><td>522</td></tr>\r\n<tr><td>2022</td><td>529</td></tr>\r\n<tr><td>2023</td><td>543</td></tr>\r\n<tr><td>2024</td><td>555</td></tr>\r\n</tbody>\r\n</table>\r\n<p><small>Persamaan tren linear: y = 8.4389x - 16535.8858</small></p>', 'tabel_umat', NULL, '2026-04-06 13:21:51'),
(11, 'Rencana Pembangunan Gereja', '<div class=\"mb-4\">\r\n        <h4 class=\"fw-bold text-primary\"><i class=\"fas fa-church me-2\"></i> A. Kewajiban Pembangunan: Perbaikan Mimbar Gereja</h4>\r\n        <p class=\"text-muted\" style=\"text-align: justify; \">Sebagai bentuk tanggung jawab pastoral dan liturgis yang mendesak, umat berkewajiban untuk segera memperbaiki bagian mimbar gereja lama. Perbaikan ini bertujuan untuk merenovasi mimbar yang saat ini sering kali basah karena atap yang bocor dan adanya tiang yang mengganggu pandangan ke arah mimbar. Tindakan ini merupakan langkah awal untuk menjamin keamanan, kenyamanan, dan kualitas perayaan Ekaristi dalam masa transisi perencanaan pembangunan gereja yang baru.</p>\r\n    </div>\r\n    <h4 class=\"fw-bold text-primary\"><i class=\"fas fa-route me-2\"></i> B. Alternatif Pengembangan Gereja</h4>\r\n    <p>Dalam upaya menjawab kebutuhan umat yang terus bertambah dan pelayanan pastoral yang semakin berkembang di Stasi Santo Laurensius, Panitia Pembangunan bersama Pastor Paroki menetapkan dua alternatif rencana utama, yaitu:</p>\r\n    <ul class=\"mb-4\">\r\n        <li><strong>Alternatif 1:</strong> Pembangunan Gereja Baru</li>\r\n        <li><strong>Alternatif 2:</strong> Pengembangan Gereja Lama Menjadi 3 Lantai</li>\r\n    </ul>\r\n    <div class=\"row g-4\">\r\n        <div class=\"col-md-6\">\r\n            <div class=\"card h-100 border-0 bg-light rounded-4 p-3\">\r\n                <h5 class=\"fw-bold text-secondary\"><i class=\"fas fa-church me-2\"></i> Alternatif 1: Pembangunan Gereja Baru</h5>\r\n                <p style=\"text-align: justify; \">Alternatif ini merupakan kelanjutan dari rencana pembangunan sebelumnya, di mana umat telah berhasil memperoleh sebidang tanah seluas ±2.100 m² di Jl. Bunga Pancur IX sebagai lokasi gereja baru. Gereja baru akan dirancang sebagai tempat ibadah utama dengan kapasitas yang lebih besar, aman, serta sesuai standar liturgi Gereja Katolik. Pembangunan di atas lahan baru juga memungkinkan kegiatan ibadah terus berlangsung di gereja lama tanpa gangguan selama proses konstruksi. Selain ruang misa utama, akan disiapkan fasilitas pendukung seperti:</p>\r\n                <ul><li>Aula serbaguna</li><li>Ruang BIA &amp; kategorial</li><li>Sakristi dan pastoran kecil</li><li>Area parkir yang memadai</li></ul>\r\n            </div>\r\n        </div>\r\n        <div class=\"col-md-6\">\r\n            <div class=\"card h-100 border-0 bg-light rounded-4 p-3\">\r\n                <h5 class=\"fw-bold text-secondary\"><i class=\"fas fa-building me-2\"></i> Alternatif 2: Pengembangan Gereja Lama Menjadi 3 Lantai</h5>\r\n                <p style=\"text-align: justify; \">Alternatif ini mempertimbangkan pemanfaatan lahan yang ada dengan membangun vertikal. Gereja lama akan direnovasi total menjadi bangunan 3 lantai yang dapat menampung lebih banyak umat serta menyediakan ruang serbaguna, ruang kategorial, dan pastoran. Namun, opsi ini memerlukan relokasi sementara umat selama proses pembangunan. Panitia masih mengkaji kelayakan teknis dan biaya dari alternatif ini.</p>\r\n            </div>\r\n        </div>\r\n    </div>\r\n    <div class=\"alert alert-info mt-4\">\r\n        <i class=\"fas fa-info-circle me-2\"></i> Panitia Pembangunan terus berkoordinasi dengan Pastor Paroki dan umat untuk memutuskan alternatif terbaik. Doa dan dukungan seluruh umat sangat diharapkan.\r\n    </div>', 'rencana_pembangunan', NULL, '2026-04-29 02:07:24');

-- --------------------------------------------------------

--
-- Table structure for table `pembangunan`
--

CREATE TABLE `pembangunan` (
  `id_pembangunan` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kebutuhan_dana` decimal(15,2) DEFAULT 0.00,
  `dana_terpakai` decimal(15,2) DEFAULT 0.00,
  `progres_persen` int(3) DEFAULT 0,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `status` enum('direncanakan','berlangsung','selesai') NOT NULL DEFAULT 'direncanakan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembangunan`
--

INSERT INTO `pembangunan` (`id_pembangunan`, `judul`, `deskripsi`, `kebutuhan_dana`, `dana_terpakai`, `progres_persen`, `gambar`, `created_at`, `tgl_mulai`, `tgl_selesai`, `urutan`, `status`) VALUES
(5, 'I. Tahap Perencanaan & Persiapan', 'Pembentukan Panitia Pembangunan, Studi Kelayakan, Desain, Pengurusan Perizinan, Penggalangan Dana Awal.', 50000000.00, 1000000.00, 2, NULL, '2026-04-08 08:08:27', '2026-07-01', '2026-10-31', 1, 'berlangsung'),
(6, 'II. Tahap Fondasi & Struktur', 'Pembersihan Lahan, Peletakan Batu Pertama, Pengerjaan Pondasi, Pengerjaan Struktur Utama.', 150000000.00, 0.00, 0, NULL, '2026-04-08 08:08:27', '2026-11-01', '2027-02-28', 2, 'direncanakan'),
(18, 'III. Tahap Konstruksi Fisik', 'Pemasangan Dinding, Rangka Atap, Lantai & Plafon, Instalasi Listrik & Air.', 200000000.00, 0.00, 0, '', '2026-04-26 21:20:54', '2027-03-01', '2027-08-31', 3, 'direncanakan'),
(19, 'IV. Tahap Finishing & Peresmian', 'Pengecatan & Pembersihan, Pemasangan Furnitur, Pemberkatan Gereja.', 100000000.00, 0.00, 0, '', '2026-04-26 21:24:37', '2027-09-01', '2027-12-13', 4, 'direncanakan');

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `deskripsi` varchar(150) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_pembangunan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengeluaran`
--

INSERT INTO `pengeluaran` (`id`, `tanggal`, `deskripsi`, `kategori`, `jumlah`, `bukti`, `created_at`, `id_pembangunan`) VALUES
(11, '2026-04-26', 'Semen 2 Sak', 'Material', 1000000.00, '', '2026-04-26 01:03:08', 5);

--
-- Triggers `pengeluaran`
--
DELIMITER $$
CREATE TRIGGER `after_pengeluaran_delete` AFTER DELETE ON `pengeluaran` FOR EACH ROW BEGIN
    DECLARE total DECIMAL(15,2);
    DECLARE kebutuhan DECIMAL(15,2);
    DECLARE progres_calc INT;

    IF OLD.id_pembangunan IS NOT NULL THEN
        SELECT SUM(jumlah) INTO total FROM pengeluaran WHERE id_pembangunan = OLD.id_pembangunan;
        IF total IS NULL THEN SET total = 0; END IF;
        SELECT kebutuhan_dana INTO kebutuhan FROM pembangunan WHERE id_pembangunan = OLD.id_pembangunan;
        IF kebutuhan IS NULL OR kebutuhan <= 0 THEN SET progres_calc = 0;
        ELSE SET progres_calc = ROUND((total / kebutuhan) * 100); IF progres_calc > 100 THEN SET progres_calc = 100; END IF;
        END IF;
        UPDATE pembangunan SET dana_terpakai = total, progres_persen = progres_calc WHERE id_pembangunan = OLD.id_pembangunan;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_pengeluaran_insert` AFTER INSERT ON `pengeluaran` FOR EACH ROW BEGIN
    DECLARE total DECIMAL(15,2);
    DECLARE kebutuhan DECIMAL(15,2);
    DECLARE progres_calc INT;

    IF NEW.id_pembangunan IS NOT NULL THEN
        SELECT SUM(jumlah) INTO total FROM pengeluaran WHERE id_pembangunan = NEW.id_pembangunan;
        IF total IS NULL THEN SET total = 0; END IF;

        SELECT kebutuhan_dana INTO kebutuhan FROM pembangunan WHERE id_pembangunan = NEW.id_pembangunan;
        IF kebutuhan IS NULL OR kebutuhan <= 0 THEN SET progres_calc = 0;
        ELSE
            SET progres_calc = ROUND((total / kebutuhan) * 100);
            IF progres_calc > 100 THEN SET progres_calc = 100; END IF;
        END IF;

        UPDATE pembangunan
        SET dana_terpakai = total,
            progres_persen = progres_calc
        WHERE id_pembangunan = NEW.id_pembangunan;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_pengeluaran_update` AFTER UPDATE ON `pengeluaran` FOR EACH ROW BEGIN
    DECLARE total DECIMAL(15,2);
    DECLARE kebutuhan DECIMAL(15,2);
    DECLARE progres_calc INT;

    -- Jika id_pembangunan berubah, update kegiatan lama
    IF OLD.id_pembangunan IS NOT NULL AND OLD.id_pembangunan != NEW.id_pembangunan THEN
        SELECT SUM(jumlah) INTO total FROM pengeluaran WHERE id_pembangunan = OLD.id_pembangunan;
        IF total IS NULL THEN SET total = 0; END IF;
        SELECT kebutuhan_dana INTO kebutuhan FROM pembangunan WHERE id_pembangunan = OLD.id_pembangunan;
        IF kebutuhan IS NULL OR kebutuhan <= 0 THEN SET progres_calc = 0;
        ELSE SET progres_calc = ROUND((total / kebutuhan) * 100); IF progres_calc > 100 THEN SET progres_calc = 100; END IF;
        END IF;
        UPDATE pembangunan SET dana_terpakai = total, progres_persen = progres_calc WHERE id_pembangunan = OLD.id_pembangunan;
    END IF;

    -- Update kegiatan baru
    IF NEW.id_pembangunan IS NOT NULL THEN
        SELECT SUM(jumlah) INTO total FROM pengeluaran WHERE id_pembangunan = NEW.id_pembangunan;
        IF total IS NULL THEN SET total = 0; END IF;
        SELECT kebutuhan_dana INTO kebutuhan FROM pembangunan WHERE id_pembangunan = NEW.id_pembangunan;
        IF kebutuhan IS NULL OR kebutuhan <= 0 THEN SET progres_calc = 0;
        ELSE SET progres_calc = ROUND((total / kebutuhan) * 100); IF progres_calc > 100 THEN SET progres_calc = 100; END IF;
        END IF;
        UPDATE pembangunan SET dana_terpakai = total, progres_persen = progres_calc WHERE id_pembangunan = NEW.id_pembangunan;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `total_donasi`
--

CREATE TABLE `total_donasi` (
  `id_total` int(11) NOT NULL,
  `total_terkumpul` decimal(15,2) DEFAULT 0.00,
  `target_dana` decimal(15,2) DEFAULT 500000000.00,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `total_donasi`
--

INSERT INTO `total_donasi` (`id_total`, `total_terkumpul`, `target_dana`, `last_update`) VALUES
(1, 0.00, 500000000.00, '2026-04-03 13:27:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `donasi`
--
ALTER TABLE `donasi`
  ADD PRIMARY KEY (`id_donasi`);

--
-- Indexes for table `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id_galeri`);

--
-- Indexes for table `informasi_gereja`
--
ALTER TABLE `informasi_gereja`
  ADD PRIMARY KEY (`id_info`),
  ADD UNIQUE KEY `unique_tipe` (`tipe`);

--
-- Indexes for table `pembangunan`
--
ALTER TABLE `pembangunan`
  ADD PRIMARY KEY (`id_pembangunan`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pembangunan` (`id_pembangunan`);

--
-- Indexes for table `total_donasi`
--
ALTER TABLE `total_donasi`
  ADD PRIMARY KEY (`id_total`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `donasi`
--
ALTER TABLE `donasi`
  MODIFY `id_donasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id_galeri` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `informasi_gereja`
--
ALTER TABLE `informasi_gereja`
  MODIFY `id_info` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pembangunan`
--
ALTER TABLE `pembangunan`
  MODIFY `id_pembangunan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `total_donasi`
--
ALTER TABLE `total_donasi`
  MODIFY `id_total` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `pengeluaran_ibfk_1` FOREIGN KEY (`id_pembangunan`) REFERENCES `pembangunan` (`id_pembangunan`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
