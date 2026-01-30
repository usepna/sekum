<?php
ob_start(); // Safety buffer to prevent "Headers already sent"
include 'koneksi.php';

function sql_val($value) {
    global $conn;
    if ($value === '' || $value === null || $value === 'NULL') return "NULL";
    return "'" . mysqli_real_escape_string($conn, $value) . "'";
}

if (isset($_POST['simpan'])) {
    $id = $_POST['id'];
    
    // Sanitize basic inputs
    $no_gaji = mysqli_real_escape_string($conn, $_POST['no_daftar_gaji']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nip = mysqli_real_escape_string($conn, $_POST['nip']);
    $pkt = mysqli_real_escape_string($conn, $_POST['pangkat_golongan']);
    $jbt = mysqli_real_escape_string($conn, $_POST['jabatan_struktural_fungsional']);
    
    // --- AMBIL DATA TMT BARU ---
    $tmt_gol = sql_val($_POST['tmt_golongan']);
    $tmt_jab = sql_val($_POST['tmt_jabatan']);
    
    // ... Ambil sisa data ...
    $tmpl = mysqli_real_escape_string($conn, $_POST['tempat_lahir']); 
    $tgll = sql_val($_POST['tanggal_lahir']);
    $jk = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']); 
    $agm = mysqli_real_escape_string($conn, $_POST['agama']); 
    $bgs = mysqli_real_escape_string($conn, $_POST['kebangsaan']);
    $ins = mysqli_real_escape_string($conn, $_POST['instansi_induk']); 
    $mth = mysqli_real_escape_string($conn, $_POST['masa_kerja_tahun']); 
    $mbl = mysqli_real_escape_string($conn, $_POST['masa_kerja_bulan']); 
    $ketmk = mysqli_real_escape_string($conn, $_POST['keterangan_masa_kerja']); 
    $perg = mysqli_real_escape_string($conn, $_POST['peraturan_gaji']);
    $gapok = mysqli_real_escape_string($conn, $_POST['gaji_pokok']); 
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat_lengkap']); 
    $jabsamp = mysqli_real_escape_string($conn, $_POST['jabatan_sampingan']); 
    $pensamp = mysqli_real_escape_string($conn, $_POST['penghasilan_sampingan']);
    $penjan = mysqli_real_escape_string($conn, $_POST['pensiun_janda_rp']); 
    $jumanak = mysqli_real_escape_string($conn, $_POST['jumlah_anak_seluruhnya']);

    // Cek Duplikat NIP
    if (empty($id)) {
        $check_nip = mysqli_query($conn, "SELECT id FROM pegawai WHERE nip = '$nip'");
        if (mysqli_num_rows($check_nip) > 0) {
            echo "<script>alert('NIP sudah ada!');history.back();</script>"; 
            exit();
        }
    }

    if ($id) {
        $q = "UPDATE pegawai SET 
            no_daftar_gaji='$no_gaji', nama_lengkap='$nama', nip='$nip', tempat_lahir='$tmpl', tanggal_lahir=$tgll, 
            jenis_kelamin='$jk', agama='$agm', kebangsaan='$bgs', 
            pangkat_golongan='$pkt', tmt_golongan=$tmt_gol, 
            jabatan_struktural_fungsional='$jbt', tmt_jabatan=$tmt_jab,
            instansi_induk='$ins', masa_kerja_tahun='$mth', masa_kerja_bulan='$mbl', 
            keterangan_masa_kerja='$ketmk', peraturan_gaji='$perg', gaji_pokok='$gapok', 
            alamat_lengkap='$alamat', jabatan_sampingan='$jabsamp', penghasilan_sampingan='$pensamp', 
            pensiun_janda_rp='$penjan', jumlah_anak_seluruhnya='$jumanak' 
            WHERE id=$id";
    } else {
        $q = "INSERT INTO pegawai (
            no_daftar_gaji, nama_lengkap, nip, tempat_lahir, tanggal_lahir, 
            jenis_kelamin, agama, kebangsaan, 
            pangkat_golongan, tmt_golongan, 
            jabatan_struktural_fungsional, tmt_jabatan,
            instansi_induk, masa_kerja_tahun, masa_kerja_bulan, keterangan_masa_kerja, 
            peraturan_gaji, gaji_pokok, alamat_lengkap, jabatan_sampingan, 
            penghasilan_sampingan, pensiun_janda_rp, jumlah_anak_seluruhnya
        ) VALUES (
            '$no_gaji', '$nama', '$nip', '$tmpl', $tgll, 
            '$jk', '$agm', '$bgs', 
            '$pkt', $tmt_gol, 
            '$jbt', $tmt_jab, 
            '$ins', '$mth', '$mbl', '$ketmk', 
            '$perg', '$gapok', '$alamat', '$jabsamp', 
            '$pensamp', '$penjan', '$jumanak'
        )";
    }
    
    if(!mysqli_query($conn, $q)) die("Error Pegawai: ".mysqli_error($conn));
    if(empty($id)) $id = mysqli_insert_id($conn);

    // --- SIMPAN PASANGAN ---
    mysqli_query($conn, "DELETE FROM pasangan WHERE pegawai_id=$id");
    if(isset($_POST['nm_pas'])) {
        foreach($_POST['nm_pas'] as $k => $v) {
            if($v) {
                $v = mysqli_real_escape_string($conn, $v);
                $lhr = sql_val($_POST['lhr_pas'][$k]); 
                $nik = sql_val($_POST['nik_pas'][$k]); 
                $job = mysqli_real_escape_string($conn, $_POST['job_pas'][$k]); 
                $inc = mysqli_real_escape_string($conn, $_POST['inc_pas'][$k]); 
                $ket = mysqli_real_escape_string($conn, $_POST['ket_pas'][$k]);
                mysqli_query($conn, "INSERT INTO pasangan (pegawai_id, nama_pasangan, tanggal_lahir, tanggal_perkawinan, pekerjaan, penghasilan_sebulan, keterangan) VALUES ($id, '$v', $lhr, $nik, '$job', '$inc', '$ket')");
            }
        }
    }

    // --- SIMPAN ANAK ---
    mysqli_query($conn, "DELETE FROM anak WHERE pegawai_id=$id");
    if(isset($_POST['nm_anak'])) {
        foreach($_POST['nm_anak'] as $k => $v) {
            if($v) {
                $v = mysqli_real_escape_string($conn, $v);
                $st = mysqli_real_escape_string($conn, $_POST['st_anak'][$k]); 
                $lhr = sql_val($_POST['lhr_anak'][$k]); 
                $sek = mysqli_real_escape_string($conn, $_POST['sek_anak'][$k]); 
                $msk = mysqli_real_escape_string($conn, $_POST['msk_gaji'][$k]);
                $ayah = mysqli_real_escape_string($conn, $_POST['ayah_anak'][$k]); 
                $ibu = mysqli_real_escape_string($conn, $_POST['ibu_anak'][$k]); 
                $ortu = sql_val($_POST['ortu_anak'][$k]); 
                $kerja = sql_val($_POST['kerja_anak'][$k]); 
                $ket = mysqli_real_escape_string($conn, $_POST['ket_anak'][$k]);
                $bea = mysqli_real_escape_string($conn, $_POST['bea_anak'][$k]); 
                $dinas = mysqli_real_escape_string($conn, $_POST['dinas_anak'][$k]); 
                
                $q_anak = "INSERT INTO anak (pegawai_id, nama_anak, status_anak, tanggal_lahir, sekolah_kuliah_pada, masuk_daftar_gaji, nama_ayah, nama_ibu, tgl_wafat_cerai_ortu, status_bekerja, keterangan, status_belum_kawin, tidak_dapat_beasiswa, tidak_dapat_ikatan_dinas) VALUES ($id, '$v', '$st', $lhr, '$sek', '$msk', '$ayah', '$ibu', $ortu, $kerja, '$ket', 'Ya', '$bea', '$dinas')";
                mysqli_query($conn, $q_anak);
            }
        }
    }

    header("Location: cetak_sktk.php?id=$id");
    exit();
}
ob_end_flush();
?>
