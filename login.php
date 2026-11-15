<?php
session_start();
include "koneksi.php";

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $koneksi->prepare("SELECT password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hash);
    $stmt->fetch();

    if ($hash && $password == $hash) {
        session_regenerate_id(true);
        $_SESSION["username"] = $username;
        header("Location: index.php");
        exit();
    }
    $error = "Username atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Admin</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="card login">
    <h3>Login Admin LaundryCare</h3>
    <?php if ($error != "") { echo "<p class='salah'>$error</p>"; } ?>
    <form method="post">
      <label>Username</label>
      <input type="text" name="username" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <br><br>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
