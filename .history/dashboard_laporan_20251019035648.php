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

    <!-- 🌈 DESAIN BARU LANGSUNG DI SINI -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f6f8fa;
            margin: 0;
            color: #333;
        }
        .main-content {
            padding: 40px;
            margin-left: 260px;
        }
        h1 {
            font-size: 28px;
            font-weight: 600;
            color: #222;
            margin-bottom: 5px;
        }
        .breadcrumb {
            color: #777;
            font-size: 14px;
            margin-bottom: 25px;
        }
        .filter {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .filter label {
            font-weight: 600;
        }
        .filter select {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        th {
            background-color: #39c6ed;
            color: white;
            text-align: left;
            padding: 12px;
            font-weight: 500;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        .kategori {
            font-weight: 600;
            border-radius: 8px;
            padding: 6px 10px;
            display: inline-block;
            text-align: center;
        }
        .kategori.biasa { background-color: #e8f8ee; color: #28b463; }
        .kategori.sedang { background-color: #fff7e0; color: #f1c40f; }
        .kategori.urgent { background-color: #fdecea; color: #e74c3c; }

        .hapus-float-btn {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .hapus-float-btn:hover { background-color: #c0392b; transform: translateY(-2px); }

        /* === POPUP === */
        .popup {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .popup-content {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            position: relative;
            animation: fadeIn 0.25s ease;
        }
        .close-btn {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 22px;
            color: #777;
            cursor: pointer;
        }
        .close-btn:hover { color: #000; }
        .popup-content h2 {
            font-size: 20px;
            margin-bottom: 15px;
            font-weight: 600;
            color: #222;
        }
        .popup-content input, .popup-content select, .popup-content textarea {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-top: 5px;
            font-size: 14px;
        }
        .popup-content button {
            background: #39c6ed;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: 0.2s;
        }
        .popup-content button:hover { background: #2b8bc3; }

        /* === MEKANIK === */
        #mekanikList {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 8px;
            margin-top: 10px;
        }
        #mekanikList label {
            background: #f7f7f7;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 14px;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        #mekanikList input[type="checkbox"] {
            accent-color: #39c6ed;
            transform: scale(1.2);
        }
        #mekanikList label:hover {
            background: #e6f9ff;
            border-color: #39c6ed;
        }

        /* === ANIMASI === */
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        /* === NOTIFIKASI === */
        .success {
            background-color: #e8f8ee;
            color: #28b463;
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <!-- === ISI BODY PHP TETAP SAMA === -->
    <?php include "dashboard_laporan_body.php"; ?>
</body>
</html>
