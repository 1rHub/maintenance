<?php
include "koneksi.php";

$nama_mesin = $_GET['nama'] ?? '';

if ($nama_mesin === '') {
    echo json_encode([]);
    exit;
}

$query = "SELECT created_at AS tanggal, reporter_name, divisi, pesan AS keterangan, mekanik, pesan_mekanik, kategori 
          FROM laporan
          WHERE nama_mesin = '$nama_mesin'
          ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($data);
?>
