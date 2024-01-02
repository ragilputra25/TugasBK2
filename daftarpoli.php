<?php
if (!isset($_SESSION)) {
    session_start();
}

$no_antrian = 0;
$button_disabled = "";

// Check if the patient has already registered
$check_query = "SELECT * FROM daftar_poli WHERE id_pasien = '".$_SESSION['id_pasien']."'";
$check_result = $mysqli->query($check_query);
if ($check_result->num_rows > 0) {
    $button_disabled = "disabled";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $button_disabled != "disabled") {
    $keluhan = $_POST['keluhan'];
    $id_jadwal = $_POST['id_jadwal'];

    // Check if the patient has already registered
    $check_query = "SELECT * FROM daftar_poli WHERE id_pasien = '".$_SESSION['id_pasien']."'";
    $check_result = $mysqli->query($check_query);
    if ($check_result->num_rows > 0) {
        echo "<script>alert('Anda sudah Mengantri Untuk Hari ini!!');</script>";
    } else {
        // Check if the form fields are not empty
        if (!empty($keluhan) && !empty($id_jadwal)) {
            // Get the current highest no_antrian
            $query = "SELECT MAX(no_antrian) as max_no FROM daftar_poli";
            $result = $mysqli->query($query);
            $row = $result->fetch_assoc();
            $no_antrian = $row['max_no'] + 1;

            // Insert the new poli registration into the daftar_poli table
            $insert_query = "INSERT INTO daftar_poli (id_pasien, id_jadwal, keluhan, no_antrian, tanggal) VALUES ('".$_SESSION['id_pasien']."', '$id_jadwal', '$keluhan', '$no_antrian', NOW())";
            if (mysqli_query($mysqli, $insert_query)) {
                echo "<script>alert('No antrian anda adalah $no_antrian');</script>";
                $button_disabled = "disabled";
                // Redirect to prevent form resubmission
                header("Location: index.php?page=daftarpoli&no_antrian=$no_antrian");
            } else {
                $error = "Pendaftaran gagal";
            }
        } else {
            $error = "Please fill in all fields";
        }
    }
}

// Fetch the doctors and their schedules
$query = "SELECT dokter.id AS dokter_id, dokter.nama AS dokter_nama, jadwal_periksa.id AS jadwal_id, jadwal_periksa.hari AS hari, jadwal_periksa.jam_mulai AS jam_mulai, jadwal_periksa.jam_selesai AS jam_selesai FROM dokter JOIN jadwal_periksa ON dokter.id = jadwal_periksa.id_dokter";
$result = $mysqli->query($query);
if (!$result) {
    die("Query error: " . $mysqli->error);
}
$dokter_schedules = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center" style="font-weight: bold; font-size: 32px;">Daftar Poli</div>
                <div class="card-body">
    <?php
    if (isset($_SESSION['no_rm'])) {
        echo '<div class="alert alert-success">Nomor RM Anda adalah: ' . $_SESSION['no_rm'] . '</div>';
    }
    if (isset($error)) {
        echo '<div class="alert alert-danger">' . $error . '
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
              </div>';
    }
    ?>
    <form method="POST" action="index.php?page=daftarpoli">
        <div class="form-group">
            <label for="keluhan">Keluhan</label>
            <input type="text" name="keluhan" class="form-control" required placeholder="Masukkan keluhan anda">
        </div>
        <div class="form-group">
            <label for="id_jadwal">Jadwal Dokter</label>
            <select name="id_jadwal" class="form-control">
                <?php
                foreach ($dokter_schedules as $schedule) {
                    echo '<option value="'.$schedule['jadwal_id'].'">'.$schedule['dokter_nama'].' - '.$schedule['hari'].' ('.$schedule['jam_mulai'].' - '.$schedule['jam_selesai'].')</option>';
                }
                ?>
            </select>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-block" <?php echo $button_disabled; ?>>Daftar Poli</button>
        </div>
    </form>
</div>
            </div>
        </div>
    </div>
</div>

<?php
// Display the no_antrian alert
if (isset($_GET['no_antrian'])) {
    echo "<script>alert('No antrian anda adalah ".$_GET['no_antrian']."');</script>";
}
?>