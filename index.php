<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

// nilai default → mencegah undefined variable
$total_semua = 0;

// kalau belum login, arahkan ke login.php
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];

// AMBIL FILTER TANGGAL
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$tanggal_safe = null;

// QUERY REKAP VOLUME PER TPS
$sql = "
    SELECT 
        tps.nama_tps,
        tps.lokasi,
        SUM(laporan_harian.volume_kg) AS total_volume
    FROM laporan_harian
    JOIN tps ON laporan_harian.id_tps = tps.id_tps
    WHERE 1=1
";

if ($tanggal != '') {
    $tanggal_safe = mysqli_real_escape_string($conn, $tanggal);
    $sql .= " AND laporan_harian.tanggal = '$tanggal_safe'";
}

$sql .= " GROUP BY tps.id_tps ORDER BY total_volume DESC";

$result = mysqli_query($conn, $sql);

// TOTAL SEMUA VOLUME
$sql_total = "SELECT SUM(volume_kg) AS total_semua FROM laporan_harian";
if ($tanggal_safe) {
    $sql_total .= " WHERE tanggal = '$tanggal_safe'";
}
$res_total = mysqli_query($conn, $sql_total);

if ($res_total) {
    $data_total = mysqli_fetch_assoc($res_total);
    $total_semua = $data_total['total_semua'] ?? 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard SMPS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="top-bar-title">
            SMPS – Dashboard Pengelolaan Sampah
        </div>
        <div class="top-bar-right">
            <span><?php echo $username . " (" . $role . ")"; ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <!-- CONTAINER -->
    <div class="container">

        <!-- HEADER -->
        <div class="page-header">
            <h1>Ringkasan Aktivitas Sampah</h1>
            <p>Memonitor volume sampah di seluruh TPS untuk mendukung lingkungan yang lebih bersih dan sehat.</p>
        </div>

        <!-- BAGIAN KIRI & KANAN -->
        <div class="grid-2">

            <!-- FILTER + RINGKASAN -->
            <div class="card accent">
                <h2>Filter Laporan</h2>
                <p class="text-muted" style="margin-bottom:10px;">
                    Filter berdasarkan tanggal untuk melihat data per hari.
                </p>

                <form method="GET">
                    <p>
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?php echo $tanggal; ?>">
                    </p>

                    <button type="submit" class="btn-primary">Terapkan</button>
                    <a href="index.php" class="btn btn-secondary">Reset</a>
                </form>

                <div style="margin-top:15px;">
                    <div class="summary-box">
                        <div>
                            <span class="text-muted">
                                Total volume <?php echo ($tanggal != '' ? "pada <strong>$tanggal</strong>" : "semua waktu"); ?>:
                            </span>
                        </div>
                        <strong><?php echo $total_semua; ?> kg</strong>
                    </div>
                </div>
            </div>

            <!-- INFORMASI -->
            <div class="card">
                <h2>Tips Pengelolaan Sampah</h2>
                <p class="text-muted" style="margin-top:8px;">
                    Data rekap membantu dalam:
                </p>
                <ul style="margin-top:10px; padding-left:20px; font-size:14px; color:#455a64;">
                    <li>Mengatur prioritas pengangkutan.</li>
                    <li>Menganalisis TPS dengan volume tertinggi.</li>
                    <li>Menilai tren harian untuk program pengurangan sampah.</li>
                </ul>
            </div>
        </div>

        <!-- TABEL REKAP -->
        <div class="card" style="margin-top:15px;">
            <h2>Rekap Volume Sampah per TPS</h2>
            <p class="text-muted" style="margin-bottom:8px;">
                Menampilkan urutan TPS berdasarkan volume sampah tertinggi.
            </p>

            <div class="table-wrapper" style="margin-top:8px;">
                <table>
                    <tr>
                        <th>Nama TPS</th>
                        <th>Lokasi</th>
                        <th>Total Volume (kg)</th>
                    </tr>

                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['nama_tps'] . "</td>";
                            echo "<td>" . $row['lokasi'] . "</td>";
                            echo "<td>" . $row['total_volume'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>Belum ada data laporan tersedia.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>

        <!-- MENU -->
        <div class="card" style="margin-top:15px;">
            <h2>Menu Utama</h2>
            <p class="text-muted" style="margin-bottom:10px;">
                Kelola data sistem atau masukkan laporan harian.
            </p>

            <p class="menu-links">
                <?php if ($role == 'ADMIN') { ?>
                    <a href="tps.php" class="btn btn-primary">Data TPS</a>
                    <a href="jenis_sampah.php" class="btn btn-primary">Jenis Sampah</a>
                    <a href="petugas.php" class="btn btn-primary">Data Petugas</a>
                <?php } ?>
                <a href="laporan.php" class="btn btn-secondary">Laporan Harian</a>
            </p>
        </div>

    </div><!-- end container -->

</body>
</html>