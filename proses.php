<?php
ob_start();
include 'koneksi.php';

// Helper untuk menangani nilai NULL pada database
function sql_val($value) {
    global $conn;
    if ($value === '' || $value === null || $value === 'NULL' || $value === '-') return "NULL";
    return "'" . mysqli_real_escape_string($conn, $value) . "'";
}

// Helper untuk escape string dengan aman (mendukung nilai kosong/null)
function esc($conn, $value) {
    return mysqli_real_escape_string($conn, $value ?? '');
}

// ==========================================
// 1. PROSES SIMPAN / UPDATE PEGAWAI & KELUARGA
// ==========================================
if (isset($_POST['simpan'])) {
    $id = (int)($_POST['id'] ?? 0); // Cast ke int untuk keamanan

    // ---- Ambil semua data dari $_POST dengan nilai default ----
    // Nama field disesuaikan dengan yang ada di form.php
    $no_gaji        = esc($conn, $_POST['no_daftar_gaji']               ?? '');
    $nama           = esc($conn, $_POST['nama_lengkap']                  ?? '');
    $nip            = esc($conn, $_POST['nip']                           ?? '');
    $tempat_lahir   = esc($conn, $_POST['tempat_lahir']                  ?? '');
    $tgl_lahir      = sql_val($_POST['tanggal_lahir']                    ?? '');
    $jenis_kelamin  = esc($conn, $_POST['jenis_kelamin']                 ?? '');
    $agama          = esc($conn, $_POST['agama']                         ?? '');
    $kebangsaan     = esc($conn, $_POST['kebangsaan']                    ?? 'Indonesia');
    $instansi       = esc($conn, $_POST['instansi_induk']                ?? '');
    $masa_ker_thn   = (int)($_POST['masa_kerja_tahun']                   ?? 0);
    $masa_ker_bln   = (int)($_POST['masa_kerja_bulan']                   ?? 0);
    $ket_masa_ker   = esc($conn, $_POST['keterangan_masa_kerja']         ?? '');
    $peraturan_gaji = esc($conn, $_POST['peraturan_gaji']                ?? 'PP Nomor 5 Tahun 2024');
    $gaji_pokok     = (int)($_POST['gaji_pokok']                         ?? 0);
    $alamat         = esc($conn, $_POST['alamat_lengkap']                ?? '');
    $jab_sampingan  = esc($conn, $_POST['jabatan_sampingan']             ?? '-');
    $pgh_sampingan  = esc($conn, $_POST['penghasilan_sampingan']         ?? '0');
    $pensiun_janda  = esc($conn, $_POST['pensiun_janda_rp']              ?? '0');
    $jml_anak       = (int)($_POST['jumlah_anak_seluruhnya']             ?? 0);
    $pangkat        = esc($conn, $_POST['pangkat_golongan']              ?? '');
    $tmt_gol        = sql_val($_POST['tmt_golongan']                     ?? '');
    $jabatan        = esc($conn, $_POST['jabatan_struktural_fungsional'] ?? '');
    $tmt_jab        = sql_val($_POST['tmt_jabatan']                      ?? '');

    mysqli_begin_transaction($conn); // Mulai transaksi agar data konsisten

    try {
        if ($id > 0) {
            // ---- UPDATE PEGAWAI ----
            $sql = "UPDATE pegawai SET
                        no_daftar_gaji                  = '$no_gaji',
                        nama_lengkap                    = '$nama',
                        nip                             = '$nip',
                        tempat_lahir                    = '$tempat_lahir',
                        tanggal_lahir                   = $tgl_lahir,
                        jenis_kelamin                   = '$jenis_kelamin',
                        agama                           = '$agama',
                        kebangsaan                      = '$kebangsaan',
                        instansi_induk                  = '$instansi',
                        masa_kerja_tahun                = '$masa_ker_thn',
                        masa_kerja_bulan                = '$masa_ker_bln',
                        keterangan_masa_kerja           = '$ket_masa_ker',
                        peraturan_gaji                  = '$peraturan_gaji',
                        gaji_pokok                      = '$gaji_pokok',
                        alamat_lengkap                  = '$alamat',
                        jabatan_sampingan               = '$jab_sampingan',
                        penghasilan_sampingan           = '$pgh_sampingan',
                        pensiun_janda_rp                = '$pensiun_janda',
                        jumlah_anak_seluruhnya          = '$jml_anak',
                        pangkat_golongan                = '$pangkat',
                        tmt_golongan                    = $tmt_gol,
                        jabatan_struktural_fungsional   = '$jabatan',
                        tmt_jabatan                     = $tmt_jab
                    WHERE id = $id";

            if (!mysqli_query($conn, $sql)) {
                throw new Exception("Gagal update pegawai: " . mysqli_error($conn));
            }

        } else {
            // ---- INSERT PEGAWAI BARU ----
            $sql = "INSERT INTO pegawai (
                        no_daftar_gaji, nama_lengkap, nip,
                        tempat_lahir, tanggal_lahir, jenis_kelamin,
                        agama, kebangsaan, instansi_induk,
                        masa_kerja_tahun, masa_kerja_bulan, keterangan_masa_kerja,
                        peraturan_gaji, gaji_pokok, alamat_lengkap,
                        jabatan_sampingan, penghasilan_sampingan, pensiun_janda_rp,
                        jumlah_anak_seluruhnya, pangkat_golongan, tmt_golongan,
                        jabatan_struktural_fungsional, tmt_jabatan
                    ) VALUES (
                        '$no_gaji', '$nama', '$nip',
                        '$tempat_lahir', $tgl_lahir, '$jenis_kelamin',
                        '$agama', '$kebangsaan', '$instansi',
                        '$masa_ker_thn', '$masa_ker_bln', '$ket_masa_ker',
                        '$peraturan_gaji', '$gaji_pokok', '$alamat',
                        '$jab_sampingan', '$pgh_sampingan', '$pensiun_janda',
                        '$jml_anak', '$pangkat', $tmt_gol,
                        '$jabatan', $tmt_jab
                    )";

            if (!mysqli_query($conn, $sql)) {
                throw new Exception("Gagal insert pegawai: " . mysqli_error($conn));
            }

            $id = mysqli_insert_id($conn);
        }

        // ---- PROSES DATA PASANGAN ----
        // Nama field di form: nm_pas[], lhr_pas[], nik_pas[], job_pas[], inc_pas[], ket_pas[]
        if (!mysqli_query($conn, "DELETE FROM pasangan WHERE pegawai_id = $id")) {
            throw new Exception("Gagal hapus pasangan lama: " . mysqli_error($conn));
        }

        if (!empty($_POST['nm_pas'])) {
            foreach ($_POST['nm_pas'] as $k => $v) {
                if (empty(trim($v))) continue;

                $nama_p    = esc($conn, $v);
                $tgl_lhr   = sql_val($_POST['lhr_pas'][$k] ?? '');
                $tgl_nkh   = sql_val($_POST['nik_pas'][$k] ?? '');
                $pekerjaan = esc($conn, $_POST['job_pas'][$k] ?? '');
                $penghasil = esc($conn, $_POST['inc_pas'][$k] ?? '0');
                $ket_pas   = esc($conn, $_POST['ket_pas'][$k] ?? '');

                $q_pas = "INSERT INTO pasangan (
                                pegawai_id, nama_pasangan, tanggal_lahir,
                                tanggal_perkawinan, pekerjaan, penghasilan_sebulan, keterangan
                            ) VALUES (
                                $id, '$nama_p', $tgl_lhr,
                                $tgl_nkh, '$pekerjaan', '$penghasil', '$ket_pas'
                            )";

                if (!mysqli_query($conn, $q_pas)) {
                    throw new Exception("Gagal insert pasangan: " . mysqli_error($conn));
                }
            }
        }

        // ---- PROSES DATA ANAK ----
        // Nama field di form: nm_anak[], st_anak[], lhr_anak[], sek_anak[],
        //                     msk_gaji[], bea_anak[], dinas_anak[], kerja_anak[],
        //                     ayah_anak[], ibu_anak[], ortu_anak[], ket_anak[]
        if (!mysqli_query($conn, "DELETE FROM anak WHERE pegawai_id = $id")) {
            throw new Exception("Gagal hapus anak lama: " . mysqli_error($conn));
        }

        if (!empty($_POST['nm_anak'])) {
            foreach ($_POST['nm_anak'] as $k => $v) {
                if (empty(trim($v))) continue;

                $nama_a   = esc($conn, $v);
                $sts_a    = esc($conn, $_POST['st_anak'][$k]    ?? 'ak');
                $lhr_a    = sql_val($_POST['lhr_anak'][$k]      ?? '');
                $sek_a    = esc($conn, $_POST['sek_anak'][$k]   ?? '');
                $msk_gaji = esc($conn, $_POST['msk_gaji'][$k]   ?? 'Tidak');
                $bea_a    = esc($conn, $_POST['bea_anak'][$k]   ?? 'Ya');
                $dinas_a  = esc($conn, $_POST['dinas_anak'][$k] ?? 'Ya');
                $kerja_a  = esc($conn, $_POST['kerja_anak'][$k] ?? 'Tidak');
                $ayah_a   = esc($conn, $_POST['ayah_anak'][$k]  ?? '');
                $ibu_a    = esc($conn, $_POST['ibu_anak'][$k]   ?? '');
                $ortu_a   = sql_val($_POST['ortu_anak'][$k]     ?? '');
                $ket_a    = esc($conn, $_POST['ket_anak'][$k]   ?? '');

                $q_anak = "INSERT INTO anak (
                                pegawai_id, nama_anak, status_anak,
                                tanggal_lahir, sekolah_kuliah_pada, masuk_daftar_gaji,
                                tidak_dapat_beasiswa, tidak_dapat_ikatan_dinas,
                                status_bekerja, nama_ayah, nama_ibu,
                                tgl_wafat_cerai_ortu, keterangan
                            ) VALUES (
                                $id, '$nama_a', '$sts_a',
                                $lhr_a, '$sek_a', '$msk_gaji',
                                '$bea_a', '$dinas_a',
                                '$kerja_a', '$ayah_a', '$ibu_a',
                                $ortu_a, '$ket_a'
                            )";

                if (!mysqli_query($conn, $q_anak)) {
                    throw new Exception("Gagal insert anak: " . mysqli_error($conn));
                }
            }
        }

        mysqli_commit($conn); // Semua berhasil, simpan ke database
        header("Location: index.php?status=sukses");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn); // Ada yang gagal, batalkan semua
        error_log($e->getMessage()); // Catat error di server log
        header("Location: form.php?id=" . ($id > 0 ? $id : '') . "&error=gagal_simpan");
        exit();
    }
}

// ==========================================
// 2. PROSES SIMPAN / UPDATE PEJABAT
// ==========================================
if (isset($_POST['simpan_pejabat'])) {
    $id_pejabat = (int)($_POST['id_pejabat'] ?? 0);
    $nama       = esc($conn, $_POST['nama_pejabat']    ?? '');
    $nip        = esc($conn, $_POST['nip_pejabat']     ?? '');
    $jabatan    = esc($conn, $_POST['jabatan_pejabat'] ?? '');

    if ($id_pejabat > 0) {
        $q_pejabat = "UPDATE pejabat SET
                        nama_pejabat = '$nama',
                        nip          = '$nip',
                        jabatan      = '$jabatan'
                      WHERE id = $id_pejabat";
    } else {
        $q_pejabat = "INSERT INTO pejabat (nama_pejabat, nip, jabatan)
                      VALUES ('$nama', '$nip', '$jabatan')";
    }

    if (mysqli_query($conn, $q_pejabat)) {
        header("Location: pejabat.php?status=sukses");
    } else {
        error_log("Gagal simpan pejabat: " . mysqli_error($conn));
        header("Location: pejabat.php?error=gagal_simpan");
    }
    exit();
}

// ==========================================
// 3. FITUR HAPUS DATA
// ==========================================

// Hapus Pegawai
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id_hapus = (int)($_GET['id'] ?? 0);

    if ($id_hapus > 0) {
        mysqli_begin_transaction($conn);
        try {
            if (!mysqli_query($conn, "DELETE FROM pasangan WHERE pegawai_id = $id_hapus")) {
                throw new Exception(mysqli_error($conn));
            }
            if (!mysqli_query($conn, "DELETE FROM anak WHERE pegawai_id = $id_hapus")) {
                throw new Exception(mysqli_error($conn));
            }
            if (!mysqli_query($conn, "DELETE FROM pegawai WHERE id = $id_hapus")) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_commit($conn);
            header("Location: index.php?status=terhapus");
        } catch (Exception $e) {
            mysqli_rollback($conn);
            error_log("Gagal hapus pegawai id $id_hapus: " . $e->getMessage());
            header("Location: index.php?error=gagal_hapus");
        }
    } else {
        header("Location: index.php");
    }
    exit();
}

// Hapus Pejabat
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus_pejabat') {
    $id_pejabat = (int)($_GET['id'] ?? 0);

    if ($id_pejabat > 0) {
        if (mysqli_query($conn, "DELETE FROM pejabat WHERE id = $id_pejabat")) {
            header("Location: pejabat.php?status=terhapus");
        } else {
            error_log("Gagal hapus pejabat id $id_pejabat: " . mysqli_error($conn));
            header("Location: pejabat.php?error=gagal_hapus");
        }
    } else {
        header("Location: pejabat.php");
    }
    exit();
}
?>
