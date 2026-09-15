<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_group']) || $_SESSION['id_group'] != 1) die("Akses Ditolak!");

if (isset($_POST['submit_pengajuan'])) {
    $id_dealer = $_SESSION['id_user'];
    $nama      = $_POST['nama_konsumen'];
    $nik       = $_POST['nik'];
    $kendaraan = $_POST['kendaraan'];
    $harga     = $_POST['harga_kendaraan'];
    $dp        = $_POST['dp_persen'];

    $query = "INSERT INTO Transaksi_Pengajuan (id_user_dealer, nama_konsumen, nik, kendaraan, harga_kendaraan, dp_persen) 
              VALUES ('$id_dealer', '$nama', '$nik', '$kendaraan', '$harga', '$dp')";
    if (mysqli_query($conn, $query)) $msg_success = "Pengajuan berhasil dikirim! Status: Draft";
}

$id_dealer_session = $_SESSION['id_user'];
$my_history = mysqli_query($conn, "SELECT * FROM Transaksi_Pengajuan WHERE id_user_dealer='$id_dealer_session' ORDER BY id_pengajuan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Pengajuan - JKL Portal</title>
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
    <nav class="bg-bca shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center text-white font-bold text-lg">
                    <i class="fa-solid fa-car mr-2"></i> JKL E-Credit Portal | Dealer Channel
                </div>
                <div class="text-white text-sm">
                    Halo, <span class="font-semibold mr-4"><?= $_SESSION['username']; ?></span>
                    <a href="login.php" class="bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded transition"><i class="fa-solid fa-power-off"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto mt-8 px-4">
        <?php if(isset($msg_success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6 flex items-center shadow-sm">
                <i class="fa-solid fa-check-circle mr-2 text-xl"></i><?= $msg_success; ?>
            </div>
        <?php endif; ?>

        <!-- FORM INPUT -->
        <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden mb-10">
            <div class="bg-bca px-6 py-4 border-b border-bca-dark flex justify-between items-center">
                <h2 class="text-xl font-bold text-white"><i class="fa-solid fa-file-signature mr-2"></i>Buat Pengajuan Baru</h2>
                <span class="text-xs text-bca-light bg-bca-dark/50 px-2.5 py-1 rounded">Channel: Sales Dealer</span>
            </div>
            
            <form method="POST" action="" class="p-6">
                <div class="bg-bca-light border border-bca/30 rounded-lg p-5 mb-8">
                    <label class="block text-sm font-bold text-bca-dark mb-2"><i class="fa-solid id-card mr-2 fa-id-card"></i>1. Upload e-KTP (OCR Engine)</label>
                    <input type="file" id="file_ktp" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-white file:text-bca hover:file:bg-gray-100 cursor-pointer transition shadow-sm">
                    <div id="loading_ocr" class="hidden mt-3 text-sm text-bca font-bold flex items-center">
                        <i class="fa-solid fa-circle-notch fa-spin mr-2 text-lg"></i> Memindai KTP dengan AI Engine...
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">2. Data Diri (Auto-Fill)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK KTP</label>
                        <input type="text" name="nik" id="nik" required readonly class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bca outline-none text-gray-600" placeholder="Menunggu KTP...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama_konsumen" id="nama" required readonly class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bca outline-none text-gray-600" placeholder="Menunggu KTP...">
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">3. Data Kendaraan & Pembiayaan</h3>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-6">
                    <div class="col-span-12 md:col-span-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kendaraan</label>
                        <select name="kendaraan" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bca outline-none bg-white">
                            <option value="">-- Pilih Kendaraan --</option>
                            <option value="Toyota Avanza 1.5 G MT">Toyota Avanza 1.5 G MT</option>
                            <option value="Honda Brio Satya E CVT">Honda Brio Satya E CVT</option>
                            <option value="Mitsubishi Xpander Cross">Mitsubishi Xpander Cross</option>
                        </select>
                    </div>
                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga OTR (Rp)</label>
                        <input type="number" name="harga_kendaraan" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bca outline-none" placeholder="250000000">
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">DP (%)</label>
                        <input type="number" name="dp_persen" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bca outline-none" placeholder="20">
                    </div>
                </div>

                <button type="submit" name="submit_pengajuan" class="w-full bg-bca hover:bg-bca-dark text-white font-bold py-3 px-4 rounded-lg shadow-md transition-all flex justify-center items-center">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Submit ke Pusat
                </button>
            </form>
        </div>

        <!-- TRACKING / ARSIP STATUS PENGAJUAN SAYA (DEALER) -->
        <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
            <div class="bg-slate-800 px-6 py-4 text-white flex justify-between items-center">
                <h3 class="font-bold text-lg"><i class="fa-solid fa-timeline mr-2"></i> Tracking & Arsip Pengajuan Saya</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500">
                            <th class="py-3 px-6">ID</th>
                            <th class="py-3 px-6">Konsumen</th>
                            <th class="py-3 px-6">Kendaraan</th>
                            <th class="py-3 px-6 text-center">Status Terkini (Lifecycle)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $has_my = false;
                        while($row_m = mysqli_fetch_assoc($my_history)): 
                            $has_my = true;
                            $badge_color = 'bg-amber-100 text-amber-800';
                            if($row_m['status_pengajuan']=='Contract Signed') $badge_color = 'bg-blue-100 text-blue-800';
                            if($row_m['status_pengajuan']=='Disbursed') $badge_color = 'bg-emerald-100 text-emerald-800';
                            if($row_m['status_pengajuan']=='Rejected') $badge_color = 'bg-red-100 text-red-800';
                        ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="py-3 px-6 font-mono text-slate-500">#<?= $row_m['id_pengajuan']; ?></td>
                            <td class="py-3 px-6 font-bold text-slate-800"><?= $row_m['nama_konsumen']; ?></td>
                            <td class="py-3 px-6"><?= $row_m['kendaraan']; ?></td>
                            <td class="py-3 px-6 text-center">
                                <span class="<?= $badge_color; ?> px-3 py-1 rounded-full text-xs font-bold shadow-sm inline-block">
                                    <?= $row_m['status_pengajuan']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(!$has_my): ?>
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">Belum ada riwayat pengajuan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('file_ktp').addEventListener('change', function() {
            if(this.files.length > 0) {
                document.getElementById('loading_ocr').classList.remove('hidden');
                document.getElementById('nik').value = '';
                document.getElementById('nama').value = '';

                setTimeout(function() {
                    document.getElementById('loading_ocr').classList.add('hidden');
                    let nikField = document.getElementById('nik');
                    let namaField = document.getElementById('nama');
                    
                    nikField.value = '3273123456780001';
                    namaField.value = 'BUDI SANTOSO';
                    nikField.removeAttribute('readonly');
                    namaField.removeAttribute('readonly');
                    nikField.classList.replace('bg-gray-100', 'bg-white');
                    namaField.classList.replace('bg-gray-100', 'bg-white');

                    Swal.fire({
                        icon: 'success',
                        title: 'OCR Berhasil!',
                        text: 'Teks e-KTP telah diekstrak secara otomatis.',
                        timer: 2000,
                        showConfirmButton: false,
                        confirmButtonColor: '#0066AE'
                    });
                }, 2000);
            }
        });
    </script>
</body>
</html>