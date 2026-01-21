<?php
include 'koneksi.php';

function sql_val($value) {
    global $conn;
    if ($value === '' || $value === null || $value === 'NULL') return "NULL";
    return "'" . mysqli_real_escape_string($conn, $value) . "'";
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus_pejabat') {
    mysqli_query($conn, "DELETE FROM pejabat WHERE id=".$_GET['id']);
    header("Location: pejabat.php"); exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    mysqli_query($conn, "DELETE FROM pegawai WHERE id = ".$_GET['id']);
    header("Location: index.php?pesan=Data berhasil dihapus"); exit();
}

if (isset($_POST['simpan_pejabat'])) {
    $id = $_POST['id_pejabat']; $nama = $_POST['nama']; $nip = $_POST['nip']; $jab = $_POST['jabatan']; $ins = $_POST['instansi'];
    $q = $id ? "UPDATE pejabat SET nama_pejabat='$nama', nip='$nip', jabatan='$jab', instansi='$ins' WHERE id=$id" 
             : "INSERT INTO pejabat (nama_pejabat, nip, jabatan, instansi) VALUES ('$nama', '$nip', '$jab', '$ins')";
    mysqli_query($conn, $q); header("Location: pejabat.php"); exit();
}

if (isset($_POST['simpan'])) {
    $id = $_POST['id'];
    $no_gaji = $_POST['no_daftar_gaji']; $nama = $_POST['nama_lengkap']; $nip = $_POST['nip']; $tmpl = $_POST['tempat_lahir']; $tgll = sql_val($_POST['tanggal_lahir']);
    $jk = $_POST['jenis_kelamin']; $agm = $_POST['agama']; $bgs = $_POST['kebangsaan']; $pkt = $_POST['pangkat_golongan']; $jbt = $_POST['jabatan_struktural_fungsional'];
    $ins = $_POST['instansi_induk']; $mth = $_POST['masa_kerja_tahun']; $mbl = $_POST['masa_kerja_bulan']; $ketmk = $_POST['keterangan_masa_kerja']; $perg = $_POST['peraturan_gaji'];
    $gapok = $_POST['gaji_pokok']; $alamat = $_POST['alamat_lengkap']; $jabsamp = $_POST['jabatan_sampingan']; $pensamp = $_POST['penghasilan_sampingan'];
    $penjan = $_POST['pensiun_janda_rp']; $jumanak = $_POST['jumlah_anak_seluruhnya'];

    if (empty($id)) {
        if (mysqli_num_rows(mysqli_query($conn, "SELECT id FROM pegawai WHERE nip = '$nip'")) > 0) {
            echo "<script>alert('NIP sudah ada!');history.back();</script>"; exit();
        }
    }

    if ($id) {
        $q = "UPDATE pegawai SET no_daftar_gaji='$no_gaji', nama_lengkap='$nama', nip='$nip', tempat_lahir='$tmpl', tanggal_lahir=$tgll, jenis_kelamin='$jk', agama='$agm', kebangsaan='$bgs', pangkat_golongan='$pkt', jabatan_struktural_fungsional='$jbt', instansi_induk='$ins', masa_kerja_tahun='$mth', masa_kerja_bulan='$mbl', keterangan_masa_kerja='$ketmk', peraturan_gaji='$perg', gaji_pokok='$gapok', alamat_lengkap='$alamat', jabatan_sampingan='$jabsamp', penghasilan_sampingan='$pensamp', pensiun_janda_rp='$penjan', jumlah_anak_seluruhnya='$jumanak' WHERE id=$id";
    } else {
        $q = "INSERT INTO pegawai (no_daftar_gaji, nama_lengkap, nip, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, kebangsaan, pangkat_golongan, jabatan_struktural_fungsional, instansi_induk, masa_kerja_tahun, masa_kerja_bulan, keterangan_masa_kerja, peraturan_gaji, gaji_pokok, alamat_lengkap, jabatan_sampingan, penghasilan_sampingan, pensiun_janda_rp, jumlah_anak_seluruhnya) VALUES ('$no_gaji', '$nama', '$nip', '$tmpl', $tgll, '$jk', '$agm', '$bgs', '$pkt', '$jbt', '$ins', '$mth', '$mbl', '$ketmk', '$perg', '$gapok', '$alamat', '$jabsamp', '$pensamp', '$penjan', '$jumanak')";
    }
    
    if(!mysqli_query($conn, $q)) die("Error Pegawai: ".mysqli_error($conn));
    if(empty($id)) $id = mysqli_insert_id($conn);

    mysqli_query($conn, "DELETE FROM pasangan WHERE pegawai_id=$id");
    if(isset($_POST['nm_pas'])) {
        foreach($_POST['nm_pas'] as $k => $v) {
            if($v) {
                $lhr = sql_val($_POST['lhr_pas'][$k]); $nik = sql_val($_POST['nik_pas'][$k]); $job = $_POST['job_pas'][$k]; $inc = $_POST['inc_pas'][$k]; $ket = $_POST['ket_pas'][$k];
                mysqli_query($conn, "INSERT INTO pasangan (pegawai_id, nama_pasangan, tanggal_lahir, tanggal_perkawinan, pekerjaan, penghasilan_sebulan, keterangan) VALUES ($id, '$v', $lhr, $nik, '$job', '$inc', '$ket')");
            }
        }
    }

    mysqli_query($conn, "DELETE FROM anak WHERE pegawai_id=$id");
    if(isset($_POST['nm_anak'])) {
        foreach($_POST['nm_anak'] as $k => $v) {
            if($v) {
                $st = $_POST['st_anak'][$k]; $lhr = sql_val($_POST['lhr_anak'][$k]); $sek = $_POST['sek_anak'][$k]; $msk = $_POST['msk_gaji'][$k];
                $ayah = $_POST['ayah_anak'][$k]; $ibu = $_POST['ibu_anak'][$k]; $ortu = sql_val($_POST['ortu_anak'][$k]); 
                $kerja = sql_val($_POST['kerja_anak'][$k]); $ket = $_POST['ket_anak'][$k];
                
                // DATA INPUT BARU
                $bea = $_POST['bea_anak'][$k];   // Ambil input Beasiswa
                $dinas = $_POST['dinas_anak'][$k]; // Ambil input Ikatan Dinas
                $bk = 'Ya'; // Default belum kawin

                $q_anak = "INSERT INTO anak (pegawai_id, nama_anak, status_anak, tanggal_lahir, sekolah_kuliah_pada, masuk_daftar_gaji, nama_ayah, nama_ibu, tgl_wafat_cerai_ortu, status_bekerja, keterangan, status_belum_kawin, tidak_dapat_beasiswa, tidak_dapat_ikatan_dinas) VALUES ($id, '$v', '$st', $lhr, '$sek', '$msk', '$ayah', '$ibu', $ortu, $kerja, '$ket', '$bk', '$bea', '$dinas')";
                
                if(!mysqli_query($conn, $q_anak)) die("Error Anak: " . mysqli_error($conn));
            }
        }
    }

    header("Location: cetak_sktk.php?id=$id");
    exit();
}
?>