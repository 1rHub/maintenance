<?php
include "koneksi.php";
include "sidebar.php";

// Tambah mesin
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["tambah_mesin"])) {
    $nama = trim($_POST["nama_mesin"]);
    if ($nama !== "") {
        mysqli_query($conn, "INSERT INTO mesin (nama_mesin) VALUES ('$nama')");
    }
    header("Location: mesin.php");
    exit;
}

// Hapus mesin
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["hapus_mesin"])) {
    $id = intval($_POST["id_mesin"]);
    mysqli_query($conn, "DELETE FROM mesin WHERE id = $id");
    header("Location: mesin.php");
    exit;
}

// Ambil semua mesin
$result = mysqli_query($conn, "SELECT * FROM mesin ORDER BY id ASC");
$mesin_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Mesin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: "Poppins", sans-serif;
        }

        .container {
            padding: 30px 60px;
            max-width: 1200px;
            margin: auto;
        }

        h1 {
            margin-bottom: 8px;
            color: #333;
        }

        .breadcrumb {
            color: #777;
            font-size: 14px;
            margin-bottom: 25px;
        }

        /* Search bar */
        .search-box {
            width: 100%;
            max-width: 450px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            padding: 10px 15px;
        }

        .search-box input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 15px;
            background: transparent;
            color: #333;
        }

        .search-box i {
            color: #39c6ed;
            font-size: 18px;
            margin-right: 10px;
        }

        /* Tabel */
        table {
            width: 100px;
            border-collapse: collapse;
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 14px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 15px;
        }

        th {
            background: #39c6ed;
            color: white;
            text-transform: uppercase;
            font-size: 13.5px;
            letter-spacing: 0.5px;
        }

        tr:hover td {
            background: #f7fdff;
        }

        td a {
            color: #333;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
        }

        td a:hover {
            color: #39c6ed;
        }

        /* Tombol hapus */
        .hapus-btn {
            background: #ff4d4d;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
        }

        .hapus-btn:hover {
            background: #d93c3c;
            transform: scale(1.05);
        }

        /* Floating button */
        .float-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #39c6ed;
            color: white;
            font-size: 28px;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            border: none;
            box-shadow: 0 6px 15px rgba(57,198,237,0.4);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .float-btn:hover {
            background-color: #2da8cf;
            transform: scale(1.1);
        }

        /* Popup */
        .popup .popup-content {
            width: 100%;
            max-width: 400px;
            text-align: center;
            border-radius: 12px;
        }

        .popup-content h2 {
            margin-bottom: 15px;
            color: #333;
        }

        .popup-content input[type="text"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            transition: 0.2s;
        }

        .popup-content input:focus {
            border-color: #39c6ed;
            box-shadow: 0 0 5px rgba(57,198,237,0.4);
        }

        .popup-content button {
            background: #39c6ed;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
        }

        .popup-content button:hover {
            background: #2da8cf;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Daftar Mesin</h1>
        <p class="breadcrumb">Halaman / Mesin</p>

        <!-- 🔍 Search -->
        <div class="search-box">
            <i>🔍</i>
            <input type="text" id="searchMesin" placeholder="Cari nama mesin...">
        </div>

        <!-- 📋 Tabel Mesin -->
        <table id="mesinTable">
            <thead>
                <tr>
                    <th style="width:8%;">No</th>
                    <th>Nama Mesin</th>
                    <th style="width:15%; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($mesin_list) > 0): ?>
                    <?php $no = 1; foreach ($mesin_list as $m): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <a href="#" onclick="openRiwayatMesin('<?= htmlspecialchars($m['nama_mesin']) ?>')">
                                    <?= htmlspecialchars($m['nama_mesin']) ?>
                                </a>
                            </td>
                            <td style="text-align:center;">
                                <button class="hapus-btn" onclick="hapusMesin(event, <?= $m['id'] ?>)">Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center;">Belum ada data mesin.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ➕ Tombol Tambah Mesin -->
    <button class="float-btn" onclick="openTambahPopup()">+</button>

    <!-- 📋 Popup Tambah Mesin -->
    <div class="popup" id="popupTambah">
        <div class="popup-content">
            <span class="close-btn" onclick="closeTambahPopup()">&times;</span>
            <h2>Tambah Mesin Baru</h2>
            <form method="POST">
                <input type="text" name="nama_mesin" placeholder="Nama mesin baru" required>
                <button type="submit" name="tambah_mesin">Tambah</button>
            </form>
        </div>
    </div>

    <!-- 📋 Popup Riwayat Mesin -->
    <div class="popup" id="popupRiwayatMesin">
        <div class="popup-content" style="max-width:900px;">
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
                        <th>Kategori</th>
                    </tr>
                </thead>
                <tbody id="riwayatMesinTable">
                    <tr><td colspan="7" style="text-align:center;">Pilih mesin untuk melihat riwayat.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <form id="hapusForm" method="POST" style="display:none;">
        <input type="hidden" name="hapus_mesin" value="1">
        <input type="hidden" name="id_mesin" id="hapusMesinId">
    </form>

    <script>
        // 🔍 Live search mesin
        document.getElementById('searchMesin').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('#mesinTable tbody tr');
            rows.forEach(row => {
                const nama = row.cells[1].textContent.toLowerCase();
                row.style.display = nama.includes(query) ? '' : 'none';
            });
        });

        // 📋 Riwayat mesin
        function openRiwayatMesin(namaMesin) {
            const popup = document.getElementById('popupRiwayatMesin');
            const tbody = document.getElementById('riwayatMesinTable');
            document.getElementById('riwayatMesinTitle').textContent = `Riwayat Mesin: ${namaMesin}`;
            popup.style.display = 'flex';

            fetch(`riwayat_mesin.php?nama=${encodeURIComponent(namaMesin)}`)
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = "";
                    if (data.length === 0) {
                        tbody.innerHTML = "<tr><td colspan='7' style='text-align:center;'>Belum ada riwayat perbaikan.</td></tr>";
                        return;
                    }
                    data.forEach(item => {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>${item.tanggal}</td>
                            <td>${item.nama}</td>
                            <td>${item.divisi}</td>
                            <td>${item.pesan}</td>
                            <td>${item.nama_mekanik || '-'}</td>
                            <td>${item.pesan_mekanik || '-'}</td>
                            <td>${item.kategori}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(() => {
                    tbody.innerHTML = "<tr><td colspan='7' style='text-align:center;'>Gagal memuat data.</td></tr>";
                });
        }

        function closeRiwayatMesin() {
            document.getElementById('popupRiwayatMesin').style.display = 'none';
        }

        // ➕ Tambah mesin
        function openTambahPopup() {
            document.getElementById('popupTambah').style.display = 'flex';
        }

        function closeTambahPopup() {
            document.getElementById('popupTambah').style.display = 'none';
        }

        // 🗑️ Hapus mesin
        function hapusMesin(event, id) {
            event.stopPropagation();
            if (confirm("Yakin ingin menghapus mesin ini?")) {
                document.getElementById('hapusMesinId').value = id;
                document.getElementById('hapusForm').submit();
            }
        }
    </script>
</body>
</html>
