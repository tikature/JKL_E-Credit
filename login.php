<?php
session_start();
require 'koneksi.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; 
    $query = mysqli_query($conn, "SELECT * FROM Master_User WHERE username='$username' AND password='$password' AND status_user=1");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['id_group'] = $data['id_group'];

        if ($data['id_group'] == 1) header("Location: form_pengajuan.php");
        elseif ($data['id_group'] == 3) header("Location: dashboard_atasan.php");
        elseif ($data['id_group'] == 4) header("Location: dashboard_admin.php");
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JKL Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { bca: { DEFAULT: '#0066AE', dark: '#004f87', light: '#e5f0f7' } }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-2xl rounded-2xl p-8 max-w-md w-full border border-gray-100">
        <div class="text-center mb-8">
            <div class="bg-bca text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fa-solid fa-car-side text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-bca">JKL E-Credit</h2>
            <p class="text-slate-500 text-sm mt-1">Sistem Pengajuan Kredit Terpadu</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded text-sm">
                <i class="fa-solid fa-circle-exclamation mr-2"></i><?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-5 relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-user text-gray-400"></i>
                    </div>
                    <input type="text" name="username" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bca focus:border-bca outline-none transition-all" placeholder="Masukkan username">
                </div>
            </div>
            <div class="mb-6 relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" name="password" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bca focus:border-bca outline-none transition-all" placeholder="••••••••">
                </div>
            </div>
            <button type="submit" name="login" class="w-full bg-bca hover:bg-bca-dark text-white font-semibold py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fa-solid fa-right-to-bracket mr-2"></i>Masuk Sistem
            </button>
        </form>

        <!-- INFORMASI AKUN DEMO SEJAJAR (MINIMALIS) -->
        <div class="mt-6 pt-4 border-t border-slate-100">
            <div class="text-[11px] text-slate-500 flex flex-wrap justify-center items-center gap-x-2 gap-y-1">
                <span class="font-semibold text-slate-700"><i class="fa-solid fa-circle-info"></i> Demo (Pass: 123456)</span>
                <span class="text-slate-300">|</span>
                <span>Dealer: <b class="text-bca font-mono">dealer01</b></span>
                <span class="text-slate-300">|</span>
                <span>Atasan: <b class="text-bca font-mono">bos_atasan</b></span>
                <span class="text-slate-300">|</span>
                <span>Admin: <b class="text-bca font-mono">admin_cair</b></span>
            </div>
        </div>
        
    </div>
</body>
</html>