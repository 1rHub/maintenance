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
    header("Location: mesin.php");
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
<style>
body {
    font-family: 'Poppins', sans-serif;
}

/* === MAIN CONTAINER === */
.main-container {
    margin-left: 30px;
    padding: 40px 10px 80px;
    overflow-x: hidden;
}
#mesinTable {
  width: 100%;
  min-width: 900px;
  table-layout: auto;
}

.header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 25px;
}

h1 {
    font-size: 26px;
    font-weight: 700;
    color: #333;
    margin: 0;
}

.breadcrumb {
    color: #888;
    font-size: 14px;
    margin-bottom: 30px;
}

/* === SEARCH BOX === */
.search-box {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    padding: 8px 12px;
    width: 250px;
}

.search-box input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 15px;
}

.table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    min-width: 700px;
}

th, td {
    padding: 8px 16px;
    border-bottom: 1px solid #eee;
}

th {
    background: #39c6ed;
    color: white;
    text-transform: uppercase;
    font-size: 14px;
}

td {
    color: #333;
    font-weight: 500;
}

/* === CHECKBOX === */
input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

/* === BUTTON HAPUS DI LUAR TABEL === */
.hapus-btn {
    background: #e74c3c;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: 0.2s;
    margin-bottom: 20px;
}

.hapus-btn:hover {
    background: #c0392b;
    transform: scale(1.05);
}

/* === FLOATING ADD BUTTON === */
.float-btn {
    position: fixed;
    bottom: 30px;
    right: 40px;
    background-color: #39c6ed;
    color: #fff;
    font-size: 28px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    border: none;
    box-shadow: 0 6px 15px rgba(57,198,237,0.4);
    cursor: pointer;
    transition: 0.3s ease;
}

.float-btn:hover {
    background-color: #2da8cf;
    transform: scale(1.1);
}
.popup {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.4);
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.popup-content {
  background: #fff;
  border-radius: 12px;
  padding: 20px 30px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  animation: fadeIn 0.2s ease;
}

.close-btn2 {
  float: right;
  font-size: 22px;
  cursor: pointer;
}

.popup-content form input[type="text"] {
  width: 100%;
  padding: 10px;
  margin-bottom: 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.popup-content button {
  background: #39c6ed;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
}

.popup-content button:hover {
  background: #2da8cf;
}

.popup2 .popup-content2 {
            width: 100%;
            max-width: 400px;
            text-align: center;
            background: #fff;
            border-radius: 15px;
            padding: 25px 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .popup-content h2 {
            margin-bottom: 15px;
            font-size: 20px;
            color: #333;
        }

        .popup-content2 input[type="text"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            transition: 0.2s;
        }

        .popup-content input:focus {
            border-color: #39c6ed;
            box-shadow: 0 0 5px rgba(57,198,237,0.3);
        }

        .popup-content button {
            background: #39c6ed;
            color: #fff;
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
<div class="main-container">

    <!-- === HEADER ROW === -->
    <div class="header-row">
        <h1>Daftar Mesin</h1>
        <div class="search-box">
            <input type="text" id="searchMesin" placeholder="Cari nama mesin...">
        </div>
    </div>
    <p class="breadcrumb">Halaman / Mesin</p>

    <!-- === BUTTON HAPUS DI LUAR TABEL === -->
    <form method="POST" id="formHapus">
        <button type="submit" name="hapus_laporan" class="hapus-btn" onclick="return confirm('Yakin ingin menghapus data terpilih?')">Hapus Data Terpilih</button>

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
                        <?php $no = 1; foreach ($mesin_list as $m): ?>
                            <tr class="data-row">
                                <td style="text-align:center;">
                                    <input type="checkbox" name="selected_id[]" value="<?= $m['id'] ?>">
                                </td>
                                <td><?= $no++ ?></td>
                                <td>
                                    <a href="#" onclick="openRiwayatMesin('<?= htmlspecialchars($m['nama_mesin']) ?>')">
                                        <?= htmlspecialchars($m['nama_mesin']) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center;">Belum ada data mesin.</td></tr>
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
<div class="popup2" id="popupRiwayatMesin">
    <div class="popup-content2" style="max-width:900px;">
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
                <tr><td colspan="7" style="text-align:center;">Pilih mesin untuk melihat riwayat.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// === SELECT ALL CHECKBOX ===
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="selected_id[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// === LIVE SEARCH ===
document.getElementById('searchMesin').addEventListener('input', function() {
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
function openRiwayatMesin(nama) {
    document.getElementById('riwayatMesinTitle').textContent = "Riwayat Mesin: " + nama;
    document.getElementById('popupRiwayatMesin').style.display = 'flex';
}

function closeRiwayatMesin() {
    document.getElementById('popupRiwayatMesin').style.display = 'none';
}
</script>

</body>
</html>
