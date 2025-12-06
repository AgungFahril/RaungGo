<?php
session_start();
include '../backend/koneksi.php';

// 🔒 Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=pengunjung/lengkapi_data.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Cek apakah pendaki sudah melengkapi data
$q = $conn->prepare("SELECT * FROM pendaki_detail WHERE user_id = ?");
$q->bind_param("i", $user_id);
$q->execute();
$res = $q->get_result();
$existing = $res->fetch_assoc();
$q->close();

// Jika sudah lengkap → langsung ke SOP
if ($existing) {
    header("Location: sop.php");
    exit;
}

// ✅ Ambil nama & email dari tabel users
$qUser = $conn->prepare("SELECT nama, email FROM users WHERE user_id = ?");
$qUser->bind_param("i", $user_id);
$qUser->execute();
$user = $qUser->get_result()->fetch_assoc();
$qUser->close();

// ✅ Simpan data jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik              = $_POST['nik'] ?? '';
    $tempat_lahir     = $_POST['tempat_lahir'] ?? '';
    $tanggal_lahir    = $_POST['tanggal_lahir'] ?? '';
    $jenis_kelamin    = $_POST['jenis_kelamin'] ?? '';
    $kewarganegaraan  = $_POST['kewarganegaraan'] ?? '';
    $alamat           = $_POST['alamat'] ?? '';
    $no_hp            = $_POST['no_hp'] ?? '';
    $no_darurat       = $_POST['no_darurat'] ?? '';
    $hubungan_darurat = $_POST['hubungan_darurat'] ?? '';
    $provinsi         = $_POST['provinsi'] ?? '';
    $kabupaten        = $_POST['kabupaten'] ?? '';
    $kecamatan        = $_POST['kecamatan'] ?? '';
    $kelurahan        = $_POST['kelurahan'] ?? '';
    $tanggal_update   = date('Y-m-d H:i:s');

    // 🔎 Validasi wajib
    if (empty($nik) || empty($tempat_lahir) || empty($tanggal_lahir) || empty($jenis_kelamin) || empty($alamat) || empty($no_hp)) {
        $_SESSION['alert_message'] = 'Harap lengkapi semua kolom wajib.';
        $_SESSION['alert_type'] = 'error';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO pendaki_detail (
                user_id, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, kewarganegaraan, alamat, 
                no_hp, no_darurat, hubungan_darurat, provinsi, kabupaten, kecamatan, kelurahan, tanggal_update
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "issssssssssssss",
            $user_id, $nik, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $kewarganegaraan,
            $alamat, $no_hp, $no_darurat, $hubungan_darurat, $provinsi, $kabupaten, $kecamatan, $kelurahan, $tanggal_update
        );

        if ($stmt->execute()) {
            $_SESSION['alert_message'] = 'Data diri berhasil disimpan!';
            $_SESSION['alert_type'] = 'success';
            echo "<script>window.location='sop.php';</script>";
            exit;
        } else {
            $_SESSION['alert_message'] = 'Gagal menyimpan data: ' . $conn->error;
            $_SESSION['alert_type'] = 'error';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lengkapi Data Diri Pendaki</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f6f3;
        }
        .container {
            width: 80%;
            margin: 40px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2e7d32;
            margin-bottom: 25px;
        }
        
        /* Alert Styles */
        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        .alert-error {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        .alert-success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        label {
            font-weight: 600;
            margin-top: 10px;
            display: block;
        }
        label .required {
            color: #dc3545;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            margin-top: 20px;
            background-color: #4caf50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #2e7d32;
        }
        
        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #1976d2;
            font-size: 16px;
        }
        .info-box p {
            margin: 0;
            color: #0d47a1;
            font-size: 14px;
        }
    </style>
</head>
<body>

<header>
    <?php include '../includes/navbar_user.php'; ?>
</header>

<main class="container">
    <h2>📝 Lengkapi Data Diri Pendaki</h2>

    <?php
    // Tampilkan alert jika ada
    if (isset($_SESSION['alert_message'])) {
        $alert_type = $_SESSION['alert_type'] ?? 'warning';
        echo '<div class="alert alert-' . $alert_type . '">';
        echo htmlspecialchars($_SESSION['alert_message']);
        echo '</div>';
        unset($_SESSION['alert_message']);
        unset($_SESSION['alert_type']);
    }
    ?>

    <div class="info-box">
        <h3>⚠️ Perhatian!</h3>
        <p>Anda harus melengkapi data diri terlebih dahulu sebelum dapat mengakses menu booking dan fitur lainnya. Pastikan semua data yang diisi benar dan akurat.</p>
    </div>

    <form method="POST">
        <label>Nama Lengkap</label>
        <input type="text" value="<?= htmlspecialchars($user['nama'] ?? '') ?>" disabled>

        <label>Email</label>
        <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>

        <label>NIK <span class="required">*</span></label>
        <input type="text" name="nik" placeholder="Masukkan NIK 16 digit" maxlength="16" required>

        <label>Tempat Lahir <span class="required">*</span></label>
        <input type="text" name="tempat_lahir" placeholder="Contoh: Jakarta" required>

        <label>Tanggal Lahir <span class="required">*</span></label>
        <input type="date" name="tanggal_lahir" required>

        <label>Jenis Kelamin <span class="required">*</span></label>
        <select name="jenis_kelamin" required>
            <option value="">-- Pilih --</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
        </select>

        <label>Kewarganegaraan <span class="required">*</span></label>
        <input type="text" name="kewarganegaraan" value="Indonesia" required>

        <label>Alamat Lengkap <span class="required">*</span></label>
        <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap sesuai KTP" required></textarea>

        <label>No. HP <span class="required">*</span></label>
        <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" required>

        <label>No. Telepon Darurat <span class="required">*</span></label>
        <input type="text" name="no_darurat" placeholder="08xxxxxxxxxx" required>

        <label>Hubungan dengan Kontak Darurat <span class="required">*</span></label>
        <input type="text" name="hubungan_darurat" placeholder="Contoh: Orang Tua, Saudara, Pasangan" required>

        <label>Provinsi <span class="required">*</span></label>
        <input type="text" name="provinsi" placeholder="Contoh: Jawa Timur" required>

        <label>Kabupaten <span class="required">*</span></label>
        <input type="text" name="kabupaten" placeholder="Contoh: Malang" required>

        <label>Kecamatan <span class="required">*</span></label>
        <input type="text" name="kecamatan" placeholder="Contoh: Lowokwaru" required>

        <label>Kelurahan <span class="required">*</span></label>
        <input type="text" name="kelurahan" placeholder="Contoh: Dinoyo" required>

        <button type="submit">💾 Simpan dan Lanjutkan</button>
    </form>
</main>

<footer style="text-align:center; margin:20px 0; color:#555;">
    &copy; 2025 Tahura Raden Soerjo. All Rights Reserved.
</footer>

</body>
</html>