<?php
session_start();
include "koneksi.php";
include "sidebar.php";

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
    $mesin = $_POST['mesin'];
    $pesan = $_POST['pesan'];
    $kategori = $_POST['kategori'];

    $sql = "INSERT INTO laporan (nama, divisi, mesin, pesan, kategori, tanggal, status) 
            VALUES ('$nama', '$divisi', '$mesin', '$pesan', '$kategori', NOW(), 'kerusakan')";
    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard_laporan.php?success=1");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// === AMBIL LAPORAN OLEH MEKANIK ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ambil_laporan'])) {
    $id_laporan = $_POST['id_laporan'];
    $nama_mekanik_array = $_POST['nama_mekanik'] ?? [];
    $nama_mekanik = implode(", ", $nama_mekanik_array);

    if (empty($nama_mekanik)) {
        $res = mysqli_query($conn, "SELECT nama_mekanik FROM laporan WHERE id='$id_laporan'");
        if ($row = mysqli_fetch_assoc($res)) {
            $nama_mekanik = $row['nama_mekanik'];
        }
    }

    $sql = "UPDATE laporan 
            SET status='proses', nama_mekanik='$nama_mekanik' 
            WHERE id='$id_laporan'";

    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard_laporan.php?ambil=1");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
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
                    $result = mysqli_query($conn, "SELECT * FROM laporan WHERE status IN ('kerusakan','ditunda') ORDER BY tanggal DESC");
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
                                '" . htmlspecialchars($laporan['mesin'], ENT_QUOTES) . "',
                                '" . htmlspecialchars($laporan['pesan'], ENT_QUOTES) . "',
                                '{$laporan['status']}',
                                '" . htmlspecialchars($laporan['nama_mekanik'] ?? '', ENT_QUOTES) . "',
                                '" . htmlspecialchars($laporan['pesan_mekanik'] ?? '', ENT_QUOTES) . "'
                            )\">{$laporan['mesin']}</a>
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
                    style="background-color:#e74c3c; color:white; padding:8px 14px; border:none; border-radius:6px; cursor:pointer; margin-right:10px;">
                    Ya, Hapus
                </button>
                <button onclick="closeDeletePopup()"
                    style="background-color:#bdc3c7; color:black; padding:8px 14px; border:none; border-radius:6px; cursor:pointer;">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- Popup Tambah Laporan -->
    <div class="popup" id="popupForm">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup()">&times;</span>
            <h2>Registrasi Laporan</h2>
            <form method="POST">
                <label>Nama:</label>
                <input type="text" name="nama" required>
                <label>Divisi:</label>
                <select name="divisi" required>
                    <option value="Gudang">Gudang</option>
                    <option value="Hollow">Hollow</option>
                    <option value="Tiang">Tiang</option>
                    <option value="Slitter">Slitter</option>
                    <option value="Subcon">Subcon</option>
                    <option value="Grating">Grating</option>
                    <option value="H-Beam">H-Beam</option>
                </select>
                <label>Mesin:</label>
                <select name="mesin" required>
                    <option>CRANE FLEXOR S TON NO.1</option>
                    <option>CRANE FLEXOR 10 TON NO.1</option>
                    <option>CRANE FLEXOR 20 TON NO.4</option>
                    <option>CRANE FLEXOR 32 TON NO.3</option>
                    <option>CRANE KITO S TON NO.2</option>
                    <option>CRANE KITO S TON NO.3</option>
                    <option>CRANE KITO 3 TON NO.4</option>
                    <option>CRANE KITO 10 TON NO.3</option>
                    <option>MESIN MINI FURING NO.3</option>
                    <option>MESIN LAS GRATING</option>
                    <option>MESIN SLITTER KAE CHUAN</option>
                    <option>MESIN LAS H BEAM NO.1</option>
                    <option>MESIN PELURUS TIANG NO.2</option>
                    <option>TRAFO LAS MIG ESAB MCTF - 76</option>
                </select>
                <label>Pesan Kerusakan:</label>
                <textarea name="pesan" rows="3" required></textarea>
                <label>Kategori:</label>
                <select name="kategori" required>
                    <option value="Biasa">Biasa</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Urgent">Urgent</option>
                </select>
                <button type="submit" name="submit_laporan">Kirim Laporan</button>
            </form>
        </div>
    </div>

    <!-- Popup Detail Laporan -->
    <div class="popup" id="popupDetail" style="display:none;">
        <div class="popup-content">
            <span class="close-btn" onclick="closeDetailPopup()">&times;</span>
            <h2>Detail Laporan</h2>
            <form method="POST">
                <input type="hidden" name="id_laporan" id="detail_id">
                <label>Nama Mesin:</label>
                <input type="text" id="detail_mesin" readonly>
                <label>Keterangan:</label>
                <textarea id="detail_pesan" readonly></textarea>
                <label for="mekanik">Pilih Mekanik:</label>
                <div class="mekanik-list" id="mekanikList">
                    <?php
                    $mekanik_result = mysqli_query($conn, "SELECT * FROM mekanik ORDER BY nama ASC");
                    while ($m = mysqli_fetch_assoc($mekanik_result)) {
                        $id = strtolower(str_replace(' ', '_', $m['nama']));
                        echo "
        <div class='mekanik-item'>
            <input type='checkbox' name='nama_mekanik[]' value='{$m['nama']}' id='{$id}'>
            <label for='{$id}'>{$m['nama']}</label>
        </div>";
                    }
                    ?>
                </div>
                <button type="submit" name="ambil_laporan" id="ambilBtn"
                    style="background-color: #39c6ed; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer;">
                    Ambil
                </button>
            </form>
        </div>
    </div>

    <script>
        function openPopup() { document.getElementById('popupForm').style.display = 'flex'; }
        function closePopup() { document.getElementById('popupForm').style.display = 'none'; }

        function openDetailPopup(id, mesin, pesan, status, mekanik, pesan_mekanik) {
            document.getElementById('popupDetail').style.display = 'flex';
            document.getElementById('detail_id').value = id;
            document.getElementById('detail_mesin').value = mesin;

            let gabungPesan = pesan;
            if (pesan_mekanik && pesan_mekanik !== '-') {
                gabungPesan += "\n\nCatatan Mekanik: " + pesan_mekanik;
            }
            document.getElementById('detail_pesan').value = gabungPesan;

            document.querySelectorAll('#mekanikList input[type=checkbox]').forEach(cb => {
                cb.checked = false;
                cb.disabled = false;
            });

            if (mekanik) {
                mekanik.split(", ").forEach(nama => {
                    document.querySelectorAll('#mekanikList input[type=checkbox]').forEach(cb => {
                        if (cb.value === nama) cb.checked = true;
                    });
                });
            }

            if (status === 'proses') {
                document.querySelectorAll('#mekanikList input[type=checkbox]').forEach(cb => cb.disabled = true);
                document.getElementById('ambilBtn').style.display = 'none';
            } else if (status === 'ditunda') {
                document.querySelectorAll('#mekanikList input[type=checkbox]').forEach(cb => cb.disabled = true);
                document.getElementById('ambilBtn').style.display = 'block';
            } else {
                document.querySelectorAll('#mekanikList input[type=checkbox]').forEach(cb => cb.disabled = false);
                document.getElementById('ambilBtn').style.display = 'block';
            }
        }

        function closeDetailPopup() { document.getElementById('popupDetail').style.display = 'none'; }

        function toggleAll(source) {
            const checkboxes = document.querySelectorAll('input[name="selected_id[]"]');
            checkboxes.forEach(cb => cb.checked = source.checked);
        }

        function openDeletePopup() {
            const selected = document.querySelectorAll('input[name="selected_id[]"]:checked');
            if (selected.length === 0) {
                showTempAlert("⚠️ Pilih laporan yang ingin dihapus terlebih dahulu!", "warning");
                return;
            }
            document.getElementById('popupDelete').style.display = 'flex';
        }

        function closeDeletePopup() { document.getElementById('popupDelete').style.display = 'none'; }
        function confirmDelete() {
            document.getElementById('popupDelete').style.display = 'none';
            document.getElementById('hapusForm').submit();
        }

        function filterTable() {
            const kategoriFilter = document.getElementById('filterKategori').value;
            const statusFilter = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('#laporanTable tbody tr');

            rows.forEach(row => {
                const kategori = row.dataset.kategori;
                const status = row.querySelector('.status').textContent.trim();

                const matchKategori = (kategoriFilter === 'all' || kategori === kategoriFilter);
                const matchStatus = (statusFilter === 'all' || status === statusFilter);

                row.style.display = (matchKategori && matchStatus) ? '' : 'none';
            });
        }

        setTimeout(() => {
            let msg = document.getElementById("successMsg");
            if (msg) msg.style.display = "none";
        }, 2000);

        function showTempAlert(message, type = "info") {
            // Hapus alert sebelumnya (jika ada)
            const existingAlert = document.getElementById("tempAlert");
            if (existingAlert) existingAlert.remove();

            // Buat elemen alert
            const alertBox = document.createElement("div");
            alertBox.id = "tempAlert";
            alertBox.textContent = message;
            alertBox.className = "temp-alert " + type;

            document.body.appendChild(alertBox);

            // Hilangkan otomatis setelah 2.5 detik
            setTimeout(() => {
                alertBox.style.opacity = "0";
                setTimeout(() => alertBox.remove(), 500);
            }, 2500);
        }

    </script>
</body>

</html>
