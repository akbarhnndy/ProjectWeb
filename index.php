<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION["username"])) { header("Location: login.php"); exit(); }

$total = $koneksi->query("SELECT COUNT(*) FROM mesin")->fetch_row()[0];
$rusak = $koneksi->query("SELECT COUNT(*) FROM mesin WHERE status = 'Rusak'")->fetch_row()[0];
$belum = $koneksi->query("SELECT COUNT(*) FROM laporan WHERE status <> 'Selesai'")->fetch_row()[0];

$hasil = $koneksi->query("SELECT laporan.tanggal, laporan.deskripsi, laporan.status, mesin.kode, mesin.nama
    FROM laporan INNER JOIN mesin ON laporan.mesin_id = mesin.id
    WHERE laporan.status <> 'Selesai' ORDER BY laporan.tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
  <div class="menu">
    <h2>LaundryCare</h2>
    <a href="index.php">Dashboard</a>
    <a href="mesin.php">Kelola Mesin</a>
    <a href="laporan.php">Laporan Kerusakan</a>
    <a href="logout.php">Keluar</a>
  </div>
  <div class="isi">
    <h1>Dashboard</h1>
    <div class="stat">
      <div><b><?php echo $total; ?></b>Total mesin</div>
      <div class="merah"><b><?php echo $rusak; ?></b>Mesin rusak</div>
      <div class="kuning"><b><?php echo $belum; ?></b>Laporan belum selesai</div>
    </div>
    <div class="card">
      <h3>Perbaikan yang sedang berjalan</h3>
      <table>
        <tr><th>Tanggal</th><th>Mesin</th><th>Kerusakan</th><th>Status</th></tr>
        <?php while ($r = $hasil->fetch_assoc()) { ?>
          <tr>
            <td><?php echo $r["tanggal"]; ?></td>
            <td><?php echo htmlspecialchars($r["kode"] . " - " . $r["nama"]); ?></td>
            <td><?php echo htmlspecialchars($r["deskripsi"]); ?></td>
            <td><span class="badge <?php echo $r["status"]; ?>"><?php echo $r["status"]; ?></span></td>
          </tr>
        <?php } ?>
        <?php if ($hasil->num_rows == 0) { echo "<tr><td colspan='4'>Semua mesin normal.</td></tr>"; } ?>
      </table>
    </div>
  </div>
</div>
</body>
</html>
