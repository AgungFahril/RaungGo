<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../backend/koneksi.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Ambil data user
$stmt = $conn->prepare("SELECT nama, email, foto_profil FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Ambil data pendaki
$stmt = $conn->prepare("SELECT nik, alamat, no_hp FROM pendaki_detail WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pendaki_data = $result->fetch_assoc();
$stmt->close();

// Buat folder uploads jika belum ada
$uploads_dir = '../uploads/profil';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}

// Proses update profil
if (isset($_POST['update_profil'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $nik = trim($_POST['nik']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $foto_profil = $user_data['foto_profil'] ?? null;

    // Handle file upload
    if (!empty($_FILES['foto_profil']['name'])) {
        $file = $_FILES['foto_profil'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $file['size'] <= 5242880) {
            $new_filename = $user_id . '_' . time() . '.' . $ext;
            $upload_path = '../uploads/profil/' . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                if ($foto_profil && file_exists('../uploads/profil/' . $foto_profil)) {
                    unlink('../uploads/profil/' . $foto_profil);
                }
                $foto_profil = $new_filename;
            } else {
                $message = 'Gagal upload foto profil.';
                $message_type = 'error';
            }
        } else {
            $message = 'Format foto tidak didukung atau ukuran terlalu besar (max 5MB).';
            $message_type = 'error';
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, foto_profil = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $nama, $email, $foto_profil, $user_id);

        if ($stmt->execute()) {
            $stmt2 = $conn->prepare("UPDATE pendaki_detail SET nik = ?, no_hp = ?, alamat = ? WHERE user_id = ?");
            $stmt2->bind_param("sssi", $nik, $no_hp, $alamat, $user_id);
            $stmt2->execute();
            $stmt2->close();

            $_SESSION['nama'] = $nama;
            $_SESSION['email'] = $email;
            $message = 'Profil berhasil diperbarui!';
            $message_type = 'success';
        } else {
            $message = 'Gagal memperbarui profil.';
            $message_type = 'error';
        }
        $stmt->close();
    }
}

// Proses ubah password
if (isset($_POST['ubah_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $password_baru_confirm = $_POST['password_baru_confirm'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pwd_data = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($password_lama, $pwd_data['password'])) {
        $message = 'Password lama salah!';
        $message_type = 'error';
    } elseif ($password_baru !== $password_baru_confirm) {
        $message = 'Password baru tidak cocok!';
        $message_type = 'error';
    } elseif (strlen($password_baru) < 6) {
        $message = 'Password baru minimal 6 karakter!';
        $message_type = 'error';
    } else {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $password_hash, $user_id);
        if ($stmt->execute()) {
            $message = 'Password berhasil diubah!';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengubah password.';
            $message_type = 'error';
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
<title>Edit Profil - Pendakian Gunung Raung</title>
<style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif}
    body{background:#f4f6f9;overflow-x:hidden;animation:fadeIn 1s ease}
    @keyframes fadeIn{from{opacity:0;transform:translateY(15px)}to{opacity:1;transform:translateY(0)}}
    .dashboard-container{display:flex;min-height:100vh}
    .sidebar{width:260px;background:linear-gradient(180deg,#16a34a,#15803d);color:#fff;padding:30px 0;box-shadow:3px 0 15px rgba(0,0,0,0.1);position:fixed;height:100vh}
    .sidebar-header{display:flex;align-items:center;padding:0 25px 25px;border-bottom:1px solid rgba(255,255,255,0.2)}
    .user-avatar{width:60px;height:60px;border-radius:50%;background:#1e9b52;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:bold;margin-right:15px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.2)}
    .user-avatar img{width:100%;height:100%;object-fit:cover}
    .user-info h3{font-size:15px;font-weight:600}
    .user-status{font-size:12px;color:#ffd700}
    .sidebar-nav{margin-top:25px}
    .nav-item{display:flex;align-items:center;padding:14px 25px;color:#fff;text-decoration:none;opacity:0.9;transition:all 0.3s;border-left:4px solid transparent}
    .nav-item:hover{background:rgba(255,255,255,0.1);opacity:1;border-left:4px solid #ffd700;transform:translateX(4px)}
    .nav-item.active{background:rgba(255,255,255,0.15);border-left:4px solid #ffd700}
    .main-content{flex:1;margin-left:260px;padding:50px;animation:fadeIn 0.6s ease}
    .page-title{font-size:26px;color:#15803d;font-weight:700;margin-bottom:8px}
    .page-subtitle{color:#777;margin-bottom:30px}
    .alert{padding:12px 18px;border-radius:8px;margin-bottom:25px;font-weight:500;animation:fadeIn 0.5s ease}
    .alert.success{background:#d4edda;color:#155724;border-left:5px solid #16a34a}
    .alert.error{background:#f8d7da;color:#721c24;border-left:5px solid #e74c3c}
    .form-section{background:#fff;padding:35px;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.08);margin-bottom:35px;transition:transform 0.3s}
    .form-section:hover{transform:translateY(-3px)}
    .form-section h2{color:#16a34a;font-size:20px;margin-bottom:25px;display:flex;align-items:center;gap:8px}
    .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px}
    .form-group label{font-weight:600;font-size:14px;margin-bottom:6px;color:#333}
    .form-group input,.form-group textarea{padding:11px;border:1px solid #ddd;border-radius:8px;font-size:14px;width:100%;transition:all 0.3s}
    .form-group input:focus,.form-group textarea:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,0.1)}
    .form-group textarea{min-height:80px;resize:vertical}
    .button-group{display:flex;gap:12px;margin-top:25px}
    .btn{padding:11px 25px;border:none;border-radius:8px;font-weight:600;cursor:pointer;transition:all 0.3s}
    .btn-primary{background:linear-gradient(135deg,#16a34a,#15803d);color:white}
    .btn-primary:hover{transform:translateY(-2px);box-shadow:0 4px 15px rgba(22,163,74,0.3)}
    .btn-secondary{background:#f1f1f1;color:#333}
    .btn-secondary:hover{background:#e4e4e4}
    .profile-photo-section{display:flex;align-items:flex-end;gap:25px;margin-bottom:30px;border-bottom:1px solid #eee;padding-bottom:25px}
    .profile-photo-preview img{width:130px;height:130px;border-radius:10px;object-fit:cover;border:3px solid #16a34a;box-shadow:0 4px 15px rgba(0,0,0,0.1)}
    .no-photo{width:130px;height:130px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:50px;color:#aaa;background:#f0f0f0;border:2px dashed #ccc}
    .file-input-label{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#16a34a;color:#fff;padding:11px 20px;border-radius:8px;cursor:pointer;font-weight:600;transition:all 0.3s}
    .file-input-label:hover{transform:translateY(-2px);box-shadow:0 4px 10px rgba(22,163,74,0.3)}
    input[type=file]{display:none}
    .file-info{font-size:12px;color:#777;margin-top:8px}
    @media(max-width:768px){.dashboard-container{flex-direction:column}.sidebar{width:100%;height:auto;position:relative}.main-content{margin-left:0;padding:25px}}
</style>
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <?php if (!empty($user_data['foto_profil']) && file_exists('../uploads/profil/' . $user_data['foto_profil'])): ?>
                    <img src="/ProjekSemester3/uploads/profil/<?php echo htmlspecialchars($user_data['foto_profil']); ?>" alt="Foto Profil">
                <?php else: ?>
                    <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <h3><?php echo $_SESSION['nama']; ?></h3>
                <div class="user-status">● Online</div>
            </div>
        </div>
        <div class="sidebar-nav">
<<<<<<< HEAD
            <a href="profil.php" class="nav-item">👤 Profil Pribadi</a>
            <a href="edit_profil.php" class="nav-item active">✏️ Edit Profil</a>
            <a href="booking.php" class="nav-item">📅 Booking</a>
            <a href="../pengunjung/dashboard.php?tab=transaksi" class="nav-item">📊 Transaksi</a>
            <a href="../backend/logout.php" class="nav-item" style="background:#e53935;margin-top:10px;">🚪 Logout</a>
=======
            <a href="dashboard.php" class="nav-item">🏠 Dashboard</a>
            <a href="edit_profil.php" class="nav-item active">👤 Edit Profil</a>
            <a href="booking.php" class="nav-item">📅 Booking</a>
            <a href="../pengunjung/dashboard.php?tab=transaksi" class="nav-item">📊 Transaksi</a>
            <a href="../backend/logout.php" class="nav-item">🚪 Logout</a>
>>>>>>> main
        </div>
    </div>

    <div class="main-content">
        <h1 class="page-title">Edit Profil</h1>
        <p class="page-subtitle">Perbarui informasi pribadi dan keamanan akun Anda</p>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="form-section">
            <h2>📋 Data Pribadi</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="profile-photo-section">
                    <div class="profile-photo-preview">
                        <?php if (!empty($user_data['foto_profil']) && file_exists('../uploads/profil/' . $user_data['foto_profil'])): ?>
                            <img src="/ProjekSemester3/uploads/profil/<?php echo htmlspecialchars($user_data['foto_profil']); ?>" alt="Foto Profil">
                        <?php else: ?><div class="no-photo">📷</div><?php endif; ?>
                    </div>
                    <div>
                        <label class="file-input-label" for="foto_profil">📁 Pilih Foto</label>
                        <input type="file" id="foto_profil" name="foto_profil" accept="image/*">
                        <div class="file-info">Format: JPG, JPEG, PNG, GIF | Maks: 5MB</div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($user_data['nama'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>NIK *</label>
                        <input type="text" name="nik" value="<?php echo htmlspecialchars($pendaki_data['nik'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor HP</label>
                        <input type="text" name="no_hp" value="<?php echo htmlspecialchars($pendaki_data['no_hp'] ?? ''); ?>">
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Alamat</label>
                        <textarea name="alamat"><?php echo htmlspecialchars($pendaki_data['alamat'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="button-group">
                    <button type="submit" name="update_profil" class="btn btn-primary">💾 Simpan Perubahan</button>
                    <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>

        <div class="form-section">
            <h2>🔒 Ubah Password</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Password Lama *</label>
                        <input type="password" name="password_lama" required>
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Password Baru *</label>
                        <input type="password" name="password_baru" required>
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Konfirmasi Password Baru *</label>
                        <input type="password" name="password_baru_confirm" required>
                    </div>
                </div>
                <div class="button-group">
                    <button type="submit" name="ubah_password" class="btn btn-primary">🔄 Ubah Password</button>
                    <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<<<<<<< HEAD

<script>
// Real-time photo preview saat user memilih file
document.getElementById('foto_profil').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.querySelector('.profile-photo-preview');
            
            // Hapus elemen no-photo jika ada
            const noPhoto = preview.querySelector('.no-photo');
            if (noPhoto) {
                noPhoto.remove();
            }
            
            // Hapus img lama jika ada
            const oldImg = preview.querySelector('img');
            if (oldImg) {
                oldImg.remove();
            }
            
            // Buat dan tampilkan img baru
            const img = document.createElement('img');
            img.src = event.target.result;
            img.alt = 'Foto Profil Preview';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
=======
</body>
</html>
>>>>>>> main
