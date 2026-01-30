<?php
include 'koneksi.php';

function sql_val($value) {
    global $conn;
    if ($value === '' || $value === null || $value === 'NULL') return "NULL";
    return "'" . mysqli_real_escape_string($conn, $value) . "'";
}

// ... (Kode hapus pejabat & pegawai tetap sama) ...

if (isset($_POST['simpan'])) {
    $id = $_POST['id'];
    
    // Ambil Data Lain (Nama, NIP, dll tetap sama...)
    $no_gaji = $_POST['no_daftar_gaji'];
    $nama = $_POST['nama_lengkap'];
    $nip = $_POST['nip'];
    // ... dst ...
    $pkt = $_POST['pangkat_golongan'];
    $jbt = $_POST['jabatan_struktural_fungsional'];
    
    // --- AMBIL DATA TMT BARU ---
    $tmt_gol = sql_val($_POST['tmt_golongan']);
    $tmt_jab = sql_val($_POST['tmt_jabatan']);
    
    // ... Ambil sisa data (Gaji, Alamat, dll) ...
    $tmpl = $_POST['tempat_lahir']; 
    $tgll = sql_val($_POST['tanggal_lahir']);
    $jk = $_POST['jenis_kelamin']; 
    $agm = $_POST['agama']; 
    $bgs = $_POST['kebangsaan'];
    $ins = $_POST['instansi_induk']; 
    $mth = $_POST['masa_kerja_tahun']; 
    $mbl = $_POST['masa_kerja_bulan']; 
    $ketmk = $_POST['keterangan_masa_kerja']; 
    $perg = $_POST['peraturan_gaji'];
    $gapok = $_POST['gaji_pokok']; 
    $alamat = $_POST['alamat_lengkap']; 
    $jabsamp = $_POST['jabatan_sampingan']; 
    $pensamp = $_POST['penghasilan_sampingan'];
    $penjan = $_POST['pensiun_janda_rp']; 
    $jumanak = $_POST['jumlah_anak_seluruhnya'];

    // Cek Duplikat NIP
    if (empty($id)) {
        if (mysqli_num_rows(mysqli_query($conn, "SELECT id FROM pegawai WHERE nip = '$nip'")) > 0) {
            echo "<script>alert('NIP sudah ada!');history.back();</script>"; exit();
        }
    }

    if ($id) {
        // UPDATE QUERY (Tambahkan tmt_golongan & tmt_jabatan)
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
        // INSERT QUERY (Tambahkan tmt_golongan & tmt_jabatan)
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

    // ... (Sisa kode simpan pasangan & anak TETAP SAMA, tidak perlu diubah) ...
    
    // REPEAT KODE ANAK & PASANGAN DISINI (Copy dari file sebelumnya)
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
                $bea = $_POST['bea_anak'][$k]; $dinas = $_POST['dinas_anak'][$k]; 
                
                $q_anak = "INSERT INTO anak (pegawai_id, nama_anak, status_anak, tanggal_lahir, sekolah_kuliah_pada, masuk_daftar_gaji, nama_ayah, nama_ibu, tgl_wafat_cerai_ortu, status_bekerja, keterangan, status_belum_kawin, tidak_dapat_beasiswa, tidak_dapat_ikatan_dinas) VALUES ($id, '$v', '$st', $lhr, '$sek', '$msk', '$ayah', '$ibu', $ortu, $kerja, '$ket', 'Ya', '$bea', '$dinas')";
                mysqli_query($conn, $q_anak);
            }
        }
    }

    header("Location: cetak_sktk.php?id=$id");
    exit();
}
?>