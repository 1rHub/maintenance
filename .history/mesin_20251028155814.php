<?php
include "koneksi.php";
include "sidebar.php";

// === Tambah Mesin ===
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["tambah_mesin"])) {
    $nama = trim($_POST["nama_mesin"]);
    if ($nama !== "") {
        mysqli_query($conn, "INSERT INTO mesin (nama_mesin) VALUES ('$nama')");
    }
    header("Location: mesin.php");
    exit;
}

// === Hapus Mesin (multi delete) ===
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["hapus_laporan"])) {
    if (!empty($_POST["selected_id"])) {
        $ids = implode(",", array_map('intval', $_POST["selected_id"]));
        mysqli_query($conn, "DELETE FROM mesin WHERE id IN ($ids)");
    }
    header("Location: mesin.php?hapus=1");
    exit;
}

// === Ambil Semua Mesin ===
$result = mysqli_query($conn, "SELECT * FROM mesin ORDER BY id ASC");
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
    <div class="main-container">

        <!-- === HEADER ROW === -->
        <div class="header-row">
            <h1>DAFTAR MESIN</h1>
            <div class="search-box">
                <input type="text" id="searchMesin" placeholder="Cari nama mesin...">
            </div>
        </div>
        <p class="breadcrumb">Halaman / Mesin</p>

        <!-- === BUTTON HAPUS DI LUAR TABEL === -->
        <form method="POST" id="formHapus">
            <input type="hidden" name="hapus_laporan" value="1">
            <button type="button" class="hapus-btn" onclick="showHapusPopup()">Hapus Data Terpilih</button>

            <div class="table-wrapper">
                <table id="mesinTable" class="table">
                    <thead>
                        <tr>
                            <th style="width:5%; text-align:center;"><input type="checkbox" id="selectAll"></th>
                            <th style="width:10%;">No</th>
                            <th>Nama Mesin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($mesin_list) > 0): ?>
                            <?php $no = 1;
                            foreach ($mesin_list as $m): ?>
                                <tr class="data-row">
                                    <td style="text-align:center;">
                                        <input type="checkbox" name="selected_id[]" value="<?= $m['id'] ?>">
                                    </td>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <a href="#" onclick="openRiwayatMesin(<?= $m['id'] ?>, '<?= htmlspecialchars($m['nama_mesin']) ?>')">

                                            <?= htmlspecialchars($m['nama_mesin']) ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align:center;">Belum ada data mesin.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>

    </div>

    <!-- ➕ Floating Add Button -->
    <button class="float-btn" onclick="openTambahPopup()">+</button>

    <!-- === POPUP TAMBAH (SAMA SEPERTI KODEMU) === -->
    <div class="popup" id="popupTambah">
        <div class="popup-content">
            <span class="close-btn2" onclick="closeTambahPopup()">&times;</span>
            <h2>Tambah Mesin Baru</h2>
            <form method="POST">
                <input type="text" name="nama_mesin" placeholder="Nama mesin baru" required>
                <button type="submit" name="tambah_mesin">Tambah</button>
            </form>
        </div>
    </div>

    <!-- === POPUP RIWAYAT (SAMA SEPERTI KODEMU) === -->
    <div class="popup" id="popupRiwayatMesin">
        <div class="popup-content" style="max-width:900px;">
            <span class="close-btn2" onclick="closeRiwayatMesin()">&times;</span>
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
                    <tr>
                        <td colspan="7" style="text-align:center;">Pilih mesin untuk melihat riwayat.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- === POPUP KONFIRMASI HAPUS === -->
    <div class="popup" id="popupHapus">
        <div class="popup-content" style="width:380px; text-align:center;">
            <h3 style="margin-bottom:15px;">Konfirmasi Hapus</h3>
            <p style="color:#555; margin-bottom:25px;">Apakah kamu yakin ingin menghapus data mesin terpilih?</p>
            <div style="display:flex; justify-content:center; gap:15px;">
                <button onclick="closeHapusPopup()"
                    style="background:#ccc; border:none; color:#333; padding:8px 14px; border-radius:6px; cursor:pointer;">Batal</button>
                <button onclick="confirmDelete()" id="btnHapusYa"
                    style="background:#e74c3c; border:none; color:white; padding:8px 14px; border-radius:6px; cursor:pointer;">Ya,
                    Hapus</button>
            </div>
        </div>
    </div>


    <script>
        // === SELECT ALL CHECKBOX ===
        document.getElementById('selectAll').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="selected_id[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        // === LIVE SEARCH ===
        document.getElementById('searchMesin').addEventListener('input', function () {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('#mesinTable tbody tr.data-row');
            let visible = 0;
            rows.forEach(row => {
                const nama = row.cells[2].textContent.toLowerCase();
                if (nama.includes(query)) {
                    row.style.display = '';
                    visible++;
                } else {
                    row.style.display = 'none';
                }
            });

            const tbody = document.querySelector('#mesinTable tbody');
            let emptyRow = document.getElementById('noResultRow');
            if (visible === 0) {
                if (!emptyRow) {
                    emptyRow = document.createElement('tr');
                    emptyRow.id = 'noResultRow';
                    emptyRow.innerHTML = `<td colspan="3" style="text-align:center; height:60px; color:#888;">Tidak ada mesin yang cocok.</td>`;
                    tbody.appendChild(emptyRow);
                }
            } else if (emptyRow) {
                emptyRow.remove();
            }
        });

        // === POPUP TAMBAH MESIN ===
        function openTambahPopup() {
            document.getElementById('popupTambah').style.display = 'flex';
        }

        function closeTambahPopup() {
            document.getElementById('popupTambah').style.display = 'none';
        }

        // === POPUP RIWAYAT MESIN ===
        function openRiwayatMesin(id, nama) {
    const popup = document.getElementById('popupRiwayatMesin');
    const tbody = document.getElementById('riwayatMesinTable');
    const title = document.getElementById('riwayatMesinTitle');

    title.textContent = "Riwayat Mesin: " + nama;
    popup.style.display = 'flex';
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Memuat data...</td></tr>`;

    fetch('riwayat_mesin.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Belum ada riwayat perbaikan untuk mesin ini.</td></tr>`;
            } else {
                tbody.innerHTML = data.map(r => `
                    <tr>
                        <td>${r.tanggal}</td>
                        <td>${r.nama}</td>
                        <td>${r.divisi}</td>
                        <td>${r.pesan}</td>
                        <td>${r.nama_mekanik || '-'}</td>
                        <td>${r.pesan_mekanik}</td>
                        <td>${r.kategori}</td>
                    </tr>
                `).join('');
            }
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:red;">Gagal memuat data.</td></tr>`;
        });
}


        function closeRiwayatMesin() {
            document.getElementById('popupRiwayatMesin').style.display = 'none';
        }

        // === POPUP VALIDASI HAPUS ===
        function showHapusPopup() {
            const checkboxes = document.querySelectorAll('input[name="selected_id[]"]:checked');
            if (checkboxes.length === 0) {
                alert("Pilih minimal satu data mesin untuk dihapus.");
                return;
            }
            document.getElementById('popupHapus').style.display = 'flex';
        }

        function closeHapusPopup() {
            document.getElementById('popupHapus').style.display = 'none';
        }

        document.getElementById('btnHapusYa').addEventListener('click', function () {
            document.getElementById('formHapus').submit();
        });

    </script>

</body>

</html>