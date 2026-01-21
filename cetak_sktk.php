<?php
include 'koneksi.php';

// 1. Ambil ID Pegawai dari URL (Default: 1 jika tidak ada)
$id = $_GET['id'] ?? 1;

// 2. Query Data Pegawai
$q_peg = mysqli_query($conn, "SELECT * FROM pegawai WHERE id = $id");
$peg = mysqli_fetch_assoc($q_peg);

// 3. Logika Pilih Pejabat Penandatangan
// Jika ada pilihan di URL (?pejabat=X), pakai itu. Jika tidak, ambil yang terakhir diinput.
$id_pejabat_pilih = $_GET['pejabat'] ?? null;
if ($id_pejabat_pilih) {
    $q_pej = "SELECT * FROM pejabat WHERE id = $id_pejabat_pilih";
} else {
    $q_pej = "SELECT * FROM pejabat ORDER BY id DESC LIMIT 1"; 
}
$pej = mysqli_fetch_assoc(mysqli_query($conn, $q_pej));

// 4. Query Data Pasangan
$pasangan = [];
$q_pas = mysqli_query($conn, "SELECT * FROM pasangan WHERE pegawai_id = $id");
while($r = mysqli_fetch_assoc($q_pas)) $pasangan[] = $r;

// 5. Query Data Anak (Pisahkan Tabel I dan Tabel II)
$anak1 = []; // Masuk Gaji
$anak2 = []; // Tidak Masuk Gaji
$q_anak = mysqli_query($conn, "SELECT * FROM anak WHERE pegawai_id = $id ORDER BY tanggal_lahir ASC");
while($r = mysqli_fetch_assoc($q_anak)) {
    if($r['masuk_daftar_gaji'] == 'Ya') {
        $anak1[] = $r;
    } else {
        $anak2[] = $r;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SKTK - <?= $peg['nama_lengkap'] ?? 'Data Tidak Ditemukan' ?></title>
    <style>
        /* Reset & Basic Font */
        body { font-family: "Times New Roman", Times, serif; font-size: 11pt; margin: 0; padding: 20px; background: #525659; }
        
        /* Halaman Kertas (A4/F4) */
        .page { 
            background: white; width: 215mm; min-height: 330mm; 
            margin: 0 auto 20px auto; padding: 10mm 20mm; 
            box-sizing: border-box; position: relative; 
        }
        
        /* Helper Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        .bold { font-weight: bold; }
        .upper { text-transform: uppercase; }
        .underline { text-decoration: underline; }
        .mb-1 { margin-bottom: 5px; }
        
        /* Tabel Biodata (Tanpa Border) */
        .bio-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .bio-table td { vertical-align: top; padding: 2px 0; }
        .col-no { width: 25px; } .col-label { width: 230px; } .col-sep { width: 15px; }

        /* Tabel Grid (Dengan Border) */
        .grid-table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 10px; font-size: 10pt; }
        .grid-table th, .grid-table td { border: 1px solid black; padding: 4px; vertical-align: middle; text-align: center; }
        .grid-table th { font-weight: normal; } 

        /* List Layout (Poin a, b, c) */
        .point-row { display: flex; }
        .point-label { width: 25px; }
        .point-content { flex: 1; }

        /* Signature Table Layout (Agar Sejajar) */
        .sig-table { width: 100%; margin-top: 30px; page-break-inside: avoid; border-collapse: collapse; }
        .sig-table td { text-align: center; vertical-align: top; padding: 0; border: none; }
        .sig-gap { height: 80px; }

        /* Control Panel (Hidden saat Print) */
        .controls {
            background: white; padding: 10px; margin-bottom: 20px; border-radius: 5px;
            display: flex; gap: 15px; justify-content: center; align-items: center; font-family: Arial;
        }
        
        @media print {
            body { background: none; padding: 0; }
            .page { margin: 0; box-shadow: none; page-break-after: always; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="controls no-print">
        <a href="index.php" style="text-decoration: none; color: blue;">&laquo; Kembali</a>
        
        <form action="" method="GET" style="display:flex; align-items:center; gap:5px;">
            <input type="hidden" name="pejabat" value="<?= $id_pejabat_pilih ?>">
            <label><b>Pegawai:</b></label>
            <select name="id" onchange="this.form.submit()" style="padding:5px;">
                <?php 
                $q_list = mysqli_query($conn, "SELECT id, nama_lengkap FROM pegawai ORDER BY nama_lengkap ASC");
                while($l = mysqli_fetch_assoc($q_list)) {
                    $sel = ($l['id'] == $id) ? 'selected' : '';
                    echo "<option value='".$l['id']."' $sel>".$l['nama_lengkap']."</option>";
                }
                ?>
            </select>
        </form>

        <form action="" method="GET" style="display:flex; align-items:center; gap:5px;">
            <input type="hidden" name="id" value="<?= $id ?>">
            <label><b>Pejabat TTD:</b></label>
            <select name="pejabat" onchange="this.form.submit()" style="padding:5px;">
                <?php
                $qp = mysqli_query($conn, "SELECT * FROM pejabat");
                while($rp = mysqli_fetch_assoc($qp)){
                    $sel = ($rp['id'] == $pej['id']) ? 'selected' : '';
                    echo "<option value='{$rp['id']}' $sel>{$rp['nama_pejabat']}</option>";
                }
                ?>
            </select>
        </form>

        <button onclick="window.print()" style="padding: 6px 15px; background: #007bff; color: white; border: none; cursor: pointer; font-weight: bold;">??? Cetak PDF</button>
        <a href="form.php?id=<?= $id ?>" style="color: green; text-decoration:none;">?? Edit Data</a>
    </div>

    <div class="page">
        <div class="text-right bold" style="margin-bottom: 10px;">
            Daftar Gaji<br>No. <?= $peg['no_daftar_gaji'] ?? '.......' ?>
        </div>
        
        <div class="text-center" style="margin-bottom: 20px;">
            <div class="bold underline upper" style="font-size: 12pt;">SURAT KETERANGAN</div>
            <div class="bold upper" style="font-size: 11pt;">UNTUK MENDAPATKAN PEMBAYARAN TUNJANGAN KELUARGA</div>
        </div>

        <div class="mb-1">Saya yang bertanda tangan dibawah ini :</div>

        <table class="bio-table">
            <tr><td class="col-no">1.</td><td class="col-label">Nama lengkap</td><td class="col-sep">:</td><td class="upper bold"><?= $peg['nama_lengkap'] ?></td></tr>
            <tr><td>2.</td><td>Tempat / tanggal lahir</td><td>:</td><td><?= $peg['tempat_lahir'] ?>, <?= tgl_indo($peg['tanggal_lahir']) ?></td></tr>
            <tr><td>3.</td><td>Jenis kelamin</td><td>:</td><td><?= $peg['jenis_kelamin'] ?></td></tr>
            <tr><td>4.</td><td>Agama</td><td>:</td><td><?= $peg['agama'] ?></td></tr>
            <tr><td>5.</td><td>Kebangsaan</td><td>:</td><td><?= $peg['kebangsaan'] ?></td></tr>
            <tr><td>6.</td><td>Pangkat / Golongan</td><td>:</td><td><?= $peg['pangkat_golongan'] ?></td></tr>
            <tr><td>7.</td><td>Jabatan Struktural / Fungsional</td><td>:</td><td><?= $peg['jabatan_struktural_fungsional'] ?></td></tr>
            <tr><td>8.</td><td>Pada Instansi Dep / Lemb.</td><td>:</td><td><?= $peg['instansi_induk'] ?></td></tr>
            <tr><td>9.</td><td>Masa kerja golongan</td><td>:</td><td><?= $peg['masa_kerja_tahun'] ?> Thn <?= $peg['masa_kerja_bulan'] ?> Bln <?= $peg['keterangan_masa_kerja'] ?></td></tr>
            <tr><td>10.</td><td>Digaji menurut</td><td>:</td><td><?= $peg['peraturan_gaji'] ?> Dengan gaji pokok Rp. <?= format_rupiah($peg['gaji_pokok']) ?>,-</td></tr>
            <tr><td>11.</td><td>Alamat / tempat tinggal</td><td>:</td><td><?= $peg['alamat_lengkap'] ?></td></tr>
        </table>

        <div class="mb-1">Menerangkan dengan sesungguhnya bahwa Saya :</div>

        <div class="point-row">
            <div class="point-label">a.</div>
            <div class="point-content">
                di samping jabatan utama tersebut bekerja pula sebagai: <?= $peg['jabatan_sampingan'] ?><br>
                dengan mendapatkan penghasilan sebesar Rp. <?= format_rupiah($peg['penghasilan_sampingan']) ?> sebulan
            </div>
        </div>
        <div class="point-row">
            <div class="point-label">b.</div>
            <div class="point-content">
                mempunyai pensiun / pensiun janda Rp. <?= format_rupiah($peg['pensiun_janda_rp']) ?> sebulan
            </div>
        </div>
        <div class="point-row">
            <div class="point-label">c.</div>
            <div class="point-content">
                kawin sah dengan
                <table class="grid-table">
                    <thead>
                        <tr>
                            <th rowspan="2" width="30">No</th><th rowspan="2">Nama isteri / suami tanggungan</th>
                            <th colspan="2">Tanggal</th><th rowspan="2">Pekerjaan</th><th rowspan="2">Penghasilan sebulan</th><th rowspan="2">Keterangan</th>
                        </tr>
                        <tr><th>Kelahiran (Umur)</th><th>Perkawinan</th></tr>
                    </thead>
                    <tbody>
                        <?php if(empty($pasangan)): ?>
                            <tr><td colspan="7">-</td></tr>
                        <?php else: $no=1; foreach($pasangan as $p): 
                            $umur = ($p['tanggal_lahir'] != '0000-00-00' && $p['tanggal_lahir'] != NULL) ? date_diff(date_create($p['tanggal_lahir']), date_create('today'))->y : '-';
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $p['nama_pasangan'] ?></td>
                                <td><?= tgl_indo($p['tanggal_lahir']) ?> (<?= $umur ?> Thn)</td>
                                <td><?= tgl_indo($p['tanggal_perkawinan']) ?></td>
                                <td><?= $p['pekerjaan'] ?></td>
                                <td>Rp. <?= format_rupiah($p['penghasilan_sebulan']) ?></td>
                                <td><?= $p['keterangan'] ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="point-row">
            <div class="point-label">d.</div>
            <div class="point-content">
                mempunyai anak-anak seperti dalam daftar di sebelah ini yaitu:
                <div style="margin-top:5px;">i. ANAK KANDUNG (ak), ANAK TIRI (at), dan ANAK ANGKAT (aa) yang masih menjadi tanggungan, belum mempunyai pekerjaan sendiri dan masuk dalam daftar gaji</div>
                <div style="margin-top:5px;">ii. ANAK KANDUNG (ak), ANAK TIRI (at), dan ANAK ANGKAT (aa) yang masih menjadi tanggungan tetapi tidak masuk dalam daftar gaji</div>
            </div>
        </div>
        
        <div class="point-row" style="margin-top: 5px;">
            <div class="point-label">e.</div>
            <div class="point-content">
                Jumlah anak seluruhnya : <?= $peg['jumlah_anak_seluruhnya'] ?> orang (yang menjadi tanggungan termasuk yang tidak masuk dalam daftar gaji).
            </div>
        </div>

        <p class="text-justify" style="margin-top: 15px;">
            keterangan ini saya buat dengan sesungguhnya dan apabila keterangan ini ternyata tidak benar (palsu), saya bersedia dituntut di muka pengadilan berdasarkan Undang-undang yang berlaku, dan bersedia mengembalikan semua uang tunjangan anak yang telah saya terima yang seharusnya bukan menjadi hak saya.
        </p>

        <table class="sig-table">
            <tr>
                <td width="50%">
                    Mengetahui,<br>
                    a.n. Kepala Dinas Perumahan dan Kawasan Permukiman<br>
                    Kota Bandung<br>
                    <?= $pej['jabatan'] ?? 'Sekretaris' ?>
                </td>
                <td width="50%">
                    Bandung, <?= tgl_indo(date('Y-m-d')) ?><br>
                    Yang menerangkan,
                </td>
            </tr>
            <tr>
                <td class="sig-gap"></td>
                <td class="sig-gap"></td>
            </tr>
            <tr>
                <td>
                    <span class="bold underline upper"><?= $pej['nama_pejabat'] ?? '.........................' ?></span><br>
                    NIP. <?= $pej['nip'] ?? '.........................' ?>
                </td>
                <td>
                    <span class="bold underline upper"><?= $peg['nama_lengkap'] ?></span><br>
                    NIP. <?= $peg['nip'] ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="page">
        <h3 class="text-center bold upper" style="margin-top: 0; margin-bottom: 5px;">DAFTAR ANAK-ANAK</h3>

        <table class="grid-table" style="font-size: 9pt;">
            <thead>
                <tr>
                    <th colspan="12" class="text-justify bold" style="padding: 6px;">
                        I. ANAK KANDUNG (ak), ANAK TIRI (at), ANAK ANGKAT (aa) yang masih menjadi tanggungan, belum mempunyai penghasilan sendiri dan yang masuk dalam daftar gaji
                    </th>
                </tr>
                <tr>
                    <th rowspan="2" width="25">No Urut</th>
                    <th rowspan="2">Nama</th>
                    <th rowspan="2">Status Anak<br>(ak)/(at)/(aa)</th>
                    <th rowspan="2">Tanggal Lahir</th>
                    <th rowspan="2">Belum pernah kawin</th>
                    <th rowspan="2">Bersekolah/Kuliah pada</th>
                    <th colspan="2">Tidak mendapat:</th>
                    <th colspan="3">LAHIR DARI PERKAWINAN</th>
                    <th rowspan="2">KETERANGAN<br>1) diangkat menurut<br>1 putusan pengadilan negeri<br>2. hukuman adopsi bagi keturunan Tionghoa</th>
                </tr>
                <tr>
                    <th>1. Beasiswa/darma siswa</th>
                    <th>2. Ikatan dinas</th>
                    <th>Nama ayah</th>
                    <th>Nama ibu</th>
                    <th>Tanggal meninggalnya; diceraikannya ayah ibu 2)</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($anak1)): ?>
                    <tr><td colspan="12" style="height: 30px;">-</td></tr>
                <?php else: $no=1; foreach($anak1 as $a): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $a['nama_anak'] ?></td>
                        <td><?= $a['status_anak'] ?></td>
                        <td><?= tgl_indo($a['tanggal_lahir']) ?></td>
                        <td><?= $a['status_belum_kawin'] ?></td>
                        <td><?= $a['sekolah_kuliah_pada'] ?></td>
                        <td><?= $a['tidak_dapat_beasiswa']=='Ya' ? '-' : 'Dapat' ?></td>
                        <td><?= $a['tidak_dapat_ikatan_dinas']=='Ya' ? '-' : 'Ada' ?></td>
                        <td><?= $a['nama_ayah'] ?></td>
                        <td><?= $a['nama_ibu'] ?></td>
                        <td><?= tgl_indo($a['tgl_wafat_cerai_ortu']) ?></td>
                        <td><?= $a['keterangan'] ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <table class="grid-table" style="font-size: 9pt; margin-top: 20px;">
            <thead>
                <tr>
                    <th colspan="10" class="text-justify bold" style="padding: 6px;">
                        II. ANAK KANDUNG (ak), ANAK TIRI (at), ANAK ANGKAT (aa) yang menjadi tanggungan tetapi tidak masuk dalam daftar gaji
                    </th>
                </tr>
                <tr>
                    <th rowspan="2" width="25">No Urut</th>
                    <th rowspan="2">Nama</th>
                    <th rowspan="2">Status Anak<br>(ak)/(at)/(aa)</th>
                    <th rowspan="2">Tanggal Lahir</th>
                    <th rowspan="2">Belum pernah kawin</th>
                    <th rowspan="2">Bersekolah/Kuliah pada</th>
                    <th colspan="2">Tidak mendapat:</th>
                    <th rowspan="2">Bekerja atau tidak bekerja</th>
                    <th rowspan="2">keterangan</th>
                </tr>
                <tr>
                    <th>1. Beasiswa/darma siswa</th>
                    <th>2. Ikatan dinas</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($anak2)): ?>
                    <tr><td colspan="10" style="height: 30px;">-</td></tr>
                <?php else: $no=1; foreach($anak2 as $a): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $a['nama_anak'] ?></td>
                        <td><?= $a['status_anak'] ?></td>
                        <td><?= tgl_indo($a['tanggal_lahir']) ?></td>
                        <td><?= $a['status_belum_kawin'] ?></td>
                        <td><?= $a['sekolah_kuliah_pada'] ?></td>
                        <td><?= $a['tidak_dapat_beasiswa']=='Ya' ? '-' : 'Dapat' ?></td>
                        <td><?= $a['tidak_dapat_ikatan_dinas']=='Ya' ? '-' : 'Ada' ?></td>
                        <td><?= $a['status_bekerja'] ?></td>
                        <td><?= $a['keterangan'] ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <div style="font-size: 9pt; margin-top: 10px;">
            <table>
                <tr><td valign="top">1.</td><td>Supaya dilampirkan salinan surat keputusan Pengadilan Negeri yang telah disahkan</td></tr>
                <tr><td valign="top">2.</td><td>Supaya diisi jika anak dilahirkan dari istri/suami yang telah meninggal dunia atau diceraikan</td></tr>
            </table>
        </div>
    </div>

</body>
</html>