<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_group']) || $_SESSION['id_group'] != 4) die("Akses Ditolak!");

if (isset($_GET['cairkan_id'])) {
    $id = $_GET['cairkan_id'];
    mysqli_query($conn, "UPDATE Transaksi_Pengajuan SET status_pengajuan='Disbursed' WHERE id_pengajuan='$id'");
    $msg = "API Triggered: Dana ID #$id berhasil ditransfer Host-to-Host!";
}

// Menghitung statistik untuk KPI Widget Finance
$q_ready = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(harga_kendaraan - (harga_kendaraan*dp_persen/100)) as nominal FROM Transaksi_Pengajuan WHERE status_pengajuan='Contract Signed'");
$stat_ready = mysqli_fetch_assoc($q_ready);

$q_done = mysqli_query($conn, "SELECT COUNT(*) as total FROM Transaksi_Pengajuan WHERE status_pengajuan='Disbursed'");
$stat_done = mysqli_fetch_assoc($q_done)['total'];

// Filter tab view: 'queue' (siap cair) atau 'archive' (riwayat disbursed)
$view_mode = isset($_GET['view']) ? $_GET['view'] : 'queue';
$where_status = ($view_mode == 'archive') ? "status_pengajuan='Disbursed'" : "status_pengajuan='Contract Signed'";
$query = mysqli_query($conn, "SELECT * FROM Transaksi_Pengajuan WHERE $where_status");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Finance Dashboard - JKL Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { bca: { DEFAULT: '#0066AE', dark: '#004f87', light: '#e5f0f7' } } }
            }
        }
    </script>
</head>
<body class="bg-slate-50 min-h-screen">
    <nav class="bg-bca shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center text-white">
                <div class="font-bold text-lg"><i class="fa-solid fa-vault mr-2"></i> Backoffice Finance</div>
                <div class="text-sm">
                    Halo, <span class="font-bold mr-4"><?= $_SESSION['username']; ?></span>
                    <a href="login.php" class="text-red-300 hover:text-white transition"><i class="fa-solid fa-power-off"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto mt-8 px-4 mb-10">
        <div class="flex justify-between items-end mb-6">
            <div>
                <h2 class="text-2xl font-bold text-bca-dark">Pusat Pencairan & Arsip Keuangan</h2>
                <p class="text-slate-500 text-sm">Kelola pencairan dana host-to-host dan jejak arsip transfer dealer.</p>
            </div>
            <div class="text-sm font-semibold text-bca bg-bca-light px-3 py-1.5 rounded-lg border border-bca/20">
                <i class="fa-regular fa-calendar mr-1"></i> <?= date('d M Y'); ?>
            </div>
        </div>
        
        <?php if(isset($msg)): ?>
            <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 px-4 py-3 rounded-lg mb-6 shadow-sm font-medium flex items-center">
                <i class="fa-solid fa-money-bill-transfer mr-2 text-lg"></i><?= $msg; ?>
            </div>
        <?php endif; ?>

        <!-- KPI SUMMARY CARDS FINANCE -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center border-l-4 border-l-bca">
                <div class="bg-bca-light text-bca w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500 font-medium">Antrean Siap Cair</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?= $stat_ready['total'] ?? 0; ?> <span class="text-xs font-normal text-slate-400">Kontrak</span></h3>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center border-l-4 border-l-amber-500">
                <div class="bg-amber-100text-amber-600 w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-inner text-amber-600">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500 font-medium">Total Nilai Pending Disburse</p>
                    <h3 class="text-xl font-bold text-slate-800">Rp <?= number_format($stat_ready['nominal'] ?? 0,0,',','.'); ?></h3>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center border-l-4 border-l-emerald-500">
                <div class="bg-emerald-100 text-emerald-600 w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500 font-medium">Total Sukses Dicairkan (Arsip)</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?= $stat_done; ?> <span class="text-xs font-normal text-emerald-600">Transaksi</span></h3>
                </div>
            </div>
        </div>

        <!-- TAB NAVIGATION (Queue vs Arsip) & SEARCH -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div class="flex items-center gap-2">
                    <a href="?view=queue" class="px-4 py-2 rounded-lg text-sm font-bold transition <?= $view_mode=='queue' ? 'bg-bca text-white shadow' : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-100'; ?>">
                        <i class="fa-solid fa-clock-rotate-left mr-1"></i> Antrean Cair
                    </a>
                    <a href="?view=archive" class="px-4 py-2 rounded-lg text-sm font-bold transition <?= $view_mode=='archive' ? 'bg-bca text-white shadow' : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-100'; ?>">
                        <i class="fa-solid fa-box-archive mr-1"></i> Arsip Disbursed
                    </a>
                </div>
                
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" placeholder="Cari ID/Konsumen..." class="pl-8 pr-4 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-bca outline-none">
                        <i class="fa-solid fa-search absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-bca-light/50 text-bca-dark border-b border-bca/20 text-sm">
                            <th class="py-4 px-6 font-bold">ID Transaksi</th>
                            <th class="py-4 px-6 font-bold">Konsumen</th>
                            <th class="py-4 px-6 font-bold">Kendaraan</th>
                            <th class="py-4 px-6 font-bold">Nilai Pencairan</th>
                            <th class="py-4 px-6 font-bold text-center">Status Legal / Arsip</th>
                            <th class="py-4 px-6 font-bold text-center">Eksekusi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php 
                        $has_row = false;
                        while($row = mysqli_fetch_assoc($query)): 
                            $has_row = true;
                            $nilai = $row['harga_kendaraan'] - ($row['harga_kendaraan'] * $row['dp_persen'] / 100);
                        ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="py-4 px-6 font-mono text-slate-500">#<?= $row['id_pengajuan']; ?></td>
                            <td class="py-4 px-6 font-bold text-slate-800"><?= $row['nama_konsumen']; ?><br><span class="text-xs font-normal text-slate-400"><?= $row['nik']; ?></span></td>
                            <td class="py-4 px-6"><?= $row['kendaraan']; ?></td>
                            <td class="py-4 px-6 font-bold text-red-600">Rp <?= number_format($nilai,0,',','.'); ?></td>
                            <td class="py-4 px-6 text-center">
                                <?php if($row['status_pengajuan']=='Disbursed'): ?>
                                    <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200"><i class="fa-solid fa-check-double mr-1"></i>Disbursed (Arsip)</span>
                                <?php else: ?>
                                    <span class="bg-bca-light text-bca px-3 py-1 rounded-full text-xs font-bold border border-bca/30"><i class="fa-solid fa-file-signature mr-1"></i>E-Sign Valid</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <?php if($row['status_pengajuan']=='Contract Signed'): ?>
                                    <button onclick="confirmCair(<?= $row['id_pengajuan']; ?>)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold shadow-md transition text-xs">
                                        <i class="fa-solid fa-money-bill-wave mr-1"></i> Cairkan via API
                                    </button>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic"><i class="fa-solid fa-lock mr-1"></i>Tersimpan di Arsip</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if(!$has_row): ?>
                        <tr><td colspan="6" class="py-12 text-center text-slate-400"><i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>Tidak ada data pada mode peninjauan ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function confirmCair(id) {
            Swal.fire({
                title: 'Konfirmasi Pencairan',
                text: "Sistem akan menembak API Bank untuk mentransfer dana ke Dealer dan memindahkan data ke arsip disbursed. Lanjutkan?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0066AE',
                cancelButtonColor: '#dc2626',
                confirmButtonText: 'Ya, Transfer Dana!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "?cairkan_id=" + id;
                }
            })
        }
    </script>
</body>
</html>