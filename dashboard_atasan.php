<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_group']) || $_SESSION['id_group'] != 3) die("Akses Ditolak!");

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['action'] == 'approve') {
        mysqli_query($conn, "UPDATE Transaksi_Pengajuan SET status_pengajuan='Contract Signed' WHERE id_pengajuan='$id'");
        $msg = ["type" => "success", "text" => "Pengajuan #$id disetujui! Sistem membuat PDF & E-Sign."];
    } elseif ($_GET['action'] == 'reject') {
        mysqli_query($conn, "UPDATE Transaksi_Pengajuan SET status_pengajuan='Rejected' WHERE id_pengajuan='$id'");
        $msg = ["type" => "error", "text" => "Pengajuan #$id ditolak."];
    }
}
$query = mysqli_query($conn, "SELECT * FROM Transaksi_Pengajuan WHERE status_pengajuan='Draft'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Approval</title>
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
<body class="bg-slate-100 min-h-screen">
    <nav class="bg-bca shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center text-white">
                <div class="font-bold text-lg"><i class="fa-solid fa-briefcase mr-2"></i> E-Approval Atasan</div>
                <div class="text-sm">Halo, <span class="font-bold mr-4"><?= $_SESSION['username']; ?></span><a href="login.php" class="text-red-300 hover:text-red-200"><i class="fa-solid fa-power-off"></i></a></div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto mt-8 px-4">
        <h2 class="text-2xl font-bold text-bca-dark mb-6">Menunggu Persetujuan</h2>
        
        <?php if(isset($msg)): ?>
            <div class="bg-<?= $msg['type'] == 'success' ? 'emerald' : 'red'; ?>-100 text-<?= $msg['type'] == 'success' ? 'emerald' : 'red'; ?>-800 px-4 py-3 rounded mb-4 shadow-sm border-l-4 border-<?= $msg['type'] == 'success' ? 'emerald' : 'red'; ?>-500">
                <?= $msg['text']; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-sm">
                        <th class="py-4 px-6 font-semibold">ID</th>
                        <th class="py-4 px-6 font-semibold">Konsumen</th>
                        <th class="py-4 px-6 font-semibold">Kendaraan</th>
                        <th class="py-4 px-6 font-semibold text-center">Status</th>
                        <th class="py-4 px-6 font-semibold text-center">1-Click Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php while($row = mysqli_fetch_assoc($query)): ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                        <td class="py-4 px-6 text-slate-500 font-mono">#<?= $row['id_pengajuan']; ?></td>
                        <td class="py-4 px-6">
                            <div class="font-bold text-slate-800"><?= $row['nama_konsumen']; ?></div>
                            <div class="text-xs text-slate-500"><?= $row['nik']; ?></div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="font-medium"><?= $row['kendaraan']; ?></div>
                            <div class="text-bca font-bold text-xs mt-1">Rp <?= number_format($row['harga_kendaraan'],0,',','.'); ?> (DP: <?= $row['dp_persen']; ?>%)</div>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-xs font-bold shadow-sm"><i class="fa-regular fa-clock mr-1"></i><?= $row['status_pengajuan']; ?></span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <a href="?action=approve&id=<?= $row['id_pengajuan']; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded bg-emerald-100 text-emerald-600 hover:bg-emerald-500 hover:text-white transition mr-2" title="Approve">
                                <i class="fa-solid fa-check"></i>
                            </a>
                            <a href="?action=reject&id=<?= $row['id_pengajuan']; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded bg-red-100 text-red-600 hover:bg-red-500 hover:text-white transition" title="Reject">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>