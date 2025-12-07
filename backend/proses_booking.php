<?php
// backend/proses_booking.php - FIXED UNTUK MOBILE & LAPTOP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'koneksi.php';

// Path log
$logFile = __DIR__ . '/logs/booking_error.log';

// Konfigurasi
$MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB
$ALLOWED_EXT = ['jpg','jpeg','png','pdf'];

function send_alert_back($msg){
    echo "<script>alert('".addslashes($msg)."'); history.back();</script>";
    exit;
}

function log_error($msg){
    global $logFile;
    $entry = "[".date('Y-m-d H:i:s')."] ".$msg.PHP_EOL;
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

function move_uploaded_field($f, $destDir, $prefix = '', $MAX_FILE_SIZE = 5242880, $ALLOWED_EXT = ['jpg','jpeg','png','pdf']){
    if (!is_array($f)) throw new Exception("Parameter file tidak valid.");
    if (!isset($f['error'])) throw new Exception("File tidak ditemukan.");
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload gagal (error code {$f['error']}).");
    }
    if ($f['size'] > $MAX_FILE_SIZE) {
        throw new Exception("Ukuran file {$f['name']} melebihi batas ".($MAX_FILE_SIZE/1024/1024)."MB.");
    }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $ALLOWED_EXT)) {
        throw new Exception("Format file {$f['name']} tidak didukung. Hanya: ".implode(', ',$ALLOWED_EXT));
    }
    if (!is_dir($destDir)) {
        if (!@mkdir($destDir, 0777, true)) throw new Exception("Gagal membuat folder upload: {$destDir}");
    }
    $safe = preg_replace('/[^A-Za-z0-9._-]/','_',basename($f['name']));
    $filename = $prefix . time() . '_' . bin2hex(random_bytes(6)) . '_' . $safe;
    $path = rtrim($destDir,'/').'/'.$filename;
    if (!move_uploaded_file($f['tmp_name'], $path)) {
        throw new Exception("Gagal memindahkan file {$f['name']}.");
    }
    return $filename;
}

function get_tarif($conn, $table, $idField, $idVal){
    if (empty($idVal)) return 0;
    $sql = "SELECT tarif FROM `$table` WHERE `$idField` = ? LIMIT 1";
    $st = $conn->prepare($sql);
    if (!$st) throw new Exception("Prepare get_tarif gagal: " . $conn->error);
    $st->bind_param("i", $idVal);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    return intval($r['tarif'] ?? 0);
}

function file_from_anggota_index($index, $key){
    if (!isset($_FILES['anggota'])) return null;
    if (!isset($_FILES['anggota']['name'][$index][$key])) return null;
    return [
        'name' => $_FILES['anggota']['name'][$index][$key],
        'type' => $_FILES['anggota']['type'][$index][$key],
        'tmp_name' => $_FILES['anggota']['tmp_name'][$index][$key],
        'error' => $_FILES['anggota']['error'][$index][$key],
        'size' => $_FILES['anggota']['size'][$index][$key],
    ];
}

function refValues($arr){
    $refs = [];
    foreach($arr as $k => $v) $refs[$k] = &$arr[$k];
    return $refs;
}

// --------------- MULAI PROSES ---------------
$inTransaction = false;
try {
    // Pastikan login
    if (!isset($_SESSION['user_id'])) send_alert_back('Silakan login terlebih dahulu.');
    $user_id = intval($_SESSION['user_id']);

    // FIXED: Triple fallback untuk mobile (POST > POST_BACKUP > SESSION)
    $pendakian_id = intval($_POST['pendakian_id'] ?? $_POST['pendakian_id_backup'] ?? $_SESSION['selected_pendakian'] ?? 0);
    $jumlah_pendaki = intval($_POST['jumlah_pendaki'] ?? $_POST['jumlah_pendaki_backup'] ?? $_SESSION['jumlah_pendaki'] ?? 0);

    $nama_ketua     = trim($_POST['nama_ketua'] ?? '');
    $telepon_ketua  = trim($_POST['telepon_ketua'] ?? '');
    $alamat_ketua   = trim($_POST['alamat_ketua'] ?? '');
    $no_identitas   = trim($_POST['no_identitas_ketua'] ?? '');

    $guide_id  = !empty($_POST['guide_id']) ? intval($_POST['guide_id']) : null;
    $porter_id = !empty($_POST['porter_id']) ? intval($_POST['porter_id']) : null;
    $ojek_id   = !empty($_POST['ojek_id']) ? intval($_POST['ojek_id']) : null;

    $tanggal_pesan = date('Y-m-d H:i:s');
    $status_pesanan = 'menunggu_pembayaran';

    // Validasi dasar dengan pesan detail
    if (!$pendakian_id || $jumlah_pendaki <= 0) {
        $debug_msg = "pendakian_id=" . ($pendakian_id ?: 'kosong') . 
                     ", jumlah=" . ($jumlah_pendaki ?: 'kosong') . 
                     ", session_pid=" . ($_SESSION['selected_pendakian'] ?? 'kosong') .
                     ", session_jp=" . ($_SESSION['jumlah_pendaki'] ?? 'kosong');
        log_error("Validasi gagal: " . $debug_msg);
        throw new Exception("Data booking tidak lengkap (pendakian / jumlah). Debug: " . $debug_msg);
    }
    if (!preg_match('/^\d{16}$/', $no_identitas)) {
        throw new Exception("NIK ketua harus 16 digit.");
    }

    // Pastikan file ketua ada
    if (!isset($_FILES['ktp_ketua']) || !isset($_FILES['sehat_ketua'])) {
        throw new Exception("KTP & Surat Keterangan Sehat ketua wajib diupload.");
    }

    // Ambil info pendakian & jalur
    $q = $conn->prepare("
        SELECT p.pendakian_id, p.jalur_id, j.nama_jalur, j.tarif_tiket, p.kuota_tersedia
        FROM pendakian p
        JOIN jalur_pendakian j ON p.jalur_id = j.jalur_id
        WHERE p.pendakian_id = ? LIMIT 1
    ");
    if (!$q) throw new Exception("Prepare data pendakian gagal: " . $conn->error);
    $q->bind_param("i", $pendakian_id);
    $q->execute();
    $info = $q->get_result()->fetch_assoc();
    $q->close();
    if (!$info) throw new Exception("Data pendakian tidak ditemukan.");

    $nama_jalur = strtolower($info['nama_jalur'] ?? '');
    $tarif_tiket = intval($info['tarif_tiket'] ?? 0);
    $kuota_tersedia = intval($info['kuota_tersedia'] ?? 0);

    // Validasi jalur khusus
    if (strpos($nama_jalur,'kalibaru') !== false && $jumlah_pendaki < 6) {
        throw new Exception("Jalur Kalibaru minimal 6 orang.");
    }
    if (strpos($nama_jalur,'sumberwringin') !== false && $jumlah_pendaki < 2) {
        throw new Exception("Jalur Sumberwringin minimal 2 orang.");
    }
    if ($kuota_tersedia < $jumlah_pendaki) {
        throw new Exception("Kuota tidak mencukupi. Sisa kuota: {$kuota_tersedia}");
    }
    if (strpos($nama_jalur,'kalibaru') !== false && empty($guide_id)) {
        throw new Exception("Jalur Kalibaru wajib memilih guide.");
    }

    // Hitung tarif layanan
    $tarif_guide = $guide_id ? get_tarif($conn,'guide','guide_id',$guide_id) : 0;
    $tarif_porter = $porter_id ? get_tarif($conn,'porter','porter_id',$porter_id) : 0;
    $tarif_ojek = $ojek_id ? get_tarif($conn,'ojek','ojek_id',$ojek_id) : 0;

    $total_tiket = $tarif_tiket * $jumlah_pendaki;
    $total_layanan = $tarif_guide + $tarif_porter + $tarif_ojek;
    $posted_total = isset($_POST['total_bayar']) ? floatval($_POST['total_bayar']) : null;
    $total_bayar = $posted_total ? $posted_total : ($total_tiket + $total_layanan);

    // Mulai transaction
    $conn->begin_transaction();
    $inTransaction = true;

    // Upload file ketua
    $savedKtpKetua = move_uploaded_field($_FILES['ktp_ketua'], __DIR__ . '/../uploads/ktp', 'ktp_ketua_', $MAX_FILE_SIZE, $ALLOWED_EXT);
    $savedSehatKetua = move_uploaded_field($_FILES['sehat_ketua'], __DIR__ . '/../uploads/surat_sehat', 'sehat_ketua_', $MAX_FILE_SIZE, $ALLOWED_EXT);

    // Insert pesanan - build query dinamis agar NULL dapat disimpan
    $columns = ['user_id','pendakian_id','tanggal_pesan','jumlah_pendaki','total_bayar','status_pesanan','kode_token','guide_id','porter_id','ojek_id','nama_ketua','telepon_ketua','alamat_ketua','no_identitas'];
    $values = [];
    $placeholders = [];
    $types = '';
    $params = [];

    // kode token
    $kode_token = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));

    // mapping nilai sesuai kolom
    $mapping = [
        'user_id' => $user_id,
        'pendakian_id' => $pendakian_id,
        'tanggal_pesan' => $tanggal_pesan,
        'jumlah_pendaki' => $jumlah_pendaki,
        'total_bayar' => $total_bayar,
        'status_pesanan' => $status_pesanan,
        'kode_token' => $kode_token,
        'guide_id' => $guide_id,
        'porter_id' => $porter_id,
        'ojek_id' => $ojek_id,
        'nama_ketua' => $nama_ketua,
        'telepon_ketua' => $telepon_ketua,
        'alamat_ketua' => $alamat_ketua,
        'no_identitas' => $no_identitas
    ];

    foreach ($columns as $col) {
        $val = $mapping[$col];
        if (in_array($col, ['guide_id','porter_id','ojek_id']) && ($val === null || $val === '')) {
            $placeholders[] = "NULL";
        } else {
            $placeholders[] = "?";
            $params[] = $val;
            if (in_array($col, ['user_id','pendakian_id','jumlah_pendaki'])) $types .= 'i';
            else if ($col === 'total_bayar') $types .= 'd';
            else $types .= 's';
        }
    }

    $sql = "INSERT INTO pesanan (" . implode(",", $columns) . ") VALUES (" . implode(",", $placeholders) . ")";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare INSERT pesanan gagal: " . $conn->error);

    if (count($params) > 0) {
        $bindParams = array_merge([$types], $params);
        call_user_func_array([$stmt, 'bind_param'], refValues($bindParams));
    }

    if (!$stmt->execute()) {
        throw new Exception("Eksekusi INSERT pesanan gagal: " . $stmt->error);
    }
    $pesanan_id = $stmt->insert_id;
    $stmt->close();

    // Jika tabel pesanan punya kolom ktp_ketua / sehat_ketua -> update
    $hasKtpCol = (int)$conn->query("SHOW COLUMNS FROM pesanan LIKE 'ktp_ketua'")->num_rows;
    $hasSehatCol = (int)$conn->query("SHOW COLUMNS FROM pesanan LIKE 'sehat_ketua'")->num_rows;
    if ($hasKtpCol || $hasSehatCol) {
        $setParts = [];
        $paramsU = [];
        $typesUp = '';
        if ($hasKtpCol) { $setParts[] = "ktp_ketua = ?"; $paramsU[] = $savedKtpKetua; $typesUp .= 's'; }
        if ($hasSehatCol) { $setParts[] = "sehat_ketua = ?"; $paramsU[] = $savedSehatKetua; $typesUp .= 's'; }
        if (!empty($setParts)) {
            $sqlU = "UPDATE pesanan SET " . implode(", ", $setParts) . " WHERE pesanan_id = ?";
            $stmtU = $conn->prepare($sqlU);
            if (!$stmtU) throw new Exception("Prepare UPDATE pesanan gagal: " . $conn->error);
            $typesUfinal = $typesUp . 'i';
            $paramsU[] = $pesanan_id;
            $bindNames = array_merge([$typesUfinal], $paramsU);
            call_user_func_array([$stmtU, 'bind_param'], refValues($bindNames));
            if (!$stmtU->execute()) {
                throw new Exception("Eksekusi UPDATE file ketua ke pesanan gagal: " . $stmtU->error);
            }
            $stmtU->close();
        }
    }

    // Simpan ketua ke pesanan_anggota juga (jika tabel ada)
    $hasPesananAnggota = (int)$conn->query("SHOW TABLES LIKE 'pesanan_anggota'")->num_rows;
    if ($hasPesananAnggota) {
        $colsPA = ['pesanan_id','nama','nik','ktp','surat_sehat','porter_id','ojek_id','porter_harga','ojek_harga'];
        $place = [];
        $paramsPA = [];
        $typesPA = '';
        $ket_porter_harga = $porter_id ? get_tarif($conn,'porter','porter_id',$porter_id) : 0;
        $ket_ojek_harga   = $ojek_id ? get_tarif($conn,'ojek','ojek_id',$ojek_id) : 0;

        foreach ($colsPA as $c) {
            if (in_array($c, ['porter_id','ojek_id']) && ($mapping[str_replace('_harga','',$c)] === null || $mapping[str_replace('_harga','',$c)] === '')) {
                $place[] = "NULL";
            } else {
                $place[] = "?";
                if ($c === 'pesanan_id') { $paramsPA[] = $pesanan_id; $typesPA .= 'i'; }
                elseif ($c === 'nama') { $paramsPA[] = $nama_ketua; $typesPA .= 's'; }
                elseif ($c === 'nik') { $paramsPA[] = $no_identitas; $typesPA .= 's'; }
                elseif ($c === 'ktp') { $paramsPA[] = $savedKtpKetua; $typesPA .= 's'; }
                elseif ($c === 'surat_sehat') { $paramsPA[] = $savedSehatKetua; $typesPA .= 's'; }
                elseif ($c === 'porter_harga') { $paramsPA[] = $ket_porter_harga; $typesPA .= 'i'; }
                elseif ($c === 'ojek_harga') { $paramsPA[] = $ket_ojek_harga; $typesPA .= 'i'; }
                elseif ($c === 'porter_id') { $paramsPA[] = $porter_id; $typesPA .= 'i'; }
                elseif ($c === 'ojek_id') { $paramsPA[] = $ojek_id; $typesPA .= 'i'; }
            }
        }
        $sqlPA = "INSERT INTO pesanan_anggota (" . implode(',', $colsPA) . ") VALUES (" . implode(',', $place) . ")";
        $stmtK = $conn->prepare($sqlPA);
        if (!$stmtK) throw new Exception("Prepare INSERT pesanan_anggota (ketua) gagal: ".$conn->error);
        if (count($paramsPA) > 0) {
            $bindK = array_merge([$typesPA], $paramsPA);
            call_user_func_array([$stmtK, 'bind_param'], refValues($bindK));
        }
        if (!$stmtK->execute()) throw new Exception("Eksekusi INSERT pesanan_anggota (ketua) gagal: ".$stmtK->error);
        $stmtK->close();
    }

    // Simpan juga ke anggota_pendaki (tabel daftar anggota pendaki) - catat ketua juga
    $hasAnggotaPendaki = (int)$conn->query("SHOW TABLES LIKE 'anggota_pendaki'")->num_rows;
    if ($hasAnggotaPendaki) {
        $ket_jk = trim($_POST['jenis_kelamin_ketua'] ?? '');
        if ($ket_jk === 'Laki-laki' || $ket_jk === 'L') {
            $ket_jk = 'L';
        } elseif ($ket_jk === 'Perempuan' || $ket_jk === 'P') {
            $ket_jk = 'P';
        } else {
            $ket_jk = 'P';
        }
        
        $stmtAP = $conn->prepare("INSERT INTO anggota_pendaki (pesanan_id, nama_anggota, no_identitas, jenis_kelamin) VALUES (?, ?, ?, ?)");
        if (!$stmtAP) throw new Exception("Prepare INSERT anggota_pendaki (ketua) gagal: ".$conn->error);
        $stmtAP->bind_param("isss", $pesanan_id, $nama_ketua, $no_identitas, $ket_jk);
        if (!$stmtAP->execute()) throw new Exception("Eksekusi INSERT anggota_pendaki (ketua) gagal: ".$stmtAP->error);
        $stmtAP->close();
    }

    // --- Proses anggota lain (array) ---
    if (isset($_POST['anggota']) && is_array($_POST['anggota']) && count($_POST['anggota']) > 0) {
        foreach ($_POST['anggota'] as $idx => $adata) {
            $namaA = trim($adata['nama'] ?? '');
            $nikA  = trim($adata['nik'] ?? '');
            $hpA   = trim($adata['hp'] ?? '');
            $kewA  = trim($adata['kewarganegaraan'] ?? 'WNI');
            $jkA   = trim($adata['jenis_kelamin'] ?? '');

            if ($namaA === '' || $nikA === '') throw new Exception("Data anggota tidak lengkap (nama/nik).");
            if (!preg_match('/^\d{16}$/', $nikA)) throw new Exception("NIK anggota {$namaA} harus 16 digit.");

            $fileK = file_from_anggota_index($idx, 'ktp');
            $fileS = file_from_anggota_index($idx, 'sehat');
            if (!$fileK || !$fileS) throw new Exception("File KTP atau Surat Sehat untuk anggota {$namaA} (index {$idx}) belum lengkap.");

            $savedK = move_uploaded_field($fileK, __DIR__ . '/../uploads/ktp', 'ktp_angg_',$MAX_FILE_SIZE,$ALLOWED_EXT);
            $savedS = move_uploaded_field($fileS, __DIR__ . '/../uploads/surat_sehat', 'sehat_angg_',$MAX_FILE_SIZE,$ALLOWED_EXT);

            $angg_porter_id = !empty($adata['porter_id']) ? intval($adata['porter_id']) : null;
            $angg_ojek_id   = !empty($adata['ojek_id']) ? intval($adata['ojek_id']) : null;
            $port_harga = $angg_porter_id ? get_tarif($conn,'porter','porter_id',$angg_porter_id) : 0;
            $ojek_harga = $angg_ojek_id ? get_tarif($conn,'ojek','ojek_id',$angg_ojek_id) : 0;

            $colsA = ['pesanan_id','nama','nik','ktp','surat_sehat','porter_id','ojek_id','porter_harga','ojek_harga'];
            $placeA = [];
            $paramsA = [];
            $typesA = '';
            foreach ($colsA as $c) {
                if (in_array($c, ['porter_id','ojek_id']) && ($c === 'porter_id' && $angg_porter_id === null || $c === 'ojek_id' && $angg_ojek_id === null)) {
                    $placeA[] = "NULL";
                } else {
                    $placeA[] = "?";
                    if ($c === 'pesanan_id') { $paramsA[] = $pesanan_id; $typesA .= 'i'; }
                    elseif ($c === 'nama') { $paramsA[] = $namaA; $typesA .= 's'; }
                    elseif ($c === 'nik') { $paramsA[] = $nikA; $typesA .= 's'; }
                    elseif ($c === 'ktp') { $paramsA[] = $savedK; $typesA .= 's'; }
                    elseif ($c === 'surat_sehat') { $paramsA[] = $savedS; $typesA .= 's'; }
                    elseif ($c === 'porter_id') { $paramsA[] = $angg_porter_id; $typesA .= 'i'; }
                    elseif ($c === 'ojek_id') { $paramsA[] = $angg_ojek_id; $typesA .= 'i'; }
                    elseif ($c === 'porter_harga') { $paramsA[] = $port_harga; $typesA .= 'i'; }
                    elseif ($c === 'ojek_harga') { $paramsA[] = $ojek_harga; $typesA .= 'i'; }
                }
            }
            $sqlA = "INSERT INTO pesanan_anggota (" . implode(',', $colsA) . ") VALUES (" . implode(',', $placeA) . ")";
            $stmtA = $conn->prepare($sqlA);
            if (!$stmtA) throw new Exception("Prepare INSERT pesanan_anggota gagal: " . $conn->error);
            if (count($paramsA) > 0) {
                $bindA = array_merge([$typesA], $paramsA);
                call_user_func_array([$stmtA, 'bind_param'], refValues($bindA));
            }
            if (!$stmtA->execute()) throw new Exception("Gagal menyimpan pesanan_anggota untuk {$namaA}: " . $stmtA->error);
            $stmtA->close();

            if ($hasAnggotaPendaki) {
                $jkA_normalized = $jkA;
                if ($jkA === 'Laki-laki' || $jkA === 'L') {
                    $jkA_normalized = 'L';
                } elseif ($jkA === 'Perempuan' || $jkA === 'P') {
                    $jkA_normalized = 'P';
                } else {
                    $jkA_normalized = 'P';
                }
                
                $stmtAP2 = $conn->prepare("INSERT INTO anggota_pendaki (pesanan_id, nama_anggota, no_identitas, jenis_kelamin) VALUES (?, ?, ?, ?)");
                if (!$stmtAP2) throw new Exception("Prepare INSERT anggota_pendaki gagal: " . $conn->error);
                $stmtAP2->bind_param("isss", $pesanan_id, $namaA, $nikA, $jkA_normalized);
                if (!$stmtAP2->execute()) throw new Exception("Gagal menyimpan anggota_pendaki untuk {$namaA}: " . $stmtAP2->error);
                $stmtAP2->close();
            }
        }
    }

    // Kurangi kuota
    $upd = $conn->prepare("UPDATE pendakian SET kuota_tersedia = kuota_tersedia - ? WHERE pendakian_id = ?");
    if (!$upd) throw new Exception("Prepare update kuota gagal: " . $conn->error);
    $upd->bind_param("ii", $jumlah_pendaki, $pendakian_id);
    if (!$upd->execute()) throw new Exception("Gagal mengurangi kuota: " . $upd->error);
    $upd->close();

    // Commit trans
    $conn->commit();
    $inTransaction = false;

    // clear session selection
    unset($_SESSION['selected_pendakian'], $_SESSION['jumlah_pendaki']);

    // Redirect with SweetAlert
    ?>
    <!doctype html>
    <html lang="id">
    <head><meta charset="utf-8"><title>Booking Berhasil</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
    Swal.fire({
        icon: 'success',
        title: 'Booking Berhasil!',
        html: 'Kode Token Anda:<br><b><?= htmlspecialchars($kode_token) ?></b>',
        confirmButtonColor: '#43a047'
    }).then(()=> window.location = '../pengunjung/pembayaran.php?pesanan_id=<?= $pesanan_id ?>');
    </script>
    </body>
    </html>
    <?php
    exit;

} catch (Exception $e) {
    if ($inTransaction) {
        try { $conn->rollback(); } catch (Exception $ex) { /* ignore */ }
    }
    $msg = $e->getMessage();
    log_error($msg . " | trace: " . $e->getTraceAsString());

    send_alert_back("Terjadi error saat memproses booking: " . $msg);
}