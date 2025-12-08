<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../backend/koneksi.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT nama, email, foto_profil FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT nik, alamat, no_hp FROM pendaki_detail WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pendaki_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!is_dir('../uploads/profil')) mkdir('../uploads/profil', 0777, true);

if (isset($_POST['update_profil'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $nik = trim($_POST['nik']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $foto_profil = $user_data['foto_profil'] ?? null;

    if (!empty($_FILES['foto_profil']['name'])) {
        $file = $_FILES['foto_profil'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

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
            $fotoUrl = $foto_profil ? '../uploads/profil/' . $foto_profil : '';
            echo json_encode(['ok' => 1, 'msg' => 'Profil berhasil diperbarui!', 'foto' => $fotoUrl, 'nama' => $nama, 'email' => $email]);
            exit();
        } else {
            echo json_encode(['ok' => 0, 'msg' => 'Gagal memperbarui profil.']);
            exit();
        }
    } else {
        echo json_encode(['ok' => 0, 'msg' => $message]);
        exit();
    }
}

if (isset($_POST['ubah_password'])) {
    $pl = $_POST['password_lama'];
    $pb = $_POST['password_baru'];
    $pbc = $_POST['password_baru_confirm'];
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pwd_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!password_verify($pl, $pwd_data['password'])) echo json_encode(['ok' => 0, 'msg' => 'Password lama salah!']);
    elseif ($pb !== $pbc) echo json_encode(['ok' => 0, 'msg' => 'Password baru tidak cocok!']);
    elseif (strlen($pb) < 6) echo json_encode(['ok' => 0, 'msg' => 'Password minimal 6 karakter!']);
    else {
        $ph = password_hash($pb, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $ph, $user_id);
        $res = $stmt->execute();
        echo json_encode(['ok' => $res ? 1 : 0, 'msg' => $res ? 'Password berhasil diubah!' : 'Gagal mengubah password.']);
        $stmt->close();
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profil - Pendakian Gunung Raung</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}
@keyframes fadeIn{from{opacity:0;transform:translateY(15px)}to{opacity:1;transform:none}}
@keyframes slideIn{from{transform:translateX(-50px);opacity:0}to{transform:none;opacity:1}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.6}}

body{background:linear-gradient(135deg,#f5faf5,#e8f5e9);overflow-x:hidden;animation:fadeIn .8s ease;min-height:100vh}

.dashboard-container{display:flex;min-height:100vh}

/* SIDEBAR */
.sidebar{width:270px;background:linear-gradient(180deg,#2e7d32,#1b5e20);color:#fff;padding:35px 0;box-shadow:4px 0 20px rgba(0,0,0,0.2);position:fixed;height:100vh;animation:slideIn .8s ease;display:flex;flex-direction:column;overflow-y:auto;z-index:100}
.sidebar > div:first-child{flex:1}

.sidebar-header{display:flex;align-items:center;padding:0 25px 30px;border-bottom:1px solid rgba(255,255,255,0.1);cursor:pointer;transition:.3s}
.sidebar-header:hover{background:rgba(255,255,255,0.05)}

.user-avatar{width:65px;height:65px;border-radius:50%;background:#43a047;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;color:#fff;margin-right:15px;overflow:hidden;border:3px solid rgba(255,255,255,0.2);flex-shrink:0}
.user-avatar img{width:100%;height:100%;object-fit:cover}

.user-info h3{font-size:15px;margin-bottom:5px;font-weight:600}
.user-status{font-size:12px;color:#FFD700;display:flex;align-items:center;gap:6px}
.status-indicator{width:8px;height:8px;background:#FFD700;border-radius:50%;animation:pulse 2s infinite}

.sidebar-nav{margin-top:15px;display:flex;flex-direction:column;gap:5px}
.sidebar-actions{display:flex;flex-direction:column;gap:8px;border-top:1px solid rgba(255,255,255,0.15);padding:15px 0;margin-top:auto}

.nav-item{display:block;padding:13px 28px;color:rgba(255,255,255,0.85);text-decoration:none;font-weight:500;border-left:4px solid transparent;transition:all .3s}
.nav-item i{margin-right:10px;width:18px;text-align:center}
.nav-item:hover,.nav-item.active{background:rgba(255,255,255,0.1);border-left-color:#FFD700;color:#fff}

/* MAIN CONTENT */
.main-content{margin-left:270px;flex:1;padding:50px;animation:fadeIn .8s ease}

.page-header{margin-bottom:30px}
.page-header h1{font-size:28px;color:#1b5e20;margin-bottom:8px;font-weight:700}
.page-header p{color:#666;font-size:14px}

/* ALERT */
.alert{padding:14px 20px;border-radius:10px;margin-bottom:25px;font-weight:500;animation:fadeIn .5s ease;display:none}
.alert.success{background:#d4edda;color:#155724;border-left:5px solid#16a34a}
.alert.error{background:#f8d7da;color:#721c24;border-left:5px solid #e74c3c}

/* FORM SECTION */
.form-section{background:#fff;padding:35px;border-radius:14px;box-shadow:0 6px 24px rgba(0,0,0,0.08);margin-bottom:30px;transition:transform .3s}
.form-section:hover{transform:translateY(-3px)}
.form-section h2{color:#2e7d32;font-size:20px;margin-bottom:25px;display:flex;align-items:center;gap:10px;font-weight:700}

/* PROFILE PHOTO SECTION */
.profile-photo-section{display:flex;align-items:flex-end;gap:25px;margin-bottom:30px;padding-bottom:25px;border-bottom:2px solid#e8f5e9;flex-wrap:wrap}

.profile-photo-preview{flex-shrink:0}
.profile-photo-preview img{width:130px;height:130px;border-radius:12px;object-fit:cover;border:4px solid #2e7d32;box-shadow:0 4px 15px rgba(0,0,0,0.12)}

.no-photo{width:130px;height:130px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:50px;color:#aaa;background:#f5faf5;border:2px dashed #ccc}

.photo-upload-area{flex:1;min-width:200px}

.file-input-label{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(135deg,#2e7d32,#1b5e20);color:#fff;padding:12px 24px;border-radius:10px;cursor:pointer;font-weight:600;transition:all .3s;font-size:14px}
.file-input-label:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(46,125,50,0.3)}

input[type=file]{display:none}

.file-info{font-size:12px;color:#777;margin-top:10px}

/* FORM GRID */
.form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px}

.form-group{display:flex;flex-direction:column}
.form-group label{font-weight:600;font-size:13px;margin-bottom:8px;color:#333}
.form-group input,.form-group textarea{padding:12px;border:2px solid #e0e0e0;border-radius:8px;font-size:14px;width:100%;transition:all .3s;font-family:'Poppins',sans-serif}
.form-group input:focus,.form-group textarea:focus{border-color:#2e7d32;box-shadow:0 0 0 3px rgba(46,125,50,0.1);outline:none}
.form-group textarea{min-height:100px;resize:vertical}

/* BUTTONS */
.button-group{display:flex;gap:12px;margin-top:25px;flex-wrap:wrap}

.btn{padding:12px 28px;border:none;border-radius:10px;font-weight:600;cursor:pointer;transition:all .3s;font-size:14px;display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none}

.btn-primary{background:linear-gradient(135deg,#2e7d32,#1b5e20);color:#fff}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(46,125,50,0.3)}

.btn-secondary{background:#e8e8e8;color:#333}
.btn-secondary:hover{background:#d8d8d8;transform:translateY(-2px)}

/* HAMBURGER & OVERLAY */
.mobile-menu-btn,
.sidebar-overlay{
    display:none;
}

/* === TABLET === */
@media(max-width:900px) and (min-width:769px){
    .sidebar{width:220px}
    .main-content{margin-left:220px;padding:35px}
}

/* === MOBILE === */
@media(max-width:768px){
    .dashboard-container{flex-direction:column}
    
    /* Sidebar Mobile */
    .sidebar{
        position:fixed;
        top:0;
        left:-100%;
        width:280px;
        max-width:85vw;
        height:100vh;
        z-index:9999;
        transition:left 0.4s ease;
        padding:20px 0;
    }
    
    .sidebar.show{left:0}
    
    .sidebar-header{padding:0 20px 20px}
    .user-avatar{width:60px;height:60px;font-size:24px}
    .sidebar-nav{margin-top:15px;gap:5px}
    .nav-item{padding:12px 20px;font-size:14px}
    .sidebar-actions{gap:8px;padding:15px 0}
    
    /* Mobile Menu Button */
    .mobile-menu-btn{
        display:flex;
        position:fixed;
        top:15px;
        left:15px;
        width:50px;
        height:50px;
        background:#2e7d32;
        color:#fff;
        border:none;
        border-radius:12px;
        font-size:20px;
        cursor:pointer;
        z-index:9998;
        align-items:center;
        justify-content:center;
        box-shadow:0 4px 12px rgba(0,0,0,0.3);
        transition:all 0.3s;
    }
    
    .mobile-menu-btn:active{transform:scale(0.95)}
    
    /* Overlay */
    .sidebar-overlay{
        display:none;
        position:fixed;
        top:0;
        left:0;
        width:100vw;
        height:100vh;
        background:rgba(0,0,0,0.6);
        z-index:9998;
    }
    
    .sidebar-overlay.show{display:block}
    
    /* Main Content */
    .main-content{
        margin-left:0;
        padding:80px 20px 30px 20px;
        width:100%;
    }
    
    .page-header{margin-bottom:20px}
    .page-header h1{font-size:22px}
    .page-header p{font-size:13px}
    
    /* Form Section */
    .form-section{
        padding:25px 20px;
        margin-bottom:20px;
    }
    
    .form-section h2{font-size:18px}
    
    /* Profile Photo Section */
    .profile-photo-section{
        flex-direction:column;
        align-items:center;
        text-align:center;
        gap:20px;
    }
    
    .profile-photo-preview img,
    .no-photo{
        width:110px;
        height:110px;
    }
    
    .photo-upload-area{
        width:100%;
    }
    
    .file-input-label{
        width:100%;
        padding:14px 20px;
    }
    
    /* Form Grid */
    .form-grid{
        grid-template-columns:1fr;
        gap:16px;
    }
    
    .form-group label{font-size:12px}
    .form-group input,.form-group textarea{padding:11px;font-size:13px}
    
    /* Buttons */
    .button-group{
        flex-direction:column;
        gap:10px;
    }
    
    .btn{
        width:100%;
        padding:14px 20px;
    }
}

/* === SMALL MOBILE === */
@media(max-width:375px){
    .main-content{padding:70px 15px 25px 15px}
    .form-section{padding:20px 16px}
    .profile-photo-preview img,.no-photo{width:100px;height:100px}
}
</style>
</head>
<body>

<!-- Mobile Menu Button -->
<button class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleMobileSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

<div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="mobileSidebar">
        <div>
            <div class="sidebar-header">
                <div class="user-avatar">
                    <?php if (!empty($user_data['foto_profil']) && file_exists('../uploads/profil/' . $user_data['foto_profil'])): ?>
                        <img src="../uploads/profil/<?php echo htmlspecialchars($user_data['foto_profil']); ?>" alt="Foto Profil">
                    <?php else: ?>
                        <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($_SESSION['nama']); ?></h3>
                    <div class="user-status"><span class="status-indicator"></span> Online</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="profil_pribadi.php" class="nav-item"><i class="fas fa-user"></i> Profil Pribadi</a>
                <a href="edit_profil.php" class="nav-item active"><i class="fas fa-edit"></i> Edit Profil</a>
                <a href="booking.php" class="nav-item"><i class="fas fa-calendar-alt"></i> Booking</a>
            </nav>
        </div>
        <div class="sidebar-actions">
            <a href="../index.php" class="nav-item" style="background:#2e7d32;border-left:4px solid #FFD700"><i class="fas fa-home"></i> Kembali ke Utama</a>
            <a href="../backend/logout.php" class="nav-item" style="background:#e53935;border-left:4px solid #FFD700"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-user-edit"></i> Edit Profil</h1>
            <p>Perbarui informasi pribadi dan keamanan akun Anda</p>
        </div>

        <div id="alert" class="alert"></div>

        <!-- DATA PRIBADI -->
        <div class="form-section">
            <h2><i class="fas fa-user"></i> Data Pribadi</h2>
            <form id="formProfil" enctype="multipart/form-data">
                <div class="profile-photo-section">
                    <div class="profile-photo-preview" id="photoPreview">
                        <?php if (!empty($user_data['foto_profil']) && file_exists('../uploads/profil/' . $user_data['foto_profil'])): ?>
                            <img id="photoImg" src="../uploads/profil/<?php echo htmlspecialchars($user_data['foto_profil']); ?>" alt="Foto Profil">
                        <?php else: ?>
                            <div class="no-photo" id="noPhoto">📷</div>
                        <?php endif; ?>
                    </div>
                    <div class="photo-upload-area">
                        <label class="file-input-label" for="foto_profil">
                            <i class="fas fa-cloud-upload-alt"></i> Pilih Foto
                        </label>
                        <input type="file" id="foto_profil" name="foto_profil" accept="image/*">
                        <div class="file-info">Format: JPG, JPEG, PNG, GIF | Maks: 5MB</div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user_data['nama'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>NIK *</label>
                        <input type="text" name="nik" value="<?php echo htmlspecialchars($pendaki_data['nik'] ?? ''); ?>" maxlength="16">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
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
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>

        <!-- UBAH PASSWORD -->
        <div class="form-section">
            <h2><i class="fas fa-lock"></i> Ubah Password</h2>
            <form id="formPassword">
                <div class="form-grid">
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Password Lama *</label>
                        <input type="password" name="password_lama" required>
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Password Baru *</label>
                        <input type="password" name="password_baru" required minlength="6">
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Konfirmasi Password Baru *</label>
                        <input type="password" name="password_baru_confirm" required minlength="6">
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Ubah Password
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
// Alert Function
const showAlert = (msg, ok) => {
    const a = document.getElementById('alert');
    a.textContent = msg;
    a.className = 'alert ' + (ok ? 'success' : 'error');
    a.style.display = 'block';
    setTimeout(() => a.style.display = 'none', 5000);
};

// Photo Preview
const fotoInput = document.getElementById('foto_profil');
fotoInput.addEventListener('change', function() {
    if (this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('photoImg');
            const preview = document.getElementById('photoPreview');
            if (img) {
                img.src = e.target.result;
            } else {
                preview.innerHTML = `<img id="photoImg" src="${e.target.result}" alt="Preview" style="width:130px;height:130px;border-radius:12px;object-fit:cover;border:4px solid #2e7d32;box-shadow:0 4px 15px rgba(0,0,0,0.12)">`;
            }
            const noPhoto = document.getElementById('noPhoto');
            if (noPhoto) noPhoto.remove();
        };
        reader.readAsDataURL(this.files[0]);
    }
});

// Form Profil Submit
document.getElementById('formProfil').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('update_profil', '1');
    
    fetch('', {method: 'POST', body: fd})
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                showAlert(d.msg, 1);
                document.getElementById('nama').value = d.nama;
                document.getElementById('email').value = d.email;
                
                if (d.foto) {
                    const img = document.getElementById('photoImg');
                    if (img) {
                        img.src = d.foto + '?' + Date.now();
                    } else {
                        const preview = document.getElementById('photoPreview');
                        preview.innerHTML = `<img id="photoImg" src="${d.foto}?${Date.now()}" alt="Foto" style="width:130px;height:130px;border-radius:12px;object-fit:cover;border:4px solid #2e7d32;box-shadow:0 4px 15px rgba(0,0,0,0.12)">`;
                    }
                    
                    // Update sidebar avatar
                    const sidebarAvatar = document.querySelector('.sidebar-header .user-avatar img');
                    if (sidebarAvatar) {
                        sidebarAvatar.src = d.foto + '?' + Date.now();
                    }
                }
                
                // Update sidebar name
                const sidebarName = document.querySelector('.sidebar-header .user-info h3');
                if (sidebarName) sidebarName.textContent = d.nama;
            } else {
                showAlert(d.msg, 0);
            }
        })
        .catch(err => showAlert('Terjadi kesalahan!', 0));
});

// Form Password Submit
document.getElementById('formPassword').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('ubah_password', '1');
    
    fetch('', {method: 'POST', body: fd})
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                showAlert(d.msg, 1);
                this.reset();
            } else {
                showAlert(d.msg, 0);
            }
        })
        .catch(err => showAlert('Terjadi kesalahan!', 0));
});

// Mobile Sidebar Functions
function toggleMobileSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btn = document.getElementById('mobileMenuBtn');
    
    if (!sidebar || !overlay || !btn) return;
    
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
    
    const icon = btn.querySelector('i');
    if (sidebar.classList.contains('show')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
        document.body.style.overflow = 'hidden';
    } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
        document.body.style.overflow = 'auto';
    }
}

function closeMobileSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btn = document.getElementById('mobileMenuBtn');
    
    if (sidebar) sidebar.classList.remove('show');
    if (overlay) overlay.classList.remove('show');
    if (btn) {
        const icon = btn.querySelector('i');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
    document.body.style.overflow = 'auto';
}

document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            setTimeout(closeMobileSidebar, 200);
        }
    });
});

window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeMobileSidebar();
    }
});
</script>

</body>
</html>
