<?php
include "koneksi.php";
include "sidebar.php";

// Ambil semua mesin
$result = mysqli_query($conn, "SELECT * FROM mesin ORDER BY nama_mesin ASC");
$mesin_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Mesin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Search bar */
        .search-box {
            width: 100%;
            max-width: 400px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 10px 15px;
        }

        .search-box input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 15px;
            padding-left: 5px;
            background: transparent;
        }

        .search-box i {
            color: #39c6ed;
            font-size: 18px;
            margin-right: 8px;
        }

        /* Mesin cards - horizontal & responsif */
        .mesin-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
            margin-top: 15px;
        }

        .mesin-card {
            flex: 0 0 220px; /* lebar tetap agar sejajar */
            background: white;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            font-weight: 600;
            color: #333;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .mesin-card:hover {
            background: #39c6ed;
            color: white;
            transform: translateY(-4px);
            box-shadow: 0 5px 15px rgba(57,198,237,0.4);
        }

        /* Popup Riwayat */
        #popupRiwayatMesin .popup-content {
            width: 90%;
            max-width: 900px;
            max-height: 85vh;
            overflow-y: auto;
        }

        #popupRiwayatMesin table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        #popupRiwayatMesin th, #popupRiwayatMesin td {
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 13px;
            text-align: left;
        }

        #popupRiwayatMesin thead {
            background: #39c6ed;
            color: white;
        }

        #popupRiwayatMesin h2 {
            margin-bottom: 10px;
        }

        #popupRiwayatMesin p {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        
        .breadcrumb {
            padding-bottom: 20px;
        }

        /* Responsif di HP */
        @media (max-width: 600px) {
            .mesin-card {
                flex: 0 0 100%;
            }
        }
    </style>
</head>
<body>
    <div class="mekanik-container">
        <h1>Daftar Mesin</h1>
        <p class="breadcrumb">Halaman / Mesin</p>

        <!-- 🔍 Search -->
        <div class="search-box">
            <i>🔍</i>
            <input type="text" id="searchMesin" placeholder="Cari nama mesin...">
        </div>

        <!-- 🧱 Daftar Mesin -->
        <div class="mesin-list" id="mesinList">
            <?php foreach ($mesin_list as $m): ?>
                <div class="mesin-card" onclick="openRiwayatMesin('<?= htmlspecialchars($m['nama_mesin']) ?>')">
                    <?= htmlspecialchars($m['nama_mesin']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 📋 Popup Riwayat Mesin -->
    <div class="popup" id="popupRiwayatMesin">
        <div class="popup-content">
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

    <script>
        // 🔍 Live search mesin
        document.getElementById('searchMesin').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const cards = document.querySelectorAll('.mesin-card');
            cards.forEach(card => {
                const nama = card.textContent.toLowerCase();
                card.style.display = nama.includes(query) ? 'block' : 'none';
            });
        });

        // 📋 Buka popup riwayat mesin
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
    </script>
</body>
</html>
