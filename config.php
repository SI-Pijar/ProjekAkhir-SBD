<?php
$host = 'localhost';
$user = 'root';      // default Laragon
$pass = '';          // default Laragon (kosong)
$db   = 'db_smps';   // nama database tadi

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
