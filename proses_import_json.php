<?php
include 'koneksi.php';

// FUNGSI HELPER: Membersihkan & Validasi Tanggal
function format_date_sql($date_str) {
    // Jika data kosong, null, atau berisi teks placeholder, kembalikan NULL
    if (empty($date_str) || $date_str === 'YYYY-MM-DD' || $date_str === '-' || $date_str === 'null') {
        return "NULL";
    }
    // Cek apakah formatnya benar-benar angka YYYY-MM-DD
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $date_str)) {
        return "'$date_str'";
    }
    // Jika format salah, default ke NULL biar tidak error
    return "NULL";
}

// FUNGSI HELPER: Membersihkan String
function clean($conn, $val) {
    $val = trim($val ?? '');
    return ($val === '-' || $val === 'YYYY-MM-DD') ? '' : mysqli_real_escape_string($conn, $val);
}

if (isset($_POST['import'])) {
    $fileName = $_FILES["file_json"]["tmp_name"];

    if ($_FILES["file_json"]["size"] > 0) {
        
        $jsonContent = file_get_contents($fileName);
        $data = json_decode($jsonContent, true);

        if ($data === null) {
            die("Error: Format JSON tidak valid / Corrupt.");
        }

        $sukses = 0;
        $gagal = 0;

        foreach ($data as $peg) {
            // 1. BERSIHKAN NIP (Primary Key Check)
            $nip = mysqli_real_escape_string($conn, $peg['nip'] ?? '');
            $nip = str_replace("'", "", $nip); // Hapus tanda kutip jika ada

            if (empty($nip)) continue;

            // Cek Duplikat NIP
            $cek = mysqli_query($conn, "SELECT id FROM pegawai WHERE nip = '$nip'");
            if (mysqli_num_rows($cek) > 0) {
                $gagal++; 
                continue; // Skip data ini
            }

            // 2. SIAPKAN DATA PEGAWAI
            $f_no_gaji  = clean($conn, $peg['no_daftar_gaji']);
            $f_nama     = clean($conn, $peg['nama_lengkap']);
            $f_tmpl     = clean($conn, $peg['tempat_lahir']);
            
            // Gunakan fungsi format_date_sql untuk semua tanggal
            $sql_tgl_lhr = format_date_sql($peg['tanggal_lahir'] ?? '');

            $f_jk       = clean($conn, $peg['jenis_kelamin']);
            $f_agama    = clean($conn, $peg['agama']);
            $f_bangsa   = clean($conn, $peg['kebangsaan']);
            $f_pangkat  = clean($conn, $peg['pangkat_golongan']);
            $f_jabatan  = clean($conn, $peg['jabatan_struktural_fungsional']);
            $f_instansi = clean($conn, $peg['instansi_induk']);
            
            $f_mk_thn   = intval($peg['masa_kerja_tahun'] ?? 0);
            $f_mk_bln   = intval($peg['masa_kerja_bulan'] ?? 0);
            $f_ket_mk   = clean($conn, $peg['keterangan_masa_kerja']);
            
            $f_per_gaji = clean($conn, $peg['peraturan_gaji']);
            $f_gapok    = floatval($peg['gaji_pokok'] ?? 0);
            $f_alamat   = clean($conn, $peg['alamat_lengkap']);
            
            $f_jab_samping = clean($conn, $peg['jabatan_sampingan']);
            $f_pen_samping = floatval($peg['penghasilan_sampingan'] ?? 0);
            $f_pensiun  = floatval($peg['pensiun_janda_rp'] ?? 0);
            $f_jml_anak = intval($peg['jumlah_anak_seluruhnya'] ?? 0);

            // 3. INSERT PEGAWAI
            $q_peg = "INSERT INTO pegawai (
                nip, no_daftar_gaji, nama_lengkap, tempat_lahir, tanggal_lahir, 
                jenis_kelamin, agama, kebangsaan, pangkat_golongan, jabatan_struktural_fungsional, 
                instansi_induk, masa_kerja_tahun, masa_kerja_bulan, keterangan_masa_kerja, 
                peraturan_gaji, gaji_pokok, alamat_lengkap, jabatan_sampingan, 
                penghasilan_sampingan, pensiun_janda_rp, jumlah_anak_seluruhnya
            ) VALUES (
                '$nip', '$f_no_gaji', '$f_nama', '$f_tmpl', $sql_tgl_lhr, 
                '$f_jk', '$f_agama', '$f_bangsa', '$f_pangkat', '$f_jabatan', 
                '$f_instansi', $f_mk_thn, $f_mk_bln, '$f_ket_mk', 
                '$f_per_gaji', $f_gapok, '$f_alamat', '$f_jab_samping', 
                $f_pen_samping, $f_pensiun, $f_jml_anak
            )";

            if (mysqli_query($conn, $q_peg)) {
                $id_pegawai = mysqli_insert_id($conn);
                $sukses++;

                // 4. INSERT PASANGAN (Loop Array)
                if (!empty($peg['pasangan']) && is_array($peg['pasangan'])) {
                    foreach ($peg['pasangan'] as $pas) {
                        $p_nama = clean($conn, $pas['nama_pasangan']);
                        if(empty($p_nama)) continue;

                        // Validasi Tanggal Pasangan
                        $sql_p_lhr   = format_date_sql($pas['tanggal_lahir'] ?? '');
                        $sql_p_nikah = format_date_sql($pas['tanggal_perkawinan'] ?? '');
                        
                        $p_job  = clean($conn, $pas['pekerjaan']);
                        $p_gaji = floatval($pas['penghasilan_sebulan'] ?? 0);
                        $p_ket  = clean($conn, $pas['keterangan']);

                        $q_pas = "INSERT INTO pasangan (pegawai_id, nama_pasangan, tanggal_lahir, tanggal_perkawinan, pekerjaan, penghasilan_sebulan, keterangan)
                                  VALUES ($id_pegawai, '$p_nama', $sql_p_lhr, $sql_p_nikah, '$p_job', $p_gaji, '$p_ket')";
                        
                        if(!mysqli_query($conn, $q_pas)) { 
                            // Opsi: catat error log jika perlu
                        }
                    }
                }

                // 5. INSERT ANAK (Loop Array)
                if (!empty($peg['anak']) && is_array($peg['anak'])) {
                    foreach ($peg['anak'] as $ank) {
                        $a_nama  = clean($conn, $ank['nama_anak']);
                        if(empty($a_nama)) continue;

                        $a_sts   = clean($conn, $ank['status_anak'] ?: 'ak');
                        
                        // Validasi Tanggal Anak
                        $sql_a_lhr  = format_date_sql($ank['tanggal_lahir'] ?? '');
                        $sql_a_ortu = format_date_sql($ank['tgl_wafat_cerai_ortu'] ?? '');

                        $a_sek   = clean($conn, $ank['sekolah_kuliah_pada']);
                        $a_msk   = clean($conn, $ank['masuk_daftar_gaji'] ?: 'Ya');
                        $a_ayah  = clean($conn, $ank['nama_ayah']);
                        $a_ibu   = clean($conn, $ank['nama_ibu']);
                        
                        $a_kerja = clean($conn, $ank['status_bekerja'] ?: 'Tidak');
                        $a_ket   = clean($conn, $ank['keterangan']);
                        $a_bea   = clean($conn, $ank['tidak_dapat_beasiswa'] ?: 'Ya');
                        $a_dinas = clean($conn, $ank['tidak_dapat_ikatan_dinas'] ?: 'Ya');

                        $q_ank = "INSERT INTO anak (
                            pegawai_id, nama_anak, status_anak, tanggal_lahir, sekolah_kuliah_pada, 
                            masuk_daftar_gaji, nama_ayah, nama_ibu, tgl_wafat_cerai_ortu, 
                            status_bekerja, keterangan, status_belum_kawin, tidak_dapat_beasiswa, tidak_dapat_ikatan_dinas
                        ) VALUES (
                            $id_pegawai, '$a_nama', '$a_sts', $sql_a_lhr, '$a_sek', 
                            '$a_msk', '$a_ayah', '$a_ibu', $sql_a_ortu, 
                            '$a_kerja', '$a_ket', 'Ya', '$a_bea', '$a_dinas'
                        )";
                        
                        if(!mysqli_query($conn, $q_ank)) {
                            // Opsi: catat error log
                        }
                    }
                }

            } else {
                $gagal++;
            }
        }

        header("Location: index.php?pesan=Import JSON Selesai. Sukses: $sukses, Gagal/Duplikat: $gagal");
    } else {
        header("Location: import_json.php?pesan=File kosong");
    }
}
?>