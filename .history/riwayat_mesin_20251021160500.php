<?php
include "koneksi.php";

header('Content-Type: application/json');

// ambil id mesin
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode([]);
    exit;
}

// query ambil riwayat laporan yang sudah selesai (finish)
$sql = "
    SELECT 
        l.tanggal AS tanggal,
        l.reporter_name AS nama,
        l.divisi,
        l.pesan,
        l.pesan_mekanik,
        l.kategori,
        m.nama_mesin,
        COALESCE(l.mekanik, '-') AS nama_mekanik
    FROM laporan l
    JOIN mesin m ON l.machine_id = m.id
    WHERE l.status = 'finish' AND l.machine_id = $id
    ORDER BY l.created_at DESC
";

$result = mysqli_query($conn, $sql);
$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>
