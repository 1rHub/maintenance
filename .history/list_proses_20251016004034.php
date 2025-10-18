<?php
session_start();
include "koneksi.php";
include "sidebar.php";

// Tombol “Selesai”
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selesai_laporan'])) {
    $id_laporan = $_POST['id_laporan'];
    $pesan_mekanik = $_POST['pesan_mekanik'];

    $sql = "UPDATE laporan SET status='finish', pesan_mekanik=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $pesan_mekanik, $id_laporan);
    if ($stmt->execute()) {
        header("Location: list_proses.php?selesai=1");
        exit;
    }
}

// Tombol “Batal Proses”
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batal_proses'])) {
    $id_laporan = $_POST['id_laporan'];
    $sql = "UPDATE laporan SET status='kerusakan', mekanik_id=NULL, pesan_mekanik=NULL WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_laporan);
    if ($stmt->execute()) {
        header("Location: list_proses.php?batal=1");
        exit;
    }
}

// Tombol “Ditunda”
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tunda_laporan'])) {
    $id_laporan = $_POST['id_laporan'];
    $pesan_mekanik = $_POST['pesan_mekanik'];

    // status menjadi 'ditunda', tapi nama mekanik tidak dihapus
    $sql = "UPDATE laporan SET status='ditunda', pesan_mekanik=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $pesan_mekanik, $id_laporan);
    if ($stmt->execute()) {
        header("Location: list_proses.php?tunda=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>List Proses</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <h1>LAPORAN PROSES</h1>
        <p class="breadcrumb">Halaman / List Proses</p>

        <?php if (isset($_GET['selesai'])): ?>
            <p class="success" id="successMsg">✅ Laporan berhasil diselesaikan!</p>
        <?php endif; ?>
        <?php if (isset($_GET['batal'])): ?>
            <p class="warning" id="successMsg">⚠️ Laporan dikembalikan ke list kerusakan!</p>
        <?php endif; ?>
        <?php if (isset($_GET['tunda'])): ?>
            <p class="info" id="successMsg">🕓 Laporan ditunda dan dikembalikan ke list kerusakan.</p>
        <?php endif; ?>

        <div class="filter">
            <label for="filterKategori">Filter Kategori:</label>
            <select id="filterKategori" onchange="filterTable()">
                <option value="all">Semua</option>
                <option value="Biasa">Biasa</option>
                <option value="Sedang">Sedang</option>
                <option value="Urgent">Urgent</option>
            </select>
        </div>

        <table id="laporanTable">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Mesin</th>
                    <th>Keterangan</th>
                    <th>Nama Pelapor</th>
                    <th>Divisi</th>
                    <th>Nama Mekanik</th>
                    <th>Kategori</th>
                </tr>
            </thead>
            <tbody>
                <?php
$result = mysqli_query($conn, "
    SELECT l.*, m.nama_mesin 
    FROM laporan l
    LEFT JOIN mesin m ON l.mesin_id = m.id
    WHERE l.status = 'proses'
    ORDER BY l.tanggal DESC
");
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $kategoriClass = strtolower($row['kategori']);
        echo "<tr data-kategori='{$row['kategori']}'>
            <td>{$row['tanggal']}</td>
            <td><a href='#' onclick=\"openDetailPopup('{$row['id']}')\">{$row['nama_mesin']}</a></td>
            <td>{$row['pesan']}</td>
            <td>{$row['nama']}</td>
            <td>{$row['divisi']}</td>
            <td>{$row['pesan_mekanik']}</td>
            <td class='kategori {$kategoriClass}'>{$row['kategori']}</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='7' style='text-align:center; padding:15px;'>Tidak ada laporan Proses.</td></tr>";
}

                ?>
            </tbody>
        </table>
    </div>

    <!-- Popup Detail -->
    <div class="popup" id="popupDetail">
        <div class="popup-content" style="width: 350px; text-align: center;">
            <span class="close-btn" onclick="closeDetailPopup()">&times;</span>
            <h2 style="margin-bottom: 15px;">Proses Laporan</h2>

            <form method="POST">
                <input type="hidden" name="id_laporan" id="detail_id">

                <textarea name="pesan_mekanik" id="pesan_mekanik" rows="3" 
                    placeholder="Tuliskan pesan setelah perbaikan..." 
                    style="width: 90%; margin: 10px 0; border-radius: 6px; padding: 8px; resize: none;"></textarea>

                <div style="display: flex; justify-content: center; gap: 10px;">
                    <button type="submit" name="batal_proses"
                        style="background-color: #e74c3c; color: white; padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer;">
                        Batalkan
                    </button>
                    <button type="submit" name="tunda_laporan"
                        style="background-color: #f39c12; color: white; padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer;">
                        Ditunda
                    </button>
                    <button type="submit" name="selesai_laporan"
                        style="background-color: #27ae60; color: white; padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer;">
                        Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function filterTable() {
            let filter = document.getElementById('filterKategori').value;
            document.querySelectorAll('#laporanTable tbody tr').forEach(row => {
                row.style.display = (filter === 'all' || row.dataset.kategori === filter) ? '' : 'none';
            });
        }

        function openDetailPopup(id) {
            document.getElementById('popupDetail').style.display = 'flex';
            document.getElementById('detail_id').value = id;
        }

        function closeDetailPopup() {
            document.getElementById('popupDetail').style.display = 'none';
        }

        // Otomatis hilangkan notifikasi
        setTimeout(() => {
            let msg = document.getElementById("successMsg");
            if (msg) msg.style.display = "none";
        }, 2500);
    </script>
</body>
</html>
