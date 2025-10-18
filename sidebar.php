<?php

$current_page = basename($_SERVER['PHP_SELF']);
?>

<head>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<div class="sidebar">
    <ul class="menu">
        <li>
            <a href="dashboard_laporan.php" class="<?= $current_page == 'dashboard_laporan.php' ? 'active' : '' ?>">
                List Kerusakan
            </a>
        </li>
        <li>
            <a href="list_proses.php" class="<?= $current_page == 'list_proses.php' ? 'active' : '' ?>">
                List Proses
            </a>
        </li>
        <li>
            <a href="list_finish.php" class="<?= $current_page == 'list_finish.php' ? 'active' : '' ?>">
                List Finish
            </a>
        </li>
        <li>
            <a href="#" class="disabled">Profil</a>
        </li>
        <li>
            <a href="mekanik.php" class="<?= $current_page == 'mekanik.php' ? 'active' : '' ?>">
                Mekanik
            </a>
        </li>
        <li>
            <a href="mesin.php" class="<?= $current_page == 'mesin.php' ? 'active' : '' ?>">
                Mesin
            </a>
        </li>
    </ul>
    <?php if ($current_page == 'dashboard_laporan.php'): ?>
        <button class="register-btn" onclick="openPopup()">Registrasi Laporan</button>
    <?php endif; ?>
</div>