<?php
include '../backend/koneksi.php';
$id = intval($_GET['pendaki_id'] ?? 0);
if($id){
  $conn->query("DELETE FROM pendaki_detail WHERE pendaki_id=$id");
}
header('Location: data_pendaki.php');
exit;
