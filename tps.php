<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

// hanya ADMIN yang boleh akses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: login.php");
    exit;
}

// ambil data user dari session
$username = $_SESSION['username'] ?? '';
$role     = $_SESSION['role'] ?? '';

// ----------------------
// PROSES SIMPAN DATA TPS
// ----------------------
if (isset($_POST['simpan'])) {
    $nama_tps       = $_POST['nama_tps'];
    $lokasi         = $_POST['lokasi'];
    $kapasitas_maks = $_POST['kapasitas_maks'];
    $status         = $_POST['status'];

    $sql_insert = "INSERT INTO tps (nama_tps, lokasi, kapasitas_maks, status)
                   VALUES ('$nama_tps', '$lokasi', '$kapasitas_maks', '$status')";

    mysqli_query($conn, $sql_insert);

    // setelah simpan, reload halaman
    header("Location: tps.php");
    exit;
}

// ----------------------
// PROSES HAPUS DATA TPS
// ----------------------
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $sql_delete = "DELETE FROM tps WHERE id_tps = $id";
    mysqli_query($conn, $sql_delete);

    header("Location: tps.php");
    exit;
}

// ----------------------
// AMBIL DATA TPS UNTUK DITAMPILKAN
// ----------------------
$sql_tps    = "SELECT * FROM tps ORDER BY id_tps DESC";
$result_tps = mysqli_query($conn, $sql_tps);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data TPS - SMPS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="top-bar-title">
            SMPS – Data TPS
        </div>
        <div class="top-bar-right">
            <span><?php echo $username . " (" . $role . ")"; ?></span>
            <a href="index.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>Pengelolaan Titik TPS</h1>
            <p>Atur lokasi dan kapasitas TPS sebagai dasar monitoring pengangkutan sampah.</p>
        </div>

        <div class="grid-2">
            <!-- FORM TAMBAH TPS -->
            <div class="card accent">
                <h2>Tambah TPS Baru</h2>
                <p class="text-muted" style="margin-bottom:10px;">
                    Isi data TPS secara lengkap agar perencanaan pengangkutan lebih akurat.
                </p>
                <form method="POST">
                    <p>
                        <label>Nama TPS</label>
                        <input type="text" name="nama_tps" required placeholder="Misal: TPS Pasar Utama">
                    </p>
                    <p>
                        <label>Lokasi</label>
                        <input type="text" name="lokasi" required placeholder="Alamat atau patokan lokasi">
                    </p>
                    <p>
                        <label>Kapasitas Maks (kg)</label>
                        <input type="number" step="0.01" name="kapasitas_maks" required placeholder="Misal: 5000">
                    </p>
                    <p>
                        <label>Status</label>
                        <select name="status">
                            <option value="AKTIF">AKTIF</option>
                            <option value="NONAKTIF">NONAKTIF</option>
                        </select>
                    </p>
                    <button type="submit" name="simpan">Simpan TPS</button>
                </form>
            </div>

            <!-- INFORMASI -->
            <div class="card">
                <h2>Informasi</h2>
                <p class="text-muted" style="margin-top:6px;">
                    Status <strong>AKTIF</strong> menandakan TPS digunakan dalam perhitungan laporan harian.  
                    Status <strong>NONAKTIF</strong> cocok untuk TPS yang sementara ditutup atau dipindahkan.
                </p>
            </div>
        </div>

        <!-- TABEL TPS -->
        <div class="card" style="margin-top:12px;">
            <h2>Daftar TPS</h2>
            <div class="table-wrapper" style="margin-top:8px;">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nama TPS</th>
                        <th>Lokasi</th>
                        <th>Kapasitas (kg)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                    <?php
                    if ($result_tps && mysqli_num_rows($result_tps) > 0) {
                        while ($row = mysqli_fetch_assoc($result_tps)) {
                            $badgeClass = $row['status'] == 'AKTIF' ? 'badge-aktif' : 'badge-nonaktif';
                            echo "<tr>";
                            echo "<td>" . $row['id_tps'] . "</td>";
                            echo "<td>" . $row['nama_tps'] . "</td>";
                            echo "<td>" . $row['lokasi'] . "</td>";
                            echo "<td>" . $row['kapasitas_maks'] . "</td>";
                            echo "<td><span class='badge ".$badgeClass."'>" . $row['status'] . "</span></td>";
                            echo "<td>
                                    <a href='tps.php?hapus=" . $row['id_tps'] . "'
                                       class='btn btn-danger'
                                       onclick=\"return confirm('Hapus TPS ini?')\">
                                       Hapus
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>Belum ada data TPS.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>