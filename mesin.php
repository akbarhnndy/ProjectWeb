<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION["username"])) { header("Location: login.php"); exit(); }

if (isset($_POST["hapus"])) {
    $stmt = $koneksi->prepare("DELETE FROM mesin WHERE id = ?");
    $stmt->bind_param("i", $_POST["id"]);
    $stmt->execute();
    $_SESSION["pesan"] = "Mesin dihapus.";
    header("Location: mesin.php");
    exit();
}

if (isset($_POST["simpan"])) {
    $id = (int)$_POST["id"];
    try {
        if ($id > 0) {
            $stmt = $koneksi->prepare("UPDATE mesin SET kode=?, nama=?, jenis=?, merek=?, lokasi=?, status=? WHERE id=?");
            $stmt->bind_param("ssssssi", $_POST["kode"], $_POST["nama"], $_POST["jenis"], $_POST["merek"], $_POST["lokasi"], $_POST["status"], $id);
        } else {
            $stmt = $koneksi->prepare("INSERT INTO mesin (kode, nama, jenis, merek, lokasi, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $_POST["kode"], $_POST["nama"], $_POST["jenis"], $_POST["merek"], $_POST["lokasi"], $_POST["status"]);
        }
        $stmt->execute();
        $_SESSION["pesan"] = "Data mesin tersimpan.";
    } catch (mysqli_sql_exception $e) {
        $_SESSION["pesan"] = "Gagal menyimpan. Kode mesin mungkin sudah dipakai.";
    }
    header("Location: mesin.php");
    exit();
}

$edit = ["id" => 0, "kode" => "", "nama" => "", "jenis" => "", "merek" => "", "lokasi" => "", "status" => "Normal"];
if (isset($_GET["edit"])) {
    $stmt = $koneksi->prepare("SELECT * FROM mesin WHERE id = ?");
    $stmt->bind_param("i", $_GET["edit"]);
    $stmt->execute();
    $baris = $stmt->get_result()->fetch_assoc();
    if ($baris) { $edit = $baris; }
}
$daftar = $koneksi->query("SELECT * FROM mesin ORDER BY kode");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kelola Mesin</title>
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
    <h1>Kelola Mesin</h1>
    <?php if (isset($_SESSION["pesan"])) { echo "<p class='pesan'>" . htmlspecialchars($_SESSION["pesan"]) . "</p>"; unset($_SESSION["pesan"]); } ?>

    <div class="card">
      <h3><?php echo $edit["id"] ? "Edit mesin" : "Tambah mesin"; ?></h3>
      <form method="post">
        <input type="hidden" name="id" value="<?php echo $edit["id"]; ?>">
        <div class="form-grid">
          <div><label>Kode mesin</label>
            <input type="text" name="kode" value="<?php echo htmlspecialchars($edit["kode"]); ?>" required></div>
          <div><label>Nama mesin</label>
            <input type="text" name="nama" value="<?php echo htmlspecialchars($edit["nama"]); ?>" required></div>
          <div><label>Jenis mesin</label>
            <select name="jenis" required>
              <?php foreach (["Mesin Cuci", "Pengering", "Setrika Uap"] as $j) { ?>
                <option <?php if ($edit["jenis"] == $j) echo "selected"; ?>><?php echo $j; ?></option>
              <?php } ?>
            </select></div>
          <div><label>Merek</label>
            <input type="text" name="merek" value="<?php echo htmlspecialchars($edit["merek"]); ?>" required></div>
          <div><label>Lokasi / ruangan</label>
            <input type="text" name="lokasi" value="<?php echo htmlspecialchars($edit["lokasi"]); ?>" required></div>
          <div><label>Status kondisi</label>
            <select name="status">
              <option <?php if ($edit["status"] == "Normal") echo "selected"; ?>>Normal</option>
              <option <?php if ($edit["status"] == "Rusak") echo "selected"; ?>>Rusak</option>
            </select></div>
        </div>
        <br>
        <button type="submit" name="simpan">Simpan</button>
        <?php if ($edit["id"]) { echo "<a href='mesin.php' class='tombol abu'>Batal</a>"; } ?>
      </form>
    </div>

    <div class="card">
      <table>
        <tr><th>Kode</th><th>Nama</th><th>Jenis</th><th>Merek</th><th>Lokasi</th><th>Kondisi</th><th>Aksi</th></tr>
        <?php while ($r = $daftar->fetch_assoc()) { ?>
          <tr>
            <td><?php echo htmlspecialchars($r["kode"]); ?></td>
            <td><?php echo htmlspecialchars($r["nama"]); ?></td>
            <td><?php echo $r["jenis"]; ?></td>
            <td><?php echo htmlspecialchars($r["merek"]); ?></td>
            <td><?php echo htmlspecialchars($r["lokasi"]); ?></td>
            <td><span class="badge <?php echo $r["status"]; ?>"><?php echo $r["status"]; ?></span></td>
            <td>
              <a href="mesin.php?edit=<?php echo $r["id"]; ?>" class="tombol abu">Edit</a>
              <form method="post" style="display:inline" onsubmit="return confirm('Hapus mesin ini beserta laporannya?')">
                <input type="hidden" name="id" value="<?php echo $r["id"]; ?>">
                <button type="submit" name="hapus" class="hapus">Hapus</button>
              </form>
            </td>
          </tr>
        <?php } ?>
        <?php if ($daftar->num_rows == 0) { echo "<tr><td colspan='7'>Belum ada data mesin.</td></tr>"; } ?>
      </table>
    </div>
  </div>
</div>
</body>
</html>
