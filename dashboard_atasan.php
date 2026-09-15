<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_group']) || $_SESSION['id_group'] != 3) die("Akses Ditolak!");

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['action'] == 'approve') {
        mysqli_query($conn, "UPDATE Transaksi_Pengajuan SET status_pengajuan='Contract Signed' WHERE id_pengajuan='$id'");
        $msg = ["type" => "emerald", "text" => "Pengajuan #$id disetujui! Triggered Auto-PDF & E-Sign."];
    } elseif ($_GET['action'] == 'reject') {
        mysqli_query($conn, "UPDATE Transaksi_Pengajuan SET status_pengajuan='Rejected' WHERE id_pengajuan='$id'");
        $msg = ["type" => "red", "text" => "Pengajuan #$id telah ditolak dan diarsipkan."];
    }
}

// KPI Metrics Atasan
$q_draft = mysqli_query($conn, "SELECT COUNT(*) as total FROM Transaksi_Pengajuan WHERE status_pengajuan='Draft'");
$stat_draft = mysqli_fetch_assoc($q_draft)['total'];

$q_processed = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(harga_kendaraan) as val FROM Transaksi_Pengajuan WHERE status_pengajuan IN ('Contract Signed', 'Disbursed')");
$stat_proc = mysqli_fetch_assoc($q_processed);

$q_rej = mysqli_query($conn, "SELECT COUNT(*) as total FROM Transaksi_Pengajuan WHERE status_pengajuan='Rejected'");
$stat_rej = mysqli_fetch_assoc($q_rej)['total'];

// Mode Tab: 'queue' (Menunggu Approval) atau 'history' (Arsip Keputusan)
$view_mode = isset($_GET['view']) ? $_GET['view'] : 'queue';
$where_status = ($view_mode == 'history') 
    ? "status_pengajuan IN ('Contract Signed', 'Disbursed', 'Rejected')" 
    : "status_pengajuan='Draft'";
$query = mysqli_query($conn, "SELECT * FROM Transaksi_Pengajuan WHERE $where_status ORDER BY id_pengajuan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Approval Executive - JKL Portal</title>
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
<body class="bg-slate-50 min-h-screen pb-12">
    <!-- Navbar Corporate BCA Blue -->
    <nav class="bg-bca shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center text-white">
                <div class="font-bold text-lg"><i class="fa-solid fa-car mr-2"></i> JKL E-Credit Portal | E-Approval Executive Desk</div>
                <div class="text-sm">
                    <span class="mr-3 text-bca-light"><i class="fa-regular fa-bell"></i></span>
                    Halo, <span class="font-bold mr-4"><?= $_SESSION['username']; ?></span>
                    <a href="logout.php" class="text-red-300 hover:text-white transition"><i class="fa-solid fa-power-off"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto mt-8 px-4">
        <div class="flex justify-between items-end mb-6">
            <div>
                <h2 class="text-2xl font-bold text-bca-dark">Dashboard Persetujuan Kredit</h2>
                <p class="text-slate-500 text-sm">Credit scoring review, 1-click decision, dan audit trail keputusan.</p>
            </div>
            <div class="text-sm font-semibold text-bca bg-bca-light px-3 py-1.5 rounded-lg border border-bca/20">
                <i class="fa-regular fa-calendar mr-1"></i> <?= date('d M Y'); ?>
            </div>
        </div>
        
        <?php if(isset($msg)): ?>
            <div class="bg-<?= $msg['type']; ?>-100 border-l-4 border-<?= $msg['type']; ?>-500 text-<?= $msg['type']; ?>-800 px-4 py-3 rounded-lg mb-6 shadow-sm flex items-center">
                <i class="fa-solid fa-circle-info mr-2 text-lg"></i> <?= $msg['text']; ?>
            </div>
        <?php endif; ?>

        <!-- KPI SUMMARY CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center border-l-4 border-l-amber-500">
                <div class="bg-amber-100 text-amber-600 w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500 font-medium">Menunggu Approval</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?= $stat_draft; ?> <span class="text-xs font-normal text-slate-400">Berkas</span></h3>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center border-l-4 border-l-emerald-500">
                <div class="bg-emerald-100 text-emerald-600 w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500 font-medium">Disetujui / Lolos (Signed/Disbursed)</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?= $stat_proc['total'] ?? 0; ?> <span class="text-xs font-normal text-emerald-600">Kontrak</span></h3>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center border-l-4 border-l-red-500">
                <div class="bg-red-100 text-red-600 w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500 font-medium">Ditolak (Rejected)</p>
                    <h3 class="text-xl font-bold text-slate-800"><?= $stat_rej; ?> <span class="text-xs font-normal text-slate-400">Berkas</span></h3>
                </div>
            </div>
        </div>

        <!-- TAB NAVIGATION & SEARCH -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div class="flex items-center gap-2">
                    <a href="?view=queue" class="px-4 py-2 rounded-lg text-sm font-bold transition <?= $view_mode=='queue' ? 'bg-bca text-white shadow' : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-100'; ?>">
                        <i class="fa-solid fa-list-check mr-1"></i> Antrean Approval (<?= $stat_draft; ?>)
                    </a>
                    <a href="?view=history" class="px-4 py-2 rounded-lg text-sm font-bold transition <?= $view_mode=='history' ? 'bg-bca text-white shadow' : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-100'; ?>">
                        <i class="fa-solid fa-box-archive mr-1"></i> Arsip Keputusan
                    </a>
                </div>
                
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" placeholder="Cari nama/NIK..." class="pl-8 pr-4 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-bca outline-none">
                        <i class="fa-solid fa-search absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-bca-light/50 text-bca-dark border-b border-bca/20 text-sm">
                            <th class="py-4 px-6 font-bold">ID</th>
                            <th class="py-4 px-6 font-bold">Konsumen</th>
                            <th class="py-4 px-6 font-bold">Kendaraan & Pembiayaan</th>
                            <th class="py-4 px-6 font-bold text-center">Status Lifecycle</th>
                            <th class="py-4 px-6 font-bold text-center">Eksekusi / Audit Trail</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php 
                        $has_row = false;
                        while($row = mysqli_fetch_assoc($query)): 
                            $has_row = true;
                            $badge = 'bg-amber-100 text-amber-800 border-amber-200';
                            if($row['status_pengajuan']=='Contract Signed') $badge = 'bg-blue-100 text-blue-800 border-blue-200';
                            if($row['status_pengajuan']=='Disbursed') $badge = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                            if($row['status_pengajuan']=='Rejected') $badge = 'bg-red-100 text-red-800 border-red-200';
                        ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="py-4 px-6 text-slate-500 font-mono">#<?= $row['id_pengajuan']; ?></td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800"><?= $row['nama_konsumen']; ?></div>
                                <div class="text-xs text-slate-500"><?= $row['nik']; ?></div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-medium text-slate-800"><?= $row['kendaraan']; ?></div>
                                <div class="text-bca font-bold text-xs mt-1">Rp <?= number_format($row['harga_kendaraan'],0,',','.'); ?> (DP: <?= $row['dp_persen']; ?>%)</div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold border <?= $badge; ?> inline-block">
                                    <?= $row['status_pengajuan']; ?>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <?php if($view_mode == 'queue'): ?>
                                    <button onclick="confirmAction(<?= $row['id_pengajuan']; ?>, 'approve')" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition font-semibold text-xs shadow-sm mr-1">
                                        <i class="fa-solid fa-check mr-1"></i> Approve
                                    </button>
                                    <button onclick="confirmAction(<?= $row['id_pengajuan']; ?>, 'reject')" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700 transition font-semibold text-xs shadow-sm">
                                        <i class="fa-solid fa-xmark mr-1"></i> Reject
                                    </button>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic"><i class="fa-solid fa-lock mr-1"></i> Keputusan Terkunci / Arsip</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if(!$has_row): ?>
                        <tr><td colspan="5" class="py-12 text-center text-slate-400"><i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>Tidak ada data pada peninjauan ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function confirmAction(id, type) {
            let title = type === 'approve' ? 'Setujui Pengajuan?' : 'Tolak Pengajuan?';
            let text = type === 'approve' 
                ? 'Sistem akan merilis auto-generate PDF kontrak dan E-Sign link ke konsumen.' 
                : 'Pengajuan akan ditandai Rejected dan masuk arsip penolakan.';
            let confirmColor = type === 'approve' ? '#059669' : '#dc2626';
            
            Swal.fire({
                title: title,
                text: text,
                icon: type === 'approve' ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#64748b',
                confirmButtonText: type === 'approve' ? 'Ya, Approve!' : 'Ya, Reject!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?action=${type}&id=${id}&view=queue`;
                }
            })
        }
    </script>
</body>
</html>