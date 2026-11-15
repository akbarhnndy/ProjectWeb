<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION["username"])) { header("Location: login.php"); exit(); }

if (isset($_POST["tambah"])) {
    try {
        $stmt = $koneksi->prepare("INSERT INTO laporan (mesin_id, tanggal, deskripsi) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $_POST["mesin_id"], $_POST["tanggal"], $_POST["deskripsi"]);
        $stmt->execute();

        $stmt = $koneksi->prepare("UPDATE mesin SET status = 'Rusak' WHERE id = ?");
        $stmt->bind_param("i", $_POST["mesin_id"]);
        $stmt->execute();
        $_SESSION["pesan"] = "Laporan kerusakan dibuat.";
    } catch (mysqli_sql_exception $e) {
        $_SESSION["pesan"] = "Gagal membuat laporan.";
    }
    header("Location: laporan.php");
    exit();
}

if (isset($_POST["status"])) {
    $id = (int)$_POST["id"];
    if ($_POST["status"] == "Diperbaiki") {
        $stmt = $koneksi->prepare("UPDATE laporan SET status = 'Diperbaiki' WHERE id = ? AND status = 'Menunggu'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($_POST["status"] == "Selesai") {
        $stmt = $koneksi->prepare("UPDATE laporan SET status = 'Selesai', tanggal_selesai = CURDATE() WHERE id = ? AND status = 'Diperbaiki'");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $koneksi->prepare("UPDATE mesin SET status = 'Normal'
            WHERE id = (SELECT mesin_id FROM laporan WHERE id = ?)
            AND NOT EXISTS (SELECT 1 FROM laporan WHERE laporan.mesin_id = mesin.id AND laporan.status <> 'Selesai')");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    $_SESSION["pesan"] = "Status laporan diperbarui.";
    header("Location: laporan.php");
    exit();
}

$mesin = $koneksi->query("SELECT id, kode, nama FROM mesin ORDER BY kode");
$daftar = $koneksi->query("SELECT laporan.*, mesin.kode, mesin.nama, mesin.lokasi
    FROM laporan INNER JOIN mesin ON laporan.mesin_id = mesin.id
    ORDER BY laporan.tanggal DESC, laporan.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan Kerusakan</title>
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
    <h1>Laporan Kerusakan</h1>
    <?php if (isset($_SESSION["pesan"])) { echo "<p class='pesan'>" . htmlspecialchars($_SESSION["pesan"]) . "</p>"; unset($_SESSION["pesan"]); } ?>

    <div class="card">
      <h3>Laporkan kerusakan</h3>
      <form method="post">
        <div class="form-grid">
          <div><label>Mesin yang rusak</label>
            <select name="mesin_id" required>
              <option value="">Pilih mesin</option>
              <?php while ($m = $mesin->fetch_assoc()) { ?>
                <option value="<?php echo $m["id"]; ?>"><?php echo htmlspecialchars($m["kode"] . " - " . $m["nama"]); ?></option>
              <?php } ?>
            </select></div>
          <div><label>Tanggal laporan</label>
            <input type="date" name="tanggal" value="<?php echo date("Y-m-d"); ?>" required></div>
        </div>
        <label>Deskripsi kerusakan</label>
        <textarea name="deskripsi" rows="3" required></textarea>
        <br><br>
        <button type="submit" name="tambah">Kirim laporan</button>
      </form>
    </div>

    <div class="card">
      <table>
        <tr><th>Tanggal</th><th>Mesin</th><th>Lokasi</th><th>Kerusakan</th><th>Status</th><th>Selesai</th><th>Aksi</th></tr>
        <?php while ($r = $daftar->fetch_assoc()) { ?>
          <tr>
            <td><?php echo $r["tanggal"]; ?></td>
            <td><?php echo htmlspecialchars($r["kode"] . " - " . $r["nama"]); ?></td>
            <td><?php echo htmlspecialchars($r["lokasi"]); ?></td>
            <td><?php echo htmlspecialchars($r["deskripsi"]); ?></td>
            <td><span class="badge <?php echo $r["status"]; ?>"><?php echo $r["status"]; ?></span></td>
            <td><?php echo $r["tanggal_selesai"] ? $r["tanggal_selesai"] : "-"; ?></td>
            <td>
              <?php if ($r["status"] != "Selesai") {
                  $berikut = ($r["status"] == "Menunggu") ? "Diperbaiki" : "Selesai";
                  $label = ($r["status"] == "Menunggu") ? "Mulai perbaikan" : "Tandai selesai"; ?>
                <form method="post">
                  <input type="hidden" name="id" value="<?php echo $r["id"]; ?>">
                  <button type="submit" name="status" value="<?php echo $berikut; ?>"><?php echo $label; ?></button>
                </form>
              <?php } ?>
            </td>
          </tr>
        <?php } ?>
        <?php if ($daftar->num_rows == 0) { echo "<tr><td colspan='7'>Belum ada laporan.</td></tr>"; } ?>
      </table>
    </div>
  </div>
</div>
</body>
</html>
