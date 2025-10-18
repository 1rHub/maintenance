<?php
include "koneksi.php";
include "sidebar.php";

$result = mysqli_query($conn, "SELECT * FROM mesin ORDER BY nama ASC");
$mesin_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Mesin</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="mekanik-container">
        <h1>Daftar Mesin</h1>
        <p class="breadcrumb">Halaman / Mesin</p>

        <div class="mekanik-list">
            <?php foreach ($mesin_list as $m): ?>
                <div class="mekanik-card" onclick="openRiwayatMesin('<?= htmlspecialchars($m['nama']) ?>')">
                    <?= htmlspecialchars($m['nama']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Popup Riwayat Mesin -->
    <div class="popup" id="popupRiwayatMesin">
        <div class="popup-content" style="width:90%; max-width:900px;">
            <span class="close-btn" onclick="closeRiwayatMesin()">&times;</span>
            <h2 id="riwayatMesinTitle">Riwayat Mesin</h2>

            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pelapor</th>
                        <th>Divisi</th>
                        <th>Keterangan</th>
                        <th>Mekanik</th>
                        <th>Pesan Mekanik</th>
                    </tr>
                </thead>
                <tbody id="riwayatMesinTable">
                    <tr><td colspan="6" style="text-align:center;">Pilih mesin untuk melihat riwayat.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function openRiwayatMesin(namaMesin) {
            document.getElementById('riwayatMesinTitle').textContent = `Riwayat Mesin: ${namaMesin}`;
            document.getElementById('popupRiwayatMesin').style.display = 'flex';
            fetch(`riwayat_mesin.php?nama=${encodeURIComponent(namaMesin)}`)
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('riwayatMesinTable');
                    tbody.innerHTML = "";
                    if (data.length === 0) {
                        tbody.innerHTML = "<tr><td colspan='6' style='text-align:center;'>Belum ada riwayat perbaikan.</td></tr>";
                        return;
                    }
                    data.forEach(item => {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>${item.tanggal}</td>
                            <td>${item.reporter_name}</td>
                            <td>${item.divisi}</td>
                            <td>${item.pesan}</td>
                            <td>${item.mekanik}</td>
                            <td>${item.pesan_mekanik}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(() => {
                    document.getElementById('riwayatMesinTable').innerHTML = 
                        "<tr><td colspan='6' style='text-align:center;'>Gagal memuat data.</td></tr>";
                });
        }

        function closeRiwayatMesin() {
            document.getElementById('popupRiwayatMesin').style.display = 'none';
        }
    </script>
</body>
</html>
