<?php
include 'koneksi.php';

$mesin = mysqli_real_escape_string($conn, $_GET['mesin']);
$q = mysqli_query($conn, "SELECT * FROM list_finish WHERE mesin = '$mesin' ORDER BY created_at DESC");

echo "<h3>Riwayat Perbaikan: $mesin</h3>";
echo "<table>
<tr>
    <th>Tanggal</th>
    <th>Nama Pelapor</th>
    <th>Divisi</th>
    <th>Pesan Mekanik</th>
    <th>Status</th>
</tr>";

while ($r = mysqli_fetch_assoc($q)) {
    echo "<tr>
        <td>{$r['created_at']}</td>
        <td>{$r['reporter_name']}</td>
        <td>{$r['divisi']}</td>
        <td>{$r['pesan']}</td>
        <td>{$r['status']}</td>
    </tr>";
}

echo "</table>";
?>
