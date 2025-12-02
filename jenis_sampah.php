<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

// hanya ADMIN yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: login.php");
    exit;
}

// ambil data user dari session
$username = $_SESSION['username'] ?? '';
$role     = $_SESSION['role'] ?? '';

// ============ SIMPAN DATA ============
if (isset($_POST['simpan'])) {
    $nama_jenis = $_POST['nama_jenis'];
    $kategori   = $_POST['kategori'];
    $keterangan = $_POST['keterangan'];

    $sql_insert = "INSERT INTO jenis_sampah (nama_jenis, kategori, keterangan)
                   VALUES ('$nama_jenis', '$kategori', '$keterangan')";
    mysqli_query($conn, $sql_insert);

    header("Location: jenis_sampah.php");
    exit;
}

// ============ HAPUS DATA ============
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $sql_delete = "DELETE FROM jenis_sampah WHERE id_jenis = $id";
    mysqli_query($conn, $sql_delete);

    header("Location: jenis_sampah.php");
    exit;
}

// ============ AMBIL DATA UNTUK LIST (WAJIB ADA) ============
$sql_js    = "SELECT * FROM jenis_sampah ORDER BY id_jenis DESC";
$result_js = mysqli_query($conn, $sql_js);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Jenis Sampah - SMPS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="top-bar-title">
            SMPS – Jenis Sampah
        </div>
        <div class="top-bar-right">
            <span><?php echo $username . " (" . $role . ")"; ?></span>
            <a href="index.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>Jenis & Kategori Sampah</h1>
            <p>Mengatur klasifikasi sampah untuk monitoring yang lebih terstruktur dan ramah lingkungan.</p>
        </div>

        <div class="grid-2">
            <div class="card accent">
                <h2>Tambah Jenis Sampah</h2>
                <form method="POST" style="margin-top:10px;">
                    <p>
                        <label>Nama Jenis Sampah</label>
                        <input type="text" name="nama_jenis" required placeholder="Misal: Sampah Rumah Tangga">
                    </p>
                    <p>
                        <label>Kategori</label>
                        <select name="kategori" required>
                            <option value="ORGANIK">ORGANIK</option>
                            <option value="ANORGANIK">ANORGANIK</option>
                            <option value="B3">B3</option>
                        </select>
                    </p>
                    <p>
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" placeholder="Catatan tambahan (opsional)">
                    </p>
                    <button type="submit" name="simpan">Simpan Jenis Sampah</button>
                </form>
            </div>

            <div class="card">
                <h2>Keterangan</h2>
                <p class="text-muted" style="margin-top:6px;">
                    Pisahkan jenis sampah berdasarkan kategori agar pengolahan dan daur ulang lebih efektif:
                </p>
                <ul style="margin-top:8px; padding-left:18px; font-size:13px; color:#455a64;">
                    <li><strong>Organik</strong>: sisa makanan, daun, ranting, dan bahan mudah terurai.</li>
                    <li><strong>Anorganik</strong>: plastik, logam, kaca, kertas non-kompos.</li>
                    <li><strong>B3</strong>: bahan berbahaya dan beracun seperti baterai, oli bekas, obat kadaluarsa.</li>
                </ul>
            </div>
        </div>

        <div class="card" style="margin-top:12px;">
            <h2>Daftar Jenis Sampah</h2>
            <div class="table-wrapper" style="margin-top:8px;">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nama Jenis</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                    <?php
                    if ($result_js && mysqli_num_rows($result_js) > 0) {
                        while ($row = mysqli_fetch_assoc($result_js)) {
                            echo "<tr>";
                            echo "<td>".$row['id_jenis']."</td>";
                            echo "<td>".$row['nama_jenis']."</td>";
                            echo "<td>".$row['kategori']."</td>";
                            echo "<td>".$row['keterangan']."</td>";
                            echo "<td>
                                    <a href='jenis_sampah.php?hapus=".$row['id_jenis']."'
                                       class='btn btn-danger'
                                       onclick=\"return confirm('Hapus jenis sampah ini?')\">
                                       Hapus
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Belum ada data jenis sampah.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>