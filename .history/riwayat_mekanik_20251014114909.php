<?php
include "koneksi.php";

$nama = $_GET['nama'] ?? '';

if (empty($nama)) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT tanggal, mesin, pesan, pesan_mekanik FROM laporan WHERE status='finish' AND nama_mekanik LIKE ?");
$search = "%$nama%";
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
