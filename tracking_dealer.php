<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_group']) || $_SESSION['id_group'] != 1) die("Akses Ditolak!");

$id_dealer_session = $_SESSION['id_user'];
$my_history = mysqli_query($conn, "SELECT * FROM Transaksi_Pengajuan WHERE id_user_dealer='$id_dealer_session' ORDER BY id_pengajuan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tracking & Arsip - JKL Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { bca: { DEFAULT: '#0066AE', dark: '#004f87', light: '#e5f0f7' } } }
            }
        }
    </script>
</head>
<body class="bg-slate-50 min-h-screen">
    <!-- Navbar dengan Menu Navigasi Dual-Link -->
    <nav class="bg-bca shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-8">
                    <div class="flex items-center text-white font-bold text-lg">
                        <i class="fa-solid fa-car mr-2"></i> JKL E-Credit
                    </div>
                    <!-- Submenu Navigasi -->
                    <div class="hidden md:flex space-x-4">
                        <a href="form_pengajuan.php" class="text-bca-light hover:bg-bca-dark/50 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">
                            <i class="fa-solid fa-file-pen mr-1.5"></i> Form Pengajuan
                        </a>
                        <a href="tracking_dealer.php" class="bg-bca-dark text-white px-3 py-2 rounded-md text-sm font-semibold shadow-inner">
                            <i class="fa-solid fa-timeline mr-1.5"></i> Tracking & Arsip
                        </a>
                    </div>
                </div>
                <div class="text-white text-sm flex items-center space-x-4">
                    <span>Halo, <b class="font-semibold"><?= $_SESSION['username']; ?></b></span>
                    <a href="logout.php" class="text-red-300 hover:text-white transition"><i class="fa-solid fa-power-off"></i> Logout</a>
                </div>
            </div>
            <!-- Submenu Mobile View -->
            <div class="md:hidden flex space-x-2 pb-3">
                <a href="form_pengajuan.php" class="text-bca-light px-3 py-1.5 rounded text-xs">Form Pengajuan</a>
                <a href="tracking_dealer.php" class="bg-bca-dark text-white px-3 py-1.5 rounded text-xs font-semibold">Tracking & Arsip</a>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto mt-8 px-4 pb-12">
        <div class="flex justify-between items-end mb-6">
            <div>
                <h2 class="text-2xl font-bold text-bca-dark">Tracking & Arsip Pengajuan Saya</h2>
                <p class="text-slate-500 text-sm">Pantau status siklus hidup dokumen pembiayaan konsumen yang Anda ajukan.</p>
            </div>
            <a href="form_pengajuan.php" class="bg-bca hover:bg-bca-dark text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
                <i class="fa-solid fa-plus mr-1.5"></i> Buat Pengajuan Baru
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
            <div class="bg-slate-800 px-6 py-4 text-white flex justify-between items-center">
                <h3 class="font-bold text-base"><i class="fa-solid fa-folder-tree mr-2"></i> Riwayat End-to-End Status Lifecycle</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                            <th class="py-3.5 px-6">ID Transaksi</th>
                            <th class="py-3.5 px-6">Konsumen</th>
                            <th class="py-3.5 px-6">Kendaraan & Harga</th>
                            <th class="py-3.5 px-6">DP</th>
                            <th class="py-3.5 px-6 text-center">Status Lifecycle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $has_my = false;
                        while($row_m = mysqli_fetch_assoc($my_history)): 
                            $has_my = true;
                            $badge_color = 'bg-amber-100 text-amber-800 border-amber-200';
                            if($row_m['status_pengajuan']=='Contract Signed') $badge_color = 'bg-blue-100 text-blue-800 border-blue-200';
                            if($row_m['status_pengajuan']=='Disbursed') $badge_color = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                            if($row_m['status_pengajuan']=='Rejected') $badge_color = 'bg-red-100 text-red-800 border-red-200';
                        ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="py-4 px-6 font-mono text-slate-500">#<?= $row_m['id_pengajuan']; ?></td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800"><?= $row_m['nama_konsumen']; ?></div>
                                <div class="text-xs text-slate-400 font-mono"><?= $row_m['nik']; ?></div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-medium text-slate-800"><?= $row_m['kendaraan']; ?></div>
                                <div class="text-xs text-slate-500">Rp <?= number_format($row_m['harga_kendaraan'],0,',','.'); ?></div>
                            </td>
                            <td class="py-4 px-6"><?= $row_m['dp_persen']; ?>%</td>
                            <td class="py-4 px-6 text-center">
                                <span class="<?= $badge_color; ?> px-3 py-1 rounded-full text-xs font-bold border shadow-sm inline-block">
                                    <?= $row_m['status_pengajuan']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if(!$has_my): ?>
                        <tr><td colspan="5" class="py-16 text-center text-slate-400">
                            <i class="fa-solid fa-folder-open text-4xl mb-3 block text-slate-300"></i>
                            Belum ada riwayat pengajuan. Silakan buat form pengajuan baru.
                        </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>