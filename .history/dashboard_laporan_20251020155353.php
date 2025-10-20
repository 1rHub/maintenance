<?php
session_start();
include "koneksi.php";
include "sidebar.php";

/* 🚫 Anti-Back & Anti-Cache */
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// === HAPUS LAPORAN ===
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['hapus_laporan'])) {
    if (!empty($_POST['selected_id'])) {
        $ids = implode(",", array_map('intval', $_POST['selected_id']));
        $query = "DELETE FROM laporan WHERE id IN ($ids)";
        mysqli_query($conn, $query);
    }
    header("Location: dashboard_laporan.php?hapus=1");
    exit();
}

// === SIMPAN LAPORAN BARU ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_laporan'])) {
    $nama = $_POST['nama'];
    $divisi = $_POST['divisi'];
    $mesin_id = $_POST['mesin_id'];
    $pesan = $_POST['pesan'];
    $kategori = $_POST['kategori'];

    $sql = "INSERT INTO laporan (nama, divisi, mesin_id, pesan, kategori, tanggal, status) 
            VALUES ('$nama', '$divisi', '$mesin_id', '$pesan', '$kategori', NOW(), 'kerusakan')";
    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard_laporan.php?success=1");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ambil_laporan'])) {
    $id_laporan = $_POST['id_laporan'];
    $mekanik_ids = $_POST['nama_mekanik'] ?? [];

    if (empty($mekanik_ids)) {
        echo "<script>alert('Pilih minimal satu mekanik.'); history.back();</script>";
        exit;
    }

    $nama_mekanik_list = [];
    $id_str = implode(",", array_map('intval', $mekanik_ids));
    $query = mysqli_query($conn, "SELECT nama FROM mekanik WHERE id IN ($id_str)");

    while ($row = mysqli_fetch_assoc($query)) {
        $nama_mekanik_list[] = $row['nama'];
    }

    $nama_mekanik = implode(", ", $nama_mekanik_list);
    $mekanik_id_str = implode(",", $mekanik_ids);

    $sql = "UPDATE laporan 
            SET status='proses', mekanik_id=?, nama_mekanik=? 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $mekanik_id_str, $nama_mekanik, $id_laporan);

    if ($stmt->execute()) {
        header("Location: dashboard_laporan.php?ambil=1");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Laporan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="main-content">
        <h1>LAPORAN KERUSAKAN</h1>
        <p class="breadcrumb">Halaman / List Kerusakan</p>

        <?php if (isset($_GET['success'])): ?>
            <p class="success" id="successMsg">✅ Laporan berhasil disimpan!</p>
        <?php endif; ?>
        <?php if (isset($_GET['ambil'])): ?>
            <p class="success" id="successMsg">✅ Laporan berhasil diambil ke proses!</p>
        <?php endif; ?>
        <?php if (isset($_GET['hapus'])): ?>
            <p class="success" id="successMsg">✅ Laporan berhasil dihapus!</p>
        <?php endif; ?>

        <div class="filter">
            <label for="filterKategori">Filter Kategori:</label>
            <select id="filterKategori" onchange="filterTable()">
                <option value="all">Semua</option>
                <option value="Biasa">Biasa</option>
                <option value="Sedang">Sedang</option>
                <option value="Urgent">Urgent</option>
            </select>

            <label for="filterStatus" style="margin-left: 20px;">Filter Status:</label>
            <select id="filterStatus" onchange="filterTable()">
                <option value="all">Semua</option>
                <option value="kerusakan">Kerusakan</option>
                <option value="ditunda">Ditunda</option>
            </select>
        </div>

        <form id="hapusForm" method="POST">
            <input type="hidden" name="hapus_laporan" value="1">

            <table id="laporanTable">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;">
                            <input type="checkbox" id="checkAll" onclick="toggleAll(this)">
                        </th>
                        <th>Tanggal</th>
                        <th>Mesin</th>
                        <th>Keterangan</th>
                        <th>Pesan Mekanik</th>
                        <th>Nama Pelapor</th>
                        <th>Divisi</th>
                        <th>Kategori</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = mysqli_query($conn, "
                        SELECT l.*, m.nama_mesin,
                            GROUP_CONCAT(mk.nama SEPARATOR ', ') AS nama_mekanik
                        FROM laporan l
                        LEFT JOIN mesin m ON l.mesin_id = m.id
                        LEFT JOIN mekanik mk ON FIND_IN_SET(mk.id, l.mekanik_id)
                        WHERE l.status IN ('kerusakan', 'ditunda')
                        GROUP BY l.id
                        ORDER BY l.tanggal DESC;
                    ");

                    if (mysqli_num_rows($result) > 0) {
                        while ($laporan = mysqli_fetch_assoc($result)) {
                            $kategoriClass = strtolower($laporan['kategori']);
                            echo "<tr data-kategori='{$laporan['kategori']}'>
                                <td style='text-align:center;'>
                                    <input type='checkbox' name='selected_id[]' value='{$laporan['id']}'>
                                </td>
                                <td>{$laporan['tanggal']}</td>
                                <td>
                                    <a href='#' onclick=\"openDetailPopup(
                                        '{$laporan['id']}',
                                        '" . htmlspecialchars($laporan['nama_mesin'], ENT_QUOTES) . "',
                                        '" . htmlspecialchars($laporan['pesan'], ENT_QUOTES) . "',
                                        '{$laporan['status']}',
                                        '" . htmlspecialchars($laporan['nama_mekanik'] ?? '', ENT_QUOTES) . "',
                                        '" . htmlspecialchars($laporan['pesan_mekanik'] ?? '', ENT_QUOTES) . "'
                                    )\">{$laporan['nama_mesin']}</a>
                                </td>
                                <td>{$laporan['pesan']}</td>
                                <td>" . ($laporan['pesan_mekanik'] ?? '-') . "</td>
                                <td>{$laporan['nama']}</td>
                                <td>{$laporan['divisi']}</td>
                                <td class='kategori {$kategoriClass}'>" . ucfirst($laporan['kategori']) . "</td>
                                <td class='status'>{$laporan['status']}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' style='text-align:center; padding:15px;'>Tidak ada laporan Kerusakan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <button type="button" onclick="openDeletePopup()" class="hapus-float-btn">
                Hapus Laporan
            </button>
        </form>
    </div>

    <!-- Popup Konfirmasi Hapus -->
    <div class="popup" id="popupDelete" style="display:none;">
        <div class="popup-content" style="max-width:350px; text-align:center;">
            <h3>Konfirmasi Hapus</h3>
            <p>Yakin ingin menghapus laporan terpilih?</p>
            <div style="margin-top:20px;">
                <button onclick="confirmDelete()"
                    style="background-color:#e74c3c; color:white; padding:8px 14px; border:none; border-radius:6px; cursor:pointer; margin-right:10px;">Ya,
                    Hapus</button>
                <button onclick="closeDeletePopup()"
                    style="background-color:#bdc3c7; color:black; padding:8px 14px; border:none; border-radius:6px; cursor:pointer;">Batal</button>
            </div>
        </div>
    </div>

    <!-- Popup Tambah Laporan -->
    <!-- (tidak diubah, tetap sama seperti sebelumnya) -->

    <!-- Popup Detail Laporan -->
    <!-- (tetap sama seperti sebelumnya) -->

    <script>
        /* === Fungsi JavaScript kamu tetap utuh === */

        // 🚫 Blok tombol "Back" browser
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function () {
            window.history.pushState(null, "", window.location.href);
        };
    </script>
</body>
</html>
