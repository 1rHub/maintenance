<?php
include "koneksi.php";

header('Content-Type: application/json');

$nama = isset($_GET['nama']) ? mysqli_real_escape_string($conn, $_GET['nama']) : '';

if ($nama === '') {
    echo json_encode([]);
    exit;
}

// Ambil semua laporan yang sudah selesai dan dikerjakan oleh mekanik terkait
$query = "
    SELECT 
        l.tanggal,
        m.nama_mesin AS mesin,
        l.pesan,
        l.pesan_mekanik,
        l.kategori
    FROM laporan l
    LEFT JOIN mesin m ON l.mesin_id = m.id
    WHERE l.status = 'finish'
      AND FIND_IN_SET('$nama', l.nama_mekanik) > 0
    ORDER BY l.tanggal DESC
";

$result = mysqli_query($conn, $query);

$data = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>
