<?php
ob_start(); 
include 'koneksi.php';

// Helper untuk menangani nilai NULL pada database
function sql_val($value) {
    global $conn;
    if ($value === '' || $value === null || $value === 'NULL' || $value === '-') return "NULL";
    return "'" . mysqli_real_escape_string($conn, $value) . "'";
}

// ==========================================
// 1. PROSES SIMPAN / UPDATE PEGAWAI & KELUARGA
// ==========================================
if (isset($_POST['simpan'])) {
    $id = $_POST['id'];
    
    // Ambil data dari $_POST
    $no_gaji = mysqli_real_escape_string($conn, $_POST['no_daftar_gaji']);
    $nama    = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nip     = mysqli_real_escape_string($conn, $_POST['nip']);
    $pangkat = mysqli_real_escape_string($conn, $_POST['pangkat_golongan']);
    $tmt_gol = sql_val($_POST['tmt_golongan']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan_struktural_fungsional']);
    $tmt_jab = sql_val($_POST['tmt_jabatan']);
    $unit    = mysqli_real_escape_string($conn, $_POST['unit_kerja']);
    $instansi= mysqli_real_escape_string($conn, $_POST['instansi_induk']);
    $masa_ker= mysqli_real_escape_string($conn, $_POST['masa_kerja_golongan']);
    $gaji    = (int)$_POST['gaji_pokok_terakhir'];

    if ($id) {
        // UPDATE PEGAWAI
        $sql = "UPDATE pegawai SET 
                no_daftar_gaji='$no_gaji', nama_lengkap='$nama', nip='$nip', 
                pangkat_golongan='$pangkat', tmt_golongan=$tmt_gol, 
                jabatan_struktural_fungsional='$jabatan', tmt_jabatan=$tmt_jab, 
                unit_kerja='$unit', instansi_induk='$instansi', 
                masa_kerja_golongan='$masa_ker', gaji_pokok_terakhir='$gaji' 
                WHERE id='$id'";
        mysqli_query($conn, $sql);
    } else {
        // INSERT PEGAWAI BARU
        $sql = "INSERT INTO pegawai (no_daftar_gaji, nama_lengkap, nip, pangkat_golongan, tmt_golongan, jabatan_struktural_fungsional, tmt_jabatan, unit_kerja, instansi_induk, masa_kerja_golongan, gaji_pokok_terakhir) 
                VALUES ('$no_gaji', '$nama', '$nip', '$pangkat', $tmt_gol, '$jabatan', $tmt_jab, '$unit', '$instansi', '$masa_ker', '$gaji')";
        mysqli_query($conn, $sql);
        $id = mysqli_insert_id($conn);
    }

    // --- PROSES DATA PASANGAN (Hapus lama, Input baru) ---
    mysqli_query($conn, "DELETE FROM pasangan WHERE pegawai_id = '$id'");
    if (isset($_POST['nm_pasangan'])) {
        foreach ($_POST['nm_pasangan'] as $k => $v) {
            if (empty($v)) continue;
            $nama_p  = mysqli_real_escape_string($conn, $v);
            $tgl_lhr = sql_val($_POST['lhr_pasangan'][$k]);
            $tgl_nkh = sql_val($_POST['nikah_pasangan'][$k]);
            $pekrjn  = mysqli_real_escape_string($conn, $_POST['kerja_pasangan'][$k]);
            $nip_p   = mysqli_real_escape_string($conn, $_POST['nip_pasangan'][$k]);

            mysqli_query($conn, "INSERT INTO pasangan (pegawai_id, nama_pasangan, tanggal_lahir, tanggal_perkawinan, pekerjaan, nip_pasangan) 
                                 VALUES ('$id', '$nama_p', $tgl_lhr, $tgl_nkh, '$pekrjn', '$nip_p')");
        }
    }

    // --- PROSES DATA ANAK (Hapus lama, Input baru) ---
    mysqli_query($conn, "DELETE FROM anak WHERE pegawai_id = '$id'");
    if (isset($_POST['nm_anak'])) {
        foreach ($_POST['nm_anak'] as $k => $v) {
            if (empty($v)) continue;
            $nama_a  = mysqli_real_escape_string($conn, $v);
            $sts_a   = mysqli_real_escape_string($conn, $_POST['st_anak'][$k]);
            $lhr_a   = sql_val($_POST['lhr_anak'][$k]);
            $sek_a   = mysqli_real_escape_string($conn, $_POST['sek_anak'][$k]);
            $msk_gaji= mysqli_real_escape_string($conn, $_POST['msk_gaji'][$k]);
            $kerja_a = mysqli_real_escape_string($conn, $_POST['kerja_anak'][$k]);
            $ket_a   = mysqli_real_escape_string($conn, $_POST['ket_anak'][$k]);
            $bea_a   = mysqli_real_escape_string($conn, $_POST['bea_anak'][$k]);
            $dinas_a = mysqli_real_escape_string($conn, $_POST['dinas_anak'][$k]);

            mysqli_query($conn, "INSERT INTO anak (pegawai_id, nama_anak, status_anak, tanggal_lahir, sekolah_kuliah_pada, masuk_daftar_gaji, status_bekerja, keterangan, status_belum_kawin, tidak_dapat_beasiswa, tidak_dapat_ikatan_dinas) 
                                 VALUES ('$id', '$nama_a', '$sts_a', $lhr_a, '$sek_a', '$msk_gaji', '$kerja_a', '$ket_a', 'Ya', '$bea_a', '$dinas_a')");
        }
    }

    header("Location: index.php?status=sukses");
    exit();
}

// ==========================================
// 2. PROSES SIMPAN / UPDATE PEJABAT
// ==========================================
if (isset($_POST['simpan_pejabat'])) {
    $id_pejabat = $_POST['id_pejabat'];
    $nama       = mysqli_real_escape_string($conn, $_POST['nama_pejabat']);
    $nip        = mysqli_real_escape_string($conn, $_POST['nip_pejabat']);
    $jabatan    = mysqli_real_escape_string($conn, $_POST['jabatan_pejabat']);

    if (!empty($id_pejabat)) {
        // Update
        $q_pejabat = "UPDATE pejabat SET nama_pejabat='$nama', nip='$nip', jabatan='$jabatan' WHERE id='$id_pejabat'";
    } else {
        // Insert
        $q_pejabat = "INSERT INTO pejabat (nama_pejabat, nip, jabatan) VALUES ('$nama', '$nip', '$jabatan')";
    }

    if (mysqli_query($conn, $q_pejabat)) {
        header("Location: pejabat.php?status=sukses");
    } else {
        die("Gagal menyimpan data pejabat: " . mysqli_error($conn));
    }
    exit();
}

// ==========================================
// 3. FITUR HAPUS DATA
// ==========================================

// Hapus Pegawai
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id_hapus = (int)$_GET['id'];
    mysqli_query($conn, "DELETE FROM pasangan WHERE pegawai_id = '$id_hapus'");
    mysqli_query($conn, "DELETE FROM anak WHERE pegawai_id = '$id_hapus'");
    mysqli_query($conn, "DELETE FROM pegawai WHERE id = '$id_hapus'");
    header("Location: index.php?status=terhapus");
    exit();
}

// Hapus Pejabat
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus_pejabat') {
    $id_pejabat = (int)$_GET['id'];
    mysqli_query($conn, "DELETE FROM pejabat WHERE id = '$id_pejabat'");
    header("Location: pejabat.php?status=terhapus");
    exit();
}
?>
