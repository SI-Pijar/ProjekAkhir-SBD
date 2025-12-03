<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

// hanya ADMIN dan PETUGAS yang boleh akses
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'] ?? '';
$role     = $_SESSION['role'] ?? '';

// 1. PROSES SIMPAN LAPORAN
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

    header("Location: laporan.php");
    exit;
}

// 2. DATA MASTER UNTUK FORM INPUT
$tps_res     = mysqli_query($conn, "SELECT * FROM tps WHERE status = 'AKTIF' ORDER BY nama_tps");
$petugas_res = mysqli_query($conn, "SELECT * FROM petugas ORDER BY nama_petugas");
$jenis_res   = mysqli_query($conn, "SELECT * FROM jenis_sampah ORDER BY nama_jenis");

// 3. FILTER + PAGINATION
$filter_tps = isset($_GET['id_tps'])   ? $_GET['id_tps']   : '';
$filter_tgl = isset($_GET['tanggal'])  ? $_GET['tanggal']  : '';
$per_page   = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$page       = isset($_GET['page'])     ? (int)$_GET['page']     : 1;

$allowed_per_page = [10, 50, 100];
if (!in_array($per_page, $allowed_per_page)) {
    $per_page = 10;
}
if ($page < 1) {
    $page = 1;
}

//query dasar
$sql_base = "
    FROM laporan_harian lh
    JOIN tps         ON lh.id_tps = tps.id_tps
    JOIN petugas     ON lh.id_petugas = petugas.id_petugas
    JOIN jenis_sampah ON lh.id_jenis = jenis_sampah.id_jenis
    WHERE 1=1
";

if ($filter_tps != '') {
    $sql_base .= " AND lh.id_tps = " . intval($filter_tps);
}

if ($filter_tgl != '') {
    $tgl_safe = mysqli_real_escape_string($conn, $filter_tgl);
    $sql_base .= " AND lh.tanggal = '$tgl_safe'";
}

//Hitung total baris untuk pagination
$sql_count   = "SELECT COUNT(*) AS total " . $sql_base;
$count_res   = mysqli_query($conn, $sql_count);
$total_rows  = 0;
$total_pages = 1;

if ($count_res) {
    $row_count  = mysqli_fetch_assoc($count_res);
    $total_rows = (int)$row_count['total'];
    $total_pages = max(1, ceil($total_rows / $per_page));
}

if ($page > $total_pages) {
    $page = $total_pages;
}

$offset = ($page - 1) * $per_page;

//Query ambil data dengan LIMIT
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
    " . $sql_base . "
    ORDER BY lh.tanggal DESC, lh.jam DESC
    LIMIT $per_page OFFSET $offset
";

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
                        <input type="date" name="tanggal" value="<?php echo htmlspecialchars($filter_tgl); ?>">
                    </p>
                    <p>
                        <label>Jumlah data per halaman</label>
                        <select name="per_page">
                            <option value="10"  <?php echo ($per_page == 10  ? 'selected' : ''); ?>>10</option>
                            <option value="50"  <?php echo ($per_page == 50  ? 'selected' : ''); ?>>50</option>
                            <option value="100" <?php echo ($per_page == 100 ? 'selected' : ''); ?>>100</option>
                        </select>
                    </p>
                    <button type="submit">Terapkan Filter</button>
                    <a href="laporan.php" class="btn btn-secondary">Reset</a>
                </form>
                <p class="text-muted" style="margin-top:8px;">
                    Gunakan filter untuk fokus pada TPS tertentu, tanggal tertentu, dan atur berapa banyak data yang ditampilkan per halaman.
                </p>
            </div>
        </div>

        <!-- TABEL LAPORAN -->
        <div class="card" style="margin-top:14px;">
            <h2>Daftar Laporan</h2>
            <p class="text-muted" style="margin-top:4px;">
                Total data: <?php echo $total_rows; ?> entri.
            </p>
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

            <?php
            // 4. PAGINATION (MAKS 5 NOMOR)
            if ($total_pages > 1) {

                $qs = [];
                if ($filter_tps != '') {
                    $qs[] = "id_tps=" . urlencode($filter_tps);
                }
                if ($filter_tgl != '') {
                    $qs[] = "tanggal=" . urlencode($filter_tgl);
                }
                $qs[] = "per_page=" . $per_page;
                $base_qs = implode('&', $qs);

                $start = max(1, $page - 2);
                $end   = min($total_pages, $page + 2);

                if ($end - $start < 4) {
                    if ($start == 1) {
                        $end = min(5, $total_pages);
                    } elseif ($end == $total_pages) {
                        $start = max(1, $total_pages - 4);
                    }
                }
                ?>
                <div class="pagination">
                    <span>Halaman:</span>

                    <?php if ($page > 1) { ?>
                        <a class="page-link" href="laporan.php?<?php echo $base_qs; ?>&page=<?php echo $page - 1; ?>">&laquo;</a>
                    <?php } ?>

                    <?php for ($i = $start; $i <= $end; $i++) { ?>
                        <a class="page-link <?php echo ($i == $page ? 'active' : ''); ?>"
                           href="laporan.php?<?php echo $base_qs; ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php } ?>

                    <?php if ($page < $total_pages) { ?>
                        <a class="page-link" href="laporan.php?<?php echo $base_qs; ?>&page=<?php echo $page + 1; ?>">&raquo;</a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
