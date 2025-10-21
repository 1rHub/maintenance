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

    // Jika tidak ada mekanik dipilih
    if (empty($mekanik_ids)) {
        echo "<script>alert('Pilih minimal satu mekanik.'); history.back();</script>";
        exit;
    }

    // Ambil nama mekanik dari tabel berdasarkan id
    $nama_mekanik_list = [];
    $id_str = implode(",", array_map('intval', $mekanik_ids));
    $query = mysqli_query($conn, "SELECT nama FROM mekanik WHERE id IN ($id_str)");

    while ($row = mysqli_fetch_assoc($query)) {
        $nama_mekanik_list[] = $row['nama'];
    }

    // Gabungkan nama jadi string
    $nama_mekanik = implode(", ", $nama_mekanik_list);
    $mekanik_id_str = implode(",", $mekanik_ids);

    // ✅ Simpan ke laporan
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
<style>
    /* === STYLISH CHECKBOX MEKANIK === */
#mekanikList {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 10px;
}

#mekanikList label {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 10px 14px;
    cursor: pointer;
    transition: 0.25s ease;
    font-weight: 500;
    color: #333;
}

#mekanikList label:hover {
    background: #e9f8ff;
    border-color: #39c6ed;
}

#mekanikList input[type="checkbox"] {
    appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid #ccc;
    border-radius: 6px;
    margin-right: 12px;
    position: relative;
    transition: 0.2s;
}

#mekanikList input[type="checkbox"]:checked {
    background-color: #39c6ed;
    border-color: #39c6ed;
}

#mekanikList input[type="checkbox"]:checked::after {
    content: "✔";
    color: white;
    font-size: 13px;
    position: absolute;
    top: 1px;
    left: 4px;
}

#mekanikList input[type="checkbox"]:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

</style>
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
                <button onclick="closeDeletePopup()"
                    style="background-color:#bdc3c7; color:black; padding:8px 14px; border:none; border-radius:6px; cursor:pointer;">Batal</button>
                    
                <button onclick="confirmDelete()"
                    style="background-color:#e74c3c; color:white; padding:8px 14px; border:none; border-radius:6px; cursor:pointer; margin-left:10px;">Ya,
                    Hapus</button>
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
                <select name="mesin_id" required>
                    <option value="">-- Pilih Mesin --</option>
                    <?php
                    $mesin_res = mysqli_query($conn, "SELECT id, nama_mesin FROM mesin ORDER BY nama_mesin ASC");
                    while ($m = mysqli_fetch_assoc($mesin_res)) {
                        echo "<option value='{$m['id']}'>{$m['nama_mesin']}</option>";
                    }
                    ?>
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
    <div id="popupDetail" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closeDetailPopup()">&times;</span>
            <h2>Detail Laporan</h2>

            <input type="hidden" id="detail_id" name="id">

            <label>Nama Mesin:</label>
            <input type="text" id="detail_mesin" readonly>

            <label>Keterangan:</label>
            <textarea id="detail_pesan" readonly></textarea>

            <label>Pesan Mekanik:</label>
            <textarea id="detail_pesan_mekanik" readonly></textarea>

            <label>Pilih Mekanik:</label>
<div id="mekanikList">
    <?php
    include 'koneksi.php';
    $sql = "SELECT id, nama FROM mekanik ORDER BY nama ASC";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo '
            <label>
                <input type="checkbox" name="mekanik_id[]" value="' . $row['id'] . '">
                <span>' . htmlspecialchars($row['nama']) . '</span>
            </label>';
        }
    } else {
        echo "<p>Tidak ada data mekanik</p>";
    }
    ?>
</div>

        </div>

        <button id="ambilBtn" class="btn-ambil">Ambil</button>
        <form id="ambilForm" method="POST" style="display:none;">
            <input type="hidden" name="ambil_laporan" value="1">
            <input type="hidden" id="ambil_id_laporan" name="id_laporan">
            <div id="ambil_mekanik_inputs"></div>
        </form>
    </div>
    </div>


    <script>
        function openPopup() {
            document.getElementById('popupForm').style.display = 'flex';
        }

        function closePopup(popupId = null) {
            // Jika ada id popup yang dikirim, tutup popup tersebut
            if (popupId) {
                document.getElementById(popupId).style.display = 'none';
            } else {
                // Kalau tidak ada id, default tutup popup form
                document.getElementById('popupForm').style.display = 'none';
            }
        }

        // fungsi ini udah gak dibutuhkan lagi kalau udah pakai closePopup universal
        // tapi kalau mau tetap aman, bisa biarkan seperti ini:

        function openDetailPopup(id, mesin, pesan, status, mekanik, pesan_mekanik) {
            const popup = document.getElementById('popupDetail');
            popup.style.display = 'flex';

            // isi data ke input popup
            document.getElementById('detail_id').value = id;
            document.getElementById('detail_mesin').value = mesin;
            document.getElementById('detail_pesan').value = pesan;

            // kalau pesan mekanik belum ada, kosongin aja
            document.getElementById('detail_pesan_mekanik').value = pesan_mekanik ? pesan_mekanik : '';

            const checkboxes = document.querySelectorAll('#mekanikList input[type=checkbox]');
            const ambilBtn = document.getElementById('ambilBtn');

            // reset semua checkbox
            checkboxes.forEach(cb => {
                cb.checked = false;
                cb.disabled = false;
            });

            // tandai mekanik yg udah ada
            if (mekanik) {
                mekanik.split(",").forEach(nama => {
                    checkboxes.forEach(cb => {
                        if (cb.parentNode.textContent.trim() === nama.trim()) cb.checked = true;
                    });
                });
            }

            // aturan sesuai status
            if (status === 'ditunda') {
                checkboxes.forEach(cb => cb.disabled = true); // disable semua
                ambilBtn.style.display = 'block';
                ambilBtn.textContent = 'Lanjutkan Perbaikan';
                ambilBtn.style.backgroundColor = '#27ae60';
            } else if (status === 'kerusakan') {
                checkboxes.forEach(cb => cb.disabled = false);
                ambilBtn.style.display = 'block';
                ambilBtn.textContent = 'Ambil';
                ambilBtn.style.backgroundColor = '#39c6ed';
            } else if (status === 'proses' || status === 'finish') {
                checkboxes.forEach(cb => cb.disabled = true);
                ambilBtn.style.display = 'none';
            }
        }

        function closeDetailPopup() {
            document.getElementById('popupDetail').style.display = 'none';
        }

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
            const existingAlert = document.getElementById("tempAlert");
            if (existingAlert) existingAlert.remove();

            const alertBox = document.createElement("div");
            alertBox.id = "tempAlert";
            alertBox.textContent = message;
            alertBox.className = "temp-alert " + type;
            document.body.appendChild(alertBox);

            setTimeout(() => {
                alertBox.style.opacity = "0";
                setTimeout(() => alertBox.remove(), 500);
            }, 2500);
        }
        document.getElementById("ambilBtn").addEventListener("click", function () {
            const idLaporan = document.getElementById("detail_id").value;
            const checkboxes = document.querySelectorAll('#mekanikList input[type=checkbox]:checked');
            const form = document.getElementById("ambilForm");
            const mekanikContainer = document.getElementById("ambil_mekanik_inputs");

            // kosongkan isian lama
            mekanikContainer.innerHTML = "";
            document.getElementById("ambil_id_laporan").value = idLaporan;

            // tambahkan mekanik yang dipilih
            checkboxes.forEach(cb => {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "nama_mekanik[]";
                input.value = cb.value;
                mekanikContainer.appendChild(input);
            });

            form.submit();
        });


    </script>
</body>

</html>