<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../backend/koneksi.php';

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'admin_data';
$admin_id = $_SESSION['user_id'];

// Fetch admin data
$admins = $conn->query("SELECT user_id as id, nama, email, role, tanggal_daftar FROM users WHERE role = 'admin' ORDER BY tanggal_daftar DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Admin - Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}
body{background:linear-gradient(135deg,#f5faf5,#e8f5e9);overflow-x:hidden;min-height:100vh}
.main-content{margin-left:260px;padding:40px}
.top-header{margin-bottom:30px}
.top-header h1{color:#2e7d32;font-size:28px;font-weight:700}

.tabs{display:flex;gap:0;background:#fff;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08);overflow:hidden;margin-bottom:30px;flex-wrap:wrap}
.tab-btn{flex:1;padding:15px 20px;background:#f5f5f5;border:none;cursor:pointer;font-weight:600;color:#666;transition:all .3s;border-bottom:3px solid #ddd;min-width:150px}
.tab-btn:hover{background:#e8f5e9;color:#2e7d32}
.tab-btn.active{background:#2e7d32;color:#fff;border-bottom:3px solid #2e7d32}

.tab-content{display:none}
.tab-content.active{display:block;animation:fadeIn .3s}

@keyframes fadeIn{from{opacity:0}to{opacity:1}}

.card{background:#fff;border-radius:12px;padding:25px;box-shadow:0 4px 15px rgba(0,0,0,0.08);margin-bottom:20px}
.card h2{color:#2e7d32;font-size:20px;margin-bottom:20px;display:flex;align-items:center;gap:10px}

.form-group{margin-bottom:15px}
.form-group label{display:block;margin-bottom:5px;color:#333;font-weight:600;font-size:14px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 15px;border:2px solid #ddd;border-radius:8px;font-size:14px;font-family:'Poppins',sans-serif}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:#2e7d32;outline:none}

.btn{padding:10px 20px;border:none;border-radius:8px;cursor:pointer;font-weight:600;transition:all .3s;font-size:14px}
.btn-primary{background:#2e7d32;color:#fff}
.btn-primary:hover{background:#1b5e20;transform:translateY(-2px)}
.btn-secondary{background:#e8e8e8;color:#333}
.btn-secondary:hover{background:#d8d8d8}
.btn-danger{background:#d32f2f;color:#fff}
.btn-danger:hover{background:#b71c1c}
.btn-success{background:#43a047;color:#fff}
.btn-success:hover{background:#2e7d32}

.table-wrapper{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse;margin-top:15px}
.data-table th{background:#f5faf5;padding:12px;text-align:left;font-weight:700;color:#2e7d32;border-bottom:2px solid #e8f5e9;font-size:13px}
.data-table td{padding:12px;border-bottom:1px solid #eee;font-size:13px;color:#555}
.data-table tr:hover{background:#f5faf5}
.data-table td:last-child{text-align:center}

.badge{display:inline-block;padding:5px 10px;border-radius:20px;font-size:12px;font-weight:600}
.badge-admin{background:#e3f2fd;color:#1976d2}
.badge-success{background:#d4edda;color:#155724}
.badge-danger{background:#f8d7da;color:#721c24}
.badge-warning{background:#fff3cd;color:#856404}

.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.stat-box{background:linear-gradient(135deg,#2e7d32,#1b5e20);color:#fff;padding:20px;border-radius:12px;text-align:center}
.stat-number{font-size:32px;font-weight:700;margin-bottom:5px}
.stat-label{font-size:13px;opacity:.9}

.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center}
.modal.active{display:flex}
.modal-content{background:#fff;border-radius:12px;padding:30px;max-width:500px;width:90%;box-shadow:0 10px 40px rgba(0,0,0,0.2)}
.modal-close{float:right;background:none;border:none;font-size:24px;cursor:pointer;color:#999}

.activity-log{max-height:400px;overflow-y:auto}
.log-item{padding:12px;border-left:4px solid #2e7d32;margin-bottom:10px;background:#f5faf5;border-radius:4px;font-size:13px}
.log-time{color:#999;font-size:12px}
.log-action{color:#2e7d32;font-weight:600}

.alert{padding:15px;border-radius:8px;margin-bottom:15px;display:none}
.alert.show{display:block}
.alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
.alert-danger{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
.alert-info{background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb}

@media(max-width:768px){
    .main-content{margin-left:0;padding:20px}
    .tabs{flex-direction:column}
    .tab-btn{min-width:100%}
    .grid-2{grid-template-columns:1fr}
    .data-table{font-size:12px}
    .data-table th,.data-table td{padding:8px}
}
</style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="top-header">
        <h1><i class="fas fa-user-shield"></i> Manajemen Admin</h1>
    </div>

    <!-- TAB BUTTONS -->
    <div class="tabs">
        <button class="tab-btn <?php echo ($tab == 'admin_data') ? 'active' : ''; ?>" onclick="switchTab('admin_data')"><i class="fas fa-users"></i> Data Admin</button>
        <button class="tab-btn <?php echo ($tab == 'permissions') ? 'active' : ''; ?>" onclick="switchTab('permissions')"><i class="fas fa-lock"></i> Role & Permission</button>
        <button class="tab-btn <?php echo ($tab == 'activity_log') ? 'active' : ''; ?>" onclick="switchTab('activity_log')"><i class="fas fa-history"></i> Activity Log</button>
        <button class="tab-btn <?php echo ($tab == 'user_management') ? 'active' : ''; ?>" onclick="switchTab('user_management')"><i class="fas fa-user-lock"></i> User Management</button>
        <button class="tab-btn <?php echo ($tab == 'backup') ? 'active' : ''; ?>" onclick="switchTab('backup')"><i class="fas fa-database"></i> Backup & Restore</button>
        <button class="tab-btn <?php echo ($tab == 'settings') ? 'active' : ''; ?>" onclick="switchTab('settings')"><i class="fas fa-cog"></i> Setting Sistem</button>
    </div>

    <!-- TAB 1: DATA ADMIN -->
    <div id="admin_data" class="tab-content <?php echo ($tab == 'admin_data') ? 'active' : ''; ?>">
        <div class="card">
            <h2><i class="fas fa-users-cog"></i> Daftar Admin</h2>
            <button class="btn btn-primary" onclick="openAddAdminModal()"><i class="fas fa-plus"></i> Tambah Admin Baru</button>
            
            <div class="table-wrapper" style="margin-top:20px">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = $admins->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><span class="badge badge-admin"><?php echo ucfirst($row['role']); ?></span></td>
                            <td><?php echo date('d-m-Y', strtotime($row['tanggal_daftar'])); ?></td>
                            <td>
                                <button class="btn btn-secondary" style="font-size:12px" onclick="editAdmin(<?php echo $row['id']; ?>)"><i class="fas fa-edit"></i></button>
                                <?php if ($row['id'] != $admin_id): ?>
                                <button class="btn btn-danger" style="font-size:12px" onclick="deleteAdmin(<?php echo $row['id']; ?>)"><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:20px">
                <h3 style="color:#2e7d32;margin-bottom:15px"><i class="fas fa-user-edit"></i> Edit Profil Saya</h3>
                <form method="POST" action="#" onsubmit="updateMyProfile(event)">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" id="myNama" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="myEmail" required>
                    </div>
                    <div class="form-group">
                        <label>Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                        <input type="password" id="myPassword">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB 2: ROLE & PERMISSION -->
    <div id="permissions" class="tab-content <?php echo ($tab == 'permissions') ? 'active' : ''; ?>">
        <div class="card">
            <h2><i class="fas fa-lock-open"></i> Role & Permission Management</h2>
            
            <div class="grid-2">
                <div>
                    <h3 style="color:#2e7d32;margin-bottom:15px">Super Admin</h3>
                    <div style="background:#f5faf5;padding:15px;border-radius:8px">
                        <p style="margin-bottom:10px"><span class="badge badge-success">✓ Akses Penuh</span></p>
                        <ul style="font-size:13px;color:#555;line-height:1.8">
                            <li><i class="fas fa-check"></i> Kelola semua admin</li>
                            <li><i class="fas fa-check"></i> Akses ke semua fitur</li>
                            <li><i class="fas fa-check"></i> Lihat activity log</li>
                            <li><i class="fas fa-check"></i> Backup & restore database</li>
                            <li><i class="fas fa-check"></i> Setting sistem</li>
                        </ul>
                    </div>
                </div>
                <div>
                    <h3 style="color:#2e7d32;margin-bottom:15px">Admin Biasa</h3>
                    <div style="background:#f5faf5;padding:15px;border-radius:8px">
                        <p style="margin-bottom:10px"><span class="badge badge-warning">⚠ Akses Terbatas</span></p>
                        <ul style="font-size:13px;color:#555;line-height:1.8">
                            <li><i class="fas fa-check"></i> Kelola pesanan</li>
                            <li><i class="fas fa-check"></i> Kelola pembayaran</li>
                            <li><i class="fas fa-check"></i> Kelola pengguna</li>
                            <li><i class="fas fa-times"></i> Tidak bisa edit admin lain</li>
                            <li><i class="fas fa-times"></i> Tidak bisa backup database</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div style="margin-top:20px;padding:15px;background:#fff3cd;border-radius:8px;border-left:4px solid #f57c00">
                <strong style="color:#856404"><i class="fas fa-info-circle"></i> Info:</strong>
                <p style="color:#856404;font-size:13px;margin-top:5px">Saat ini Anda memiliki akses sebagai <strong><?php echo $_SESSION['role']; ?></strong>. Role dan Permission tidak bisa diubah melalui web, hubungi developer untuk perubahan role.</p>
            </div>
        </div>
    </div>

    <!-- TAB 3: ACTIVITY LOG -->
    <div id="activity_log" class="tab-content <?php echo ($tab == 'activity_log') ? 'active' : ''; ?>">
        <div class="card">
            <h2><i class="fas fa-list-check"></i> Activity Log Admin</h2>
            
            <div class="form-group" style="max-width:300px">
                <label>Filter Tanggal</label>
                <input type="date" id="logDate" value="<?php echo date('Y-m-d'); ?>">
                <button class="btn btn-primary" style="margin-top:10px;width:100%" onclick="filterLog()"><i class="fas fa-filter"></i> Filter</button>
            </div>

            <div class="activity-log" id="activityLog">
                <div class="log-item">
                    <div class="log-action">Login</div>
                    <div style="color:#555;font-size:13px;margin-top:5px">Admin berhasil login ke sistem</div>
                    <div class="log-time"><i class="fas fa-clock"></i> <?php echo date('d-m-Y H:i:s'); ?></div>
                </div>
                <div class="log-item">
                    <div class="log-action">Akses Halaman Laporan</div>
                    <div style="color:#555;font-size:13px;margin-top:5px">Admin mengakses halaman laporan bulanan</div>
                    <div class="log-time"><i class="fas fa-clock"></i> <?php echo date('d-m-Y H:i:s', time()-3600); ?></div>
                </div>
                <div class="log-item">
                    <div class="log-action">Update Data Pesanan</div>
                    <div style="color:#555;font-size:13px;margin-top:5px">Admin mengubah status pesanan #12345</div>
                    <div class="log-time"><i class="fas fa-clock"></i> <?php echo date('d-m-Y H:i:s', time()-7200); ?></div>
                </div>
            </div>

            <button class="btn btn-secondary" style="margin-top:15px;width:100%"><i class="fas fa-download"></i> Export Activity Log</button>
        </div>
    </div>

    <!-- TAB 4: USER MANAGEMENT -->
    <div id="user_management" class="tab-content <?php echo ($tab == 'user_management') ? 'active' : ''; ?>">
        <div class="card">
            <h2><i class="fas fa-user-lock"></i> Manajemen Akun Pendaki</h2>
            
            <div class="form-group">
                <label>Cari Pendaki</label>
                <input type="text" id="searchPendaki" placeholder="Masukkan nama atau email pendaki...">
            </div>

            <div class="table-wrapper" style="margin-top:20px">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pendaki = $conn->query("SELECT user_id as id, nama, email, role, tanggal_daftar FROM users WHERE role = 'pendaki' ORDER BY tanggal_daftar DESC LIMIT 20");
                        $no = 1;
                        while ($row = $pendaki->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="badge badge-success">Aktif</span>
                            </td>
                            <td><?php echo date('d-m-Y', strtotime($row['tanggal_daftar'])); ?></td>
                            <td>
                                <button class="btn btn-secondary" style="font-size:12px" onclick="lockAccount(<?php echo $row['id']; ?>)"><i class="fas fa-lock"></i> Kunci</button>
                                <button class="btn btn-success" style="font-size:12px" onclick="resetPassword(<?php echo $row['id']; ?>)"><i class="fas fa-key"></i> Reset Pass</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 5: BACKUP & RESTORE -->
    <div id="backup" class="tab-content <?php echo ($tab == 'backup') ? 'active' : ''; ?>">
        <div class="card">
            <h2><i class="fas fa-database"></i> Backup & Restore Database</h2>
            
            <div class="grid-2">
                <div>
                    <h3 style="color:#2e7d32;margin-bottom:15px"><i class="fas fa-cloud-download-alt"></i> Buat Backup</h3>
                    <p style="color:#555;font-size:13px;margin-bottom:15px">Buat backup database terbaru untuk keamanan data</p>
                    <button class="btn btn-primary" style="width:100%" onclick="createBackup()"><i class="fas fa-plus"></i> Buat Backup Sekarang</button>
                </div>
                <div>
                    <h3 style="color:#2e7d32;margin-bottom:15px"><i class="fas fa-cloud-upload-alt"></i> Restore Backup</h3>
                    <p style="color:#555;font-size:13px;margin-bottom:15px">Kembalikan database dari file backup sebelumnya</p>
                    <input type="file" id="backupFile" accept=".sql" style="display:none">
                    <button class="btn btn-secondary" style="width:100%" onclick="document.getElementById('backupFile').click()"><i class="fas fa-upload"></i> Pilih File Backup</button>
                </div>
            </div>

            <div style="margin-top:30px">
                <h3 style="color:#2e7d32;margin-bottom:15px"><i class="fas fa-history"></i> Riwayat Backup</h3>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Waktu Backup</th>
                                <th>Ukuran File</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><?php echo date('d-m-Y H:i:s'); ?></td>
                                <td>2.5 MB</td>
                                <td><span class="badge badge-success">Berhasil</span></td>
                                <td>
                                    <button class="btn btn-secondary" style="font-size:12px"><i class="fas fa-download"></i> Download</button>
                                    <button class="btn btn-danger" style="font-size:12px"><i class="fas fa-trash"></i> Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 6: SETTING SISTEM -->
    <div id="settings" class="tab-content <?php echo ($tab == 'settings') ? 'active' : ''; ?>">
        <div class="card">
            <h2><i class="fas fa-cog"></i> Setting Sistem</h2>
            
            <form method="POST" action="#" onsubmit="saveSettings(event)">
                <div class="form-group">
                    <label>Nama Aplikasi</label>
                    <input type="text" value="Gunung Raung Booking System" required>
                </div>
                <div class="form-group">
                    <label>Email Sistem</label>
                    <input type="email" value="admin@gungraung.com" required>
                </div>
                <div class="form-group">
                    <label>Telepon Support</label>
                    <input type="tel" value="081234567890">
                </div>
                <div class="form-group">
                    <label>Alamat Kantor</label>
                    <textarea rows="3">Jl. Contoh No. 123, Kota, Indonesia</textarea>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" checked> Aktifkan Maintenance Mode</label>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" checked> Aktifkan Email Notifikasi</label>
                </div>
                <div class="form-group">
                    <label>Max File Upload (MB)</label>
                    <input type="number" value="5" min="1" max="50">
                </div>
                <div class="form-group">
                    <label>Session Timeout (menit)</label>
                    <input type="number" value="30" min="5" max="480">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Setting</button>
            </form>

            <div style="margin-top:30px;padding:15px;background:#e3f2fd;border-radius:8px;border-left:4px solid #1976d2">
                <strong style="color:#0c5460"><i class="fas fa-info-circle"></i> Danger Zone</strong>
                <p style="color:#0c5460;font-size:13px;margin-top:5px">Area berbahaya - gunakan dengan hati-hati</p>
                <button class="btn btn-danger" style="margin-top:10px" onclick="confirmClearCache()"><i class="fas fa-trash"></i> Clear Cache</button>
                <button class="btn btn-danger" style="margin-top:10px" onclick="confirmResetSystem()"><i class="fas fa-exclamation-triangle"></i> Reset Sistem</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: TAMBAH ADMIN -->
<div id="addAdminModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('addAdminModal')">×</button>
        <h2 style="color:#2e7d32;margin-bottom:20px"><i class="fas fa-user-plus"></i> Tambah Admin Baru</h2>
        <form onsubmit="addAdmin(event)">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" id="adminNama" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="adminEmail" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="adminPassword" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" id="adminPasswordConfirm" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%"><i class="fas fa-save"></i> Tambah Admin</button>
        </form>
    </div>
</div>

<!-- MODAL: ALERT -->
<div id="alertModal" class="modal">
    <div class="modal-content">
        <h2 id="alertTitle" style="color:#2e7d32;margin-bottom:15px"></h2>
        <p id="alertMessage" style="color:#555;margin-bottom:20px;font-size:14px"></p>
        <div style="display:flex;gap:10px">
            <button class="btn btn-secondary" style="flex:1" onclick="closeModal('alertModal')">Batal</button>
            <button class="btn btn-primary" style="flex:1" id="alertConfirmBtn" onclick="confirmAction()">Konfirmasi</button>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById(tab).classList.add('active');
    event.target.classList.add('active');
    window.history.pushState(null, null, '?tab=' + tab);
}

function openAddAdminModal() {
    document.getElementById('addAdminModal').classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function addAdmin(e) {
    e.preventDefault();
    const nama = document.getElementById('adminNama').value;
    const email = document.getElementById('adminEmail').value;
    const pass = document.getElementById('adminPassword').value;
    const passConfirm = document.getElementById('adminPasswordConfirm').value;
    
    if (pass !== passConfirm) {
        alert('Password tidak cocok!');
        return;
    }
    
    alert('Admin berhasil ditambahkan!');
    closeModal('addAdminModal');
    location.reload();
}

function editAdmin(id) {
    alert('Edit admin dengan ID: ' + id);
}

function deleteAdmin(id) {
    showAlert('Hapus Admin', 'Apakah Anda yakin ingin menghapus admin ini?', () => {
        alert('Admin berhasil dihapus!');
        location.reload();
    });
}

function lockAccount(id) {
    showAlert('Kunci Akun', 'Akun pendaki akan dikunci dan tidak bisa login. Lanjutkan?', () => {
        alert('Akun pendaki berhasil dikunci!');
    });
}

function resetPassword(id) {
    showAlert('Reset Password', 'Password akan direset ke password default. Lanjutkan?', () => {
        alert('Password berhasil direset ke: pendaki123');
    });
}

function createBackup() {
    alert('Membuat backup database... Tunggu sebentar.');
    alert('Backup berhasil dibuat!');
    location.reload();
}

function updateMyProfile(e) {
    e.preventDefault();
    alert('Profil berhasil diperbarui!');
}

function saveSettings(e) {
    e.preventDefault();
    alert('Setting sistem berhasil disimpan!');
}

function confirmClearCache() {
    showAlert('Clear Cache', 'Semua cache akan dihapus. Lanjutkan?', () => {
        alert('Cache berhasil dihapus!');
    });
}

function confirmResetSystem() {
    showAlert('Reset Sistem', 'HATI-HATI! Ini akan mereset seluruh sistem. Yakin ingin lanjut?', () => {
        alert('Sistem berhasil direset ke kondisi awal!');
    });
}

function filterLog() {
    alert('Filter activity log');
}

function showAlert(title, message, callback) {
    document.getElementById('alertTitle').textContent = title;
    document.getElementById('alertMessage').textContent = message;
    window.alertCallback = callback;
    document.getElementById('alertModal').classList.add('active');
}

function confirmAction() {
    if (window.alertCallback) {
        window.alertCallback();
    }
    closeModal('alertModal');
}
</script>
</body>
</html>
