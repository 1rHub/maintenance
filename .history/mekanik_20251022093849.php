<?php
include "koneksi.php";
include "sidebar.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["tambah_mekanik"])) {
    $nama = trim($_POST["nama_mekanik"]);
    if ($nama !== "") {
        mysqli_query($conn, "INSERT INTO mekanik (nama) VALUES ('$nama')");
    }
    header("Location: mekanik.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["hapus_mekanik"])) {
    $nama = mysqli_real_escape_string($conn, $_POST["nama_mekanik"]);
    mysqli_query($conn, "DELETE FROM mekanik WHERE nama='$nama'");
    header("Location: mekanik.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_mekanik"])) {
    $old = mysqli_real_escape_string($conn, $_POST["old_name"]);
    $new = mysqli_real_escape_string($conn, $_POST["new_name"]);
    mysqli_query($conn, "UPDATE mekanik SET nama='$new' WHERE nama='$old'");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM mekanik ORDER BY id ASC");
$mekanik_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<link rel="stylesheet" href="assets/css/style.css">

<head>
    <meta charset="UTF-8">
    <title>Data Mekanik</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f5f7fb;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .mekanik-container {
            padding: 30px 50px;
            margin-left: 250px;
        }

        h1 {
            color: #222;
            font-size: 26px;
            margin-bottom: 5px;
        }

        .breadcrumb {
            font-size: 14px;
            color: #888;
            margin-bottom: 25px;
        }

        .mekanik-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .mekanik-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 18px 14px;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s;
            cursor: pointer;
        }

        .mekanik-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .editable {
            border: none;
            font-size: 16px;
            font-weight: 500;
            text-align: center;
            width: 100%;
            color: #333;
            background: transparent;
            outline: none;
        }

        /* Floating Add Button */
        .float-btn {
            position: fixed;
            bottom: 30px;
            right: 40px;
            background: #39c6ed;
            color: white;
            border: none;
            border-radius: 50%;
            width: 55px;
            height: 55px;
            font-size: 32px;
            line-height: 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: 0.3s;
        }

        .float-btn:hover {
            background: #2aa8cc;
            transform: scale(1.08);
        }

        /* Popup Styles */
        .popup {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.45);
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            background: white;
            padding: 25px 30px;
            border-radius: 14px;
            width: 450px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            animation: popupFade 0.25s ease;
        }

        @keyframes popupFade {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .popup-content h2 {
            margin-bottom: 15px;
            font-size: 20px;
            text-align: center;
            color: #222;
        }

        .close-btn {
            position: absolute;
            right: 25px;
            top: 15px;
            font-size: 22px;
            cursor: pointer;
            color: #666;
        }

        .close-btn:hover {
            color: #000;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }

        table th {
            background: #f0f4f8;
            color: #333;
            font-weight: 600;
        }

        .btn-row {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .edit-btn, .hapus-btn {
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            transition: 0.3s;
            font-size: 14px;
        }

        .edit-btn {
            background-color: #3498db;
        }

        .hapus-btn {
            background-color: #e74c3c;
        }

        .edit-btn:hover {
            background-color: #2a80b9;
        }

        .hapus-btn:hover {
            background-color: #c0392b;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 8px 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
            font-family: inherit;
        }

        input[type="text"]:focus {
            border-color: #39c6ed;
        }
    </style>
</head>

<body>
    <div class="mekanik-container">
        <h1>Daftar Mekanik</h1>
        <p class="breadcrumb">Halaman / Mekanik</p>

        <div class="mekanik-list">
            <?php foreach ($mekanik_list as $m): ?>
                <div class="mekanik-card" onclick="openRiwayat('<?= htmlspecialchars($m['nama']) ?>')">
                    <input type="text" class="editable" value="<?= htmlspecialchars($m['nama']) ?>" readonly
                        ondblclick="enableEdit(this)" onblur="saveEdit(this)" onkeypress="handleEnter(event, this)">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button class="float-btn" onclick="openPopup()">+</button>

    <!-- Popup Tambah Mekanik -->
    <div class="popup" id="popupTambah">
        <div class="popup-content" style="width:350px;">
            <span class="close-btn" onclick="closePopup()">&times;</span>
            <h2>Tambah Mekanik</h2>
            <form method="POST">
                <input type="text" name="nama_mekanik" placeholder="Nama mekanik baru" required>
                <button type="submit" name="tambah_mekanik"
                    style="margin-top:10px; background:#39c6ed; color:white; padding:8px 16px; border:none; border-radius:6px;">Tambah</button>
            </form>
        </div>
    </div>

    <!-- Popup Riwayat Mekanik -->
    <div class="popup" id="popupRiwayat">
        <div class="popup-content">
            <span class="close-btn" onclick="closeRiwayatPopup()">&times;</span>
            <h2 id="riwayatTitle">Riwayat Mekanik</h2>
            <input type="text" id="editNamaInput" placeholder="Nama baru mekanik">

            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Mesin</th>
                        <th>Keterangan</th>
                        <th>Pesan Mekanik</th>
                    </tr>
                </thead>
                <tbody id="riwayatTable">
                    <tr>
                        <td colspan="4" style="text-align:center;">Pilih mekanik untuk melihat riwayat</td>
                    </tr>
                </tbody>
            </table>

            <div class="btn-row">
                <button class="edit-btn" id="editNamaBtn" onclick="toggleEditNama()">Edit Nama</button>
                <button class="hapus-btn" id="hapusPopupBtn" onclick="hapusMekanikPopup()">Hapus Mekanik</button>
            </div>
        </div>
    </div>

    <form id="hapusForm" method="POST" style="display:none;">
        <input type="hidden" name="hapus_mekanik" value="1">
        <input type="hidden" name="nama_mekanik" id="hapusNama">
    </form>

    <script>
        let currentMekanik = "";

        function openPopup() { document.getElementById('popupTambah').style.display = 'flex'; }
        function closePopup() { document.getElementById('popupTambah').style.display = 'none'; }

        function enableEdit(input) {
            input.removeAttribute('readonly');
            input.style.borderBottom = "1px solid #39c6ed";
            input.focus();
        }
        function handleEnter(e, input) {
            if (e.key === "Enter") { e.preventDefault(); input.blur(); }
        }
        function saveEdit(input) {
            input.setAttribute("readonly", true);
            input.style.borderBottom = "none";
            const oldName = input.defaultValue;
            const newName = input.value.trim();
            if (newName === "" || newName === oldName) return;
            const formData = new FormData();
            formData.append("update_mekanik", "1");
            formData.append("old_name", oldName);
            formData.append("new_name", newName);
            fetch("mekanik.php", { method: "POST", body: formData })
                .then(() => { input.defaultValue = newName; showAlert("✅ Nama mekanik berhasil diubah!"); })
                .catch(() => showAlert("❌ Gagal mengubah nama mekanik!"));
        }

        function openRiwayat(nama) {
            currentMekanik = nama;
            document.getElementById('riwayatTitle').textContent = `Riwayat ${nama}`;
            document.getElementById('popupRiwayat').style.display = 'flex';
            document.getElementById('editNamaInput').style.display = 'none';
            document.getElementById('editNamaInput').value = '';

            fetch(`riwayat_mekanik.php?nama=${encodeURIComponent(nama)}`)
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('riwayatTable');
                    tbody.innerHTML = "";
                    if (data.length === 0) {
                        tbody.innerHTML = "<tr><td colspan='4' style='text-align:center;'>Belum ada riwayat perbaikan.</td></tr>";
                        return;
                    }
                    data.forEach(item => {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>${item.tanggal}</td>
                            <td>${item.mesin}</td>
                            <td>${item.pesan}</td>
                            <td>${item.pesan_mekanik}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(() => {
                    const tbody = document.getElementById('riwayatTable');
                    tbody.innerHTML = "<tr><td colspan='4' style='text-align:center;'>Gagal memuat data.</td></tr>";
                });
        }

        function toggleEditNama() {
            const input = document.getElementById('editNamaInput');
            if (input.style.display === 'none') {
                input.style.display = 'block';
                input.value = currentMekanik;
                input.focus();
            } else {
                const newName = input.value.trim();
                if (newName === "" || newName === currentMekanik) {
                    input.style.display = 'none';
                    return;
                }
                const formData = new FormData();
                formData.append("update_mekanik", "1");
                formData.append("old_name", currentMekanik);
                formData.append("new_name", newName);

                fetch("mekanik.php", { method: "POST", body: formData })
                    .then(() => {
                        showAlert("✅ Nama mekanik berhasil diubah!");
                        currentMekanik = newName;
                        document.getElementById('riwayatTitle').textContent = `Riwayat ${newName}`;
                        input.style.display = 'none';
                        setTimeout(() => location.reload(), 700);
                    })
                    .catch(() => showAlert("❌ Gagal mengubah nama mekanik!"));
            }
        }

        function hapusMekanikPopup() {
            if (!confirm(`Yakin ingin menghapus mekanik ${currentMekanik}?`)) return;
            document.getElementById('hapusNama').value = currentMekanik;
            document.getElementById('hapusForm').submit();
        }

        function closeRiwayatPopup() {
            document.getElementById('popupRiwayat').style.display = 'none';
        }

        function showAlert(msg) {
            const alert = document.createElement('div');
            alert.textContent = msg;
            alert.style.position = 'fixed';
            alert.style.bottom = '30px';
            alert.style.right = '30px';
            alert.style.background = '#39c6ed';
            alert.style.color = 'white';
            alert.style.padding = '10px 15px';
            alert.style.borderRadius = '8px';
            alert.style.boxShadow = '0 4px 10px rgba(0,0,0,0.3)';
            alert.style.zIndex = '9999';
            document.body.appendChild(alert);
            setTimeout(() => { alert.style.opacity = '0'; setTimeout(() => alert.remove(), 500); }, 2000);
        }
    </script>
</body>

</html>