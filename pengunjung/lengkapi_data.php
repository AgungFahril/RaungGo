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
        echo "<script>alert('Harap lengkapi semua kolom wajib.');</script>";
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
            echo "<script>alert('Data diri berhasil disimpan!'); window.location='sop.php';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan data: " . addslashes($conn->error) . "');</script>";
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
        label {
            font-weight: 600;
            margin-top: 10px;
            display: block;
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
        }
        button:hover {
            background-color: #2e7d32;
        }
    </style>
</head>
<body>

<header>
    <?php include '../includes/navbar_user.php'; ?>
</header>

<main class="container">
    <h2>📝 Lengkapi Data Diri Pendaki</h2>

    <form method="POST">
        <label>Nama Lengkap</label>
        <input type="text" value="<?= htmlspecialchars($user['nama'] ?? '') ?>" disabled>

        <label>Email</label>
        <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>

        <label>NIK</label>
        <input type="text" name="nik" required>

        <label>Tempat Lahir</label>
        <input type="text" name="tempat_lahir" required>

        <label>Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" required>

        <label>Jenis Kelamin</label>
        <select name="jenis_kelamin" required>
            <option value="">-- Pilih --</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
        </select>

        <label>Kewarganegaraan</label>
        <input type="text" name="kewarganegaraan" required>

        <label>Alamat Lengkap</label>
        <textarea name="alamat" rows="3" required></textarea>

        <label>No. HP</label>
        <input type="text" name="no_hp" required>

        <label>No. Telepon Darurat</label>
        <input type="text" name="no_darurat" required>

        <label>Hubungan dengan Kontak Darurat</label>
        <input type="text" name="hubungan_darurat" required>

        <label>Provinsi</label>
        <input type="text" name="provinsi" required>

        <label>Kabupaten</label>
        <input type="text" name="kabupaten" required>

        <label>Kecamatan</label>
        <input type="text" name="kecamatan" required>

        <label>Kelurahan</label>
        <input type="text" name="kelurahan" required>

        <button type="submit">Simpan dan Lanjut ke SOP</button>
    </form>
</main>

<footer style="text-align:center; margin:20px 0; color:#555;">
    &copy; 2025 Tahura Raden Soerjo. All Rights Reserved.
</footer>

</body>
</html>
