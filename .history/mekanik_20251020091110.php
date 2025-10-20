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
</head>
<body>
    <div class="mekanik-container">
        <h1>Daftar Mekanik</h1>
        <p class="breadcrumb">Halaman / Mekanik</p>

        <div class="mekanik-list">
            <?php foreach ($mekanik_list as $m): ?>
                <div class="mekanik-card" onclick="openRiwayat('<?= htmlspecialchars($m['nama']) ?>')">
                    <input type="text"
                        class="editable"
                        value="<?= htmlspecialchars($m['nama']) ?>"
                        readonly
                        ondblclick="enableEdit(this)"
                        onblur="saveEdit(this)"
                        onkeypress="handleEnter(event, this)">
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
                <button type="submit" name="tambah_mekanik" style="margin-top:10px; background:#39c6ed; color:white; padding:8px 16px; border:none; border-radius:6px;">Tambah</button>
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
                    <tr><td colspan="4" style="text-align:center;">Pilih mekanik untuk melihat riwayat</td></tr>
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
