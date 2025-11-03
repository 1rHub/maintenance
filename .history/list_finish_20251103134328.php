<?php
session_start();
include "koneksi.php";
include "sidebar.php";
date_default_timezone_set('Asia/Jakarta'); // 🕐 pastikan waktu sesuai WIB
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>List Finish</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="header-container">
            <div>
                <h1>LAPORAN SELESAI</h1>
                <p class="breadcrumb">Halaman / List Finish</p>
            </div>
            <div class="menu-opsi">
                <button class="menu-btn" onclick="toggleMenu()">⋮</button>
                <div id="dropdown" class="dropdown-menu">
                    <button onclick="cetakPDF()">Cetak sebagai PDF</button>
                    <button onclick="simpanGambar()">Simpan sebagai Gambar</button>
                </div>
            </div>
        </div>

        <div class="filter">
            <label for="filterTanggal">Tanggal:</label>
            <input type="date" id="filterTanggal" onchange="filterTable()">

            <label for="filterKategori">Kategori:</label>
            <select id="filterKategori" onchange="filterTable()">
                <option value="all">Semua</option>
                <option value="Biasa">Biasa</option>
                <option value="Sedang">Sedang</option>
                <option value="Urgent">Urgent</option>
            </select>

            <button onclick="resetFilter()">Reset Filter</button>
        </div>

        <div id="laporanWrapper">
            <div class="laporan-header">
                <img src="assets/image/logo_mulcindo.png" alt="Logo Perusahaan">
                <h2>LAPORAN SELESAI</h2>
            </div>

            <table id="laporanTable">
                <thead>
                    <tr>
                        <th>Tanggal Registrasi</th>
                        <th>Tanggal Ditunda</th> <!-- 🆕 -->
                        <th>Tanggal Selesai</th>
                        <th>Durasi (Jam)</th> <!-- 🆕 -->
                        <th>Mesin</th>
                        <th>Keterangan</th>
                        <th>Nama Pelapor</th>
                        <th>Divisi</th>
                        <th>Nama Mekanik</th>
                        <th>Pesan Mekanik</th>
                        <th>Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = mysqli_query($conn, "
                        SELECT 
                            l.*, 
                            m.nama_mesin,
                            GROUP_CONCAT(mk.nama SEPARATOR ', ') AS nama_mekanik
                        FROM laporan l
                        LEFT JOIN mesin m ON l.mesin_id = m.id
                        LEFT JOIN mekanik mk ON FIND_IN_SET(mk.id, l.mekanik_id)
                        WHERE l.status = 'finish'
                        GROUP BY l.id
                        ORDER BY l.tanggal_selesai DESC
                    ");

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $tanggal_pure = explode(' ', $row['tanggal'])[0];
                            $kategoriClass = strtolower($row['kategori']);

                            // 🧮 Hitung durasi kerja (jam)
                            $startTime = $row['tanggal_tunda'] ? $row['tanggal_tunda'] : $row['tanggal'];
                            $endTime = $row['tanggal_selesai'];

                            $durasiJam = '-';
                            if ($endTime && $startTime) {
                                $start = strtotime($startTime);
                                $end = strtotime($endTime);
                                $diffHours = round(($end - $start) / 3600, 1);
                                $durasiJam = $diffHours . " Jam";
                            }

                            echo "<tr data-kategori='{$row['kategori']}' data-tanggal='{$tanggal_pure}'>
                                <td>{$row['tanggal']}</td>
                                <td>" . ($row['tanggal_tunda'] ?? '-') . "</td> <!-- 🆕 -->
                                <td>{$row['tanggal_selesai']}</td>
                                <td>{$durasiJam}</td> <!-- 🆕 -->
                                <td>{$row['nama_mesin']}</td>
                                <td>{$row['pesan']}</td>
                                <td>{$row['nama']}</td>
                                <td>{$row['divisi']}</td>
                                <td>" . ($row['nama_mekanik'] ?? '-') . "</td>
                                <td>{$row['pesan_mekanik']}</td>
                                <td class='kategori {$kategoriClass}'>{$row['kategori']}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11' style='text-align:center; padding:15px;'>Tidak ada laporan selesai.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <p id="noData">Tidak ada laporan selesai.</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        function toggleMenu() {
            const menu = document.getElementById("dropdown");
            menu.style.display = menu.style.display === "block" ? "none" : "block";
        }

        document.addEventListener("click", function(event) {
            const menu = document.getElementById("dropdown");
            const button = document.querySelector(".menu-btn");
            if (!button.contains(event.target) && !menu.contains(event.target)) {
                menu.style.display = "none";
            }
        });

        function filterTable() {
            const kategori = document.getElementById('filterKategori').value;
            const tanggal = document.getElementById('filterTanggal').value;
            const rows = document.querySelectorAll('#laporanTable tbody tr');
            const noDataMsg = document.getElementById('noData');
            let visibleCount = 0;

            rows.forEach(row => {
                const rowTanggal = row.dataset.tanggal;
                const matchKategori = (kategori === 'all' || row.dataset.kategori === kategori);
                const matchTanggal = (!tanggal || rowTanggal === tanggal);
                const visible = (matchKategori && matchTanggal);

                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            noDataMsg.style.display = (visibleCount === 0) ? 'block' : 'none';
        }

        function resetFilter() {
            document.getElementById('filterTanggal').value = '';
            document.getElementById('filterKategori').value = 'all';
            filterTable();
        }

        async function cetakPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const wrapper = document.getElementById('laporanWrapper');
            const canvas = await html2canvas(wrapper, { scale: 2 });
            const imgData = canvas.toDataURL('image/png');
            const pdfWidth = doc.internal.pageSize.getWidth();
            const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
            doc.addImage(imgData, 'PNG', 0, 10, pdfWidth, pdfHeight);
            doc.save('laporan_selesai.pdf');
        }

        async function simpanGambar() {
            const wrapper = document.getElementById('laporanWrapper');
            const canvas = await html2canvas(wrapper, { scale: 2 });
            const link = document.createElement('a');
            link.download = 'laporan_selesai.png';
            link.href = canvas.toDataURL();
            link.click();
        }
    </script>
</body>
</html>
