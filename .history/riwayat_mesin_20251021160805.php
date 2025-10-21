<?php
include "koneksi.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$sql = "
    SELECT 
        l.tanggal AS tanggal,
        l.nama AS nama,
        l.divisi AS divisi,
        l.pesan AS pesan,
        l.nama_mekanik AS nama_mekanik,
        l.pesan_mekanik AS pesan_mekanik,
        l.kategori AS kategori
    FROM laporan l
    WHERE l.status = 'finish' AND l.mesin_id = $id
    ORDER BY l.tanggal DESC
";

$res = mysqli_query($conn, $sql);
$data = [];

while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
