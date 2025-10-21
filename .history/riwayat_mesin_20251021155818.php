<?php
include "koneksi.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (mysqli_num_rows($result) === 0) {
    echo json_encode(["debug" => "Nama mesin '$nama' tidak ditemukan di tabel laporan."]);
    exit;
}

$nama = mysqli_real_escape_string($conn, $_GET['nama'] ?? '');

$query = "
SELECT 
    l.tanggal, 
    l.nama, 
    l.divisi, 
    l.pesan, 
    l.pesan_mekanik, 
    l.kategori, 
    m.nama_mesin, 
    GROUP_CONCAT(mk.nama SEPARATOR ', ') AS nama_mekanik
FROM laporan l
LEFT JOIN mesin m ON l.mesin_id = m.id
LEFT JOIN mekanik mk ON FIND_IN_SET(mk.id, l.mekanik_id)
WHERE l.status = 'finish' AND m.nama_mesin = '$nama'
GROUP BY l.id
ORDER BY l.tanggal DESC";

$result = mysqli_query($conn, $query);
$data = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
