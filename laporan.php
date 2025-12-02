<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

// ADMIN dan PETUGAS harus login dulu
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// amankan data session
$username = $_SESSION['username'] ?? '';
$role     = $_SESSION['role'] ?? '';

// =========================
// 1. PROSES SIMPAN LAPORAN
// =========================
if (isset($_POST['simpan'])) {
    $id_tps     = $_POST['id_tps'];
    $id_petugas = $_POST['id_petugas'];
    $id_jenis   = $_POST['id_jenis'];
    $tanggal    = $_POST['tanggal'];
    $jam        = $_POST['jam'];
    $volume_kg  = $_POST['volume_kg'];
    $keterangan = $_POST['keterangan'];

    $sql_insert = "INSERT INTO laporan_harian
        (id_tps, id_petugas, id_jenis, tanggal, jam, volume_kg, keterangan)
        VALUES
        ('$id_tps', '$id_petugas', '$id_jenis', '$tanggal', '$jam', '$volume_kg', '$keterangan')";

    mysqli_query($conn, $sql_insert);

    // setelah simpan, reload halaman
    header("Location: laporan.php");
    exit;
}

// =========================
// 2. AMBIL DATA MASTER UNTUK DROPDOWN INPUT
// =========================
$tps_res     = mysqli_query($conn, "SELECT * FROM tps WHERE status = 'AKTIF' ORDER BY nama_tps");
$petugas_res = mysqli_query($conn, "SELECT * FROM petugas ORDER BY nama_petugas");
$jenis_res   = mysqli_query($conn, "SELECT * FROM jenis_sampah ORDER BY nama_jenis");

// =========================
// 3. FILTER DATA LAPORAN
// =========================
$filter_tps = isset($_GET['id_tps']) ? $_GET['id_tps'] : '';
$filter_tgl = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

$sql_laporan = "
    SELECT 
        lh.id_laporan,
        lh.tanggal,
        lh.jam,
        tps.nama_tps,
        petugas.nama_petugas,
        jenis_sampah.nama_jenis,
        lh.volume_kg,
        lh.keterangan
    FROM laporan_harian lh
    JOIN tps ON lh.id_tps = tps.id_tps
    JOIN petugas ON lh.id_petugas = petugas.id_petugas
    JOIN jenis_sampah ON lh.id_jenis = jenis_sampah.id_jenis
    WHERE 1=1
";

if ($filter_tps != '') {
    $sql_laporan .= " AND lh.id_tps = " . intval($filter_tps);
}

if ($filter_tgl != '') {
    $tgl_safe = mysqli_real_escape_string($conn, $filter_tgl);
    $sql_laporan .= " AND lh.tanggal = '$tgl_safe'";
}

$sql_laporan .= " ORDER BY lh.tanggal DESC, lh.jam DESC";

$laporan_res    = mysqli_query($conn, $sql_laporan);
// query terpisah untuk dropdown filter TPS
$tps_filter_res = mysqli_query($conn, "SELECT * FROM tps ORDER BY nama_tps");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian - SMPS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="top-bar-title">
            SMPS – Laporan Harian
        </div>
        <div class="top-bar-right">
            <span><?php echo $username . " (" . $role . ")"; ?></span>
            <a href="index.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>Laporan Harian Pengelolaan Sampah</h1>
            <p>Mencatat dan memantau aktivitas pengangkutan sampah setiap hari secara ramah lingkungan.</p>
        </div>

        <!-- GRID: FORM INPUT + FILTER -->
        <div class="grid-2-reverse">
            <!-- FORM INPUT -->
            <div class="card accent">
                <h2>Input Laporan Baru</h2>
                <form method="POST" style="margin-top:10px;">
                    <p>
                        <label>TPS</label>
                        <select name="id_tps" required>
                            <option value="">-- Pilih TPS --</option>
                            <?php while ($t = mysqli_fetch_assoc($tps_res)) { ?>
                                <option value="<?php echo $t['id_tps']; ?>">
                                    <?php echo $t['nama_tps']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </p>

                    <p>
                        <label>Petugas</label>
                        <select name="id_petugas" required>
                            <option value="">-- Pilih Petugas --</option>
                            <?php while ($p = mysqli_fetch_assoc($petugas_res)) { ?>
                                <option value="<?php echo $p['id_petugas']; ?>">
                                    <?php echo $p['nama_petugas']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </p>

                    <p>
                        <label>Jenis Sampah</label>
                        <select name="id_jenis" required>
                            <option value="">-- Pilih Jenis Sampah --</option>
                            <?php while ($j = mysqli_fetch_assoc($jenis_res)) { ?>
                                <option value="<?php echo $j['id_jenis']; ?>">
                                    <?php echo $j['nama_jenis']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </p>

                    <p>
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" required>
                    </p>

                    <p>
                        <label>Jam</label>
                        <input type="time" name="jam" required>
                    </p>

                    <p>
                        <label>Volume (kg)</label>
                        <input type="number" step="0.01" name="volume_kg" required placeholder="Contoh: 120.5">
                    </p>

                    <p>
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" placeholder="Opsional, misal: kondisi TPS, cuaca, dll.">
                    </p>

                    <button type="submit" name="simpan">Simpan Laporan</button>
                </form>
            </div>

            <!-- FILTER -->
            <div class="card">
                <h2>Filter Laporan</h2>
                <form method="GET" style="margin-top:10px;">
                    <p>
                        <label>TPS</label>
                        <select name="id_tps">
                            <option value="">-- Semua TPS --</option>
                            <?php
                            if ($tps_filter_res) {
                                while ($tf = mysqli_fetch_assoc($tps_filter_res)) {
                                    $selected = ($filter_tps == $tf['id_tps']) ? 'selected' : '';
                                    echo "<option value='".$tf['id_tps']."' $selected>".$tf['nama_tps']."</option>";
                                }
                            }
                            ?>
                        </select>
                    </p>
                    <p>
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?php echo $filter_tgl; ?>">
                    </p>
                    <button type="submit">Terapkan Filter</button>
                    <a href="laporan.php" class="btn btn-secondary">Reset</a>
                </form>
                <p class="text-muted" style="margin-top:8px;">
                    Gunakan filter untuk fokus pada TPS tertentu atau satu tanggal tertentu.
                </p>
            </div>
        </div>

        <!-- TABEL LAPORAN -->
        <div class="card" style="margin-top:14px;">
            <h2>Daftar Laporan</h2>
            <div class="table-wrapper" style="margin-top:8px;">
                <table>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>TPS</th>
                        <th>Petugas</th>
                        <th>Jenis Sampah</th>
                        <th>Volume (kg)</th>
                        <th>Keterangan</th>
                    </tr>
                    <?php
                    if ($laporan_res && mysqli_num_rows($laporan_res) > 0) {
                        while ($row = mysqli_fetch_assoc($laporan_res)) {
                            echo "<tr>";
                            echo "<td>".$row['tanggal']."</td>";
                            echo "<td>".$row['jam']."</td>";
                            echo "<td>".$row['nama_tps']."</td>";
                            echo "<td>".$row['nama_petugas']."</td>";
                            echo "<td>".$row['nama_jenis']."</td>";
                            echo "<td>".$row['volume_kg']."</td>";
                            echo "<td>".$row['keterangan']."</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>Belum ada data laporan.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>