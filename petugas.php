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

$username = $_SESSION['username'];
$role     = $_SESSION['role'];

// MODE EDIT
$edit_mode = false;
$edit_data = null;

if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];

    $q_edit  = mysqli_query($conn, "SELECT * FROM petugas WHERE id_petugas = $edit_id LIMIT 1");
    if ($q_edit && mysqli_num_rows($q_edit) == 1) {
        $edit_data = mysqli_fetch_assoc($q_edit);
        $edit_mode = true;
    }
}

// CREATE
if (isset($_POST['simpan'])) {
    $nama_petugas = $_POST['nama_petugas'];
    $no_hp        = $_POST['no_hp'];
    $shift        = $_POST['shift'];

    $sql_insert = "INSERT INTO petugas (nama_petugas, no_hp, shift)
                   VALUES ('$nama_petugas', '$no_hp', '$shift')";
    mysqli_query($conn, $sql_insert);

    header("Location: petugas.php");
    exit;
}

// UPDATE
if (isset($_POST['update'])) {
    $id_petugas   = (int) $_POST['id_petugas'];
    $nama_petugas = $_POST['nama_petugas'];
    $no_hp        = $_POST['no_hp'];
    $shift        = $_POST['shift'];

    $sql_update = "UPDATE petugas
                   SET nama_petugas = '$nama_petugas',
                       no_hp        = '$no_hp',
                       shift        = '$shift'
                   WHERE id_petugas = $id_petugas";
    mysqli_query($conn, $sql_update);

    header("Location: petugas.php");
    exit;
}

//DELETE 
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    $sql_delete = "DELETE FROM petugas WHERE id_petugas = $id";
    mysqli_query($conn, $sql_delete);

    header("Location: petugas.php");
    exit;
}

//READ
$sql_petugas     = "SELECT * FROM petugas ORDER BY id_petugas DESC";
$result_petugas  = mysqli_query($conn, $sql_petugas);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Petugas - SMPS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="top-bar">
        <div class="top-bar-title">SMPS – Data Petugas</div>
        <div class="top-bar-right">
            <span><?php echo $username . " (" . $role . ")"; ?></span>
            <a href="index.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>Petugas Lapangan</h1>
            <p>Mengelola data petugas yang bertanggung jawab di lapangan.</p>
        </div>

        <div class="grid-2">
            <div class="card accent">
                <h2><?php echo $edit_mode ? 'Ubah Data Petugas' : 'Tambah Petugas'; ?></h2>

                <form method="POST" style="margin-top:10px;">
                    <?php if ($edit_mode) { ?>
                        <input type="hidden" name="id_petugas" value="<?php echo $edit_data['id_petugas']; ?>">
                    <?php } ?>

                    <p>
                        <label>Nama Petugas</label>
                        <input type="text"
                               name="nama_petugas"
                               required
                               value="<?php echo htmlspecialchars($edit_data['nama_petugas'] ?? ''); ?>">
                    </p>
                    <p>
                        <label>No HP</label>
                        <input type="text"
                               name="no_hp"
                               value="<?php echo htmlspecialchars($edit_data['no_hp'] ?? ''); ?>">
                    </p>
                    <p>
                        <label>Shift</label>
                        <select name="shift" required>
                            <?php
                            $shift_now = $edit_data['shift'] ?? 'PAGI';
                            $options   = ['PAGI', 'SIANG', 'MALAM'];
                            foreach ($options as $opt) {
                                $sel = ($shift_now == $opt) ? 'selected' : '';
                                echo "<option value='$opt' $sel>$opt</option>";
                            }
                            ?>
                        </select>
                    </p>

                    <?php if ($edit_mode) { ?>
                        <button type="submit" name="update">Update Petugas</button>
                        <a href="petugas.php" class="btn btn-secondary">Batal</a>
                    <?php } else { ?>
                        <button type="submit" name="simpan">Simpan</button>
                    <?php } ?>
                </form>
            </div>

            <div class="card">
                <h2>Catatan</h2>
                <p class="text-muted" style="margin-top:6px;">
                    Data petugas digunakan dalam laporan harian untuk menelusuri
                    siapa yang bertugas di TPS tertentu dan shift yang berjalan.
                    Fitur <strong>Update</strong> memudahkan jika terjadi perubahan
                    nomor HP atau penjadwalan shift.
                </p>
            </div>
        </div>

        <div class="card" style="margin-top:12px;">
            <h2>Daftar Petugas</h2>
            <div class="table-wrapper" style="margin-top:8px;">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nama Petugas</th>
                        <th>No HP</th>
                        <th>Shift</th>
                        <th>Aksi</th>
                    </tr>

                    <?php
                    if ($result_petugas && mysqli_num_rows($result_petugas) > 0) {
                        while ($row = mysqli_fetch_assoc($result_petugas)) {
                            echo "<tr>";
                            echo "<td>".$row['id_petugas']."</td>";
                            echo "<td>".$row['nama_petugas']."</td>";
                            echo "<td>".$row['no_hp']."</td>";
                            echo "<td>".$row['shift']."</td>";
                            echo "<td>
                                    <a href='petugas.php?edit=".$row['id_petugas']."' class='btn btn-secondary'>
                                        Edit
                                    </a>
                                    <a href='petugas.php?hapus=".$row['id_petugas']."'
                                       class='btn btn-danger'
                                       onclick=\"return confirm('Hapus petugas ini?')\">
                                       Hapus
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Belum ada data petugas.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
