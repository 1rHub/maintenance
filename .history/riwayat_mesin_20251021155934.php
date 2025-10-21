<?php
include "koneksi.php";

$id = intval($_GET['id'] ?? 0);

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
WHERE l.status = 'finish' AND l.mesin_id = $id
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
?>
