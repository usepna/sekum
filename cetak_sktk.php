<?php
include 'koneksi.php';
$id = $_GET['id'] ?? 1;

// Logika Pilih Pejabat
$id_pejabat_pilih = $_GET['pejabat'] ?? null;
$q_pej = $id_pejabat_pilih ? "SELECT * FROM pejabat WHERE id = $id_pejabat_pilih" : "SELECT * FROM pejabat ORDER BY id DESC LIMIT 1";
$pej = mysqli_fetch_assoc(mysqli_query($conn, $q_pej));

// Ambil Data Utama
$peg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pegawai WHERE id = $id"));
$pasangan = [];
$q_pas = mysqli_query($conn, "SELECT * FROM pasangan WHERE pegawai_id = $id");
while($r = mysqli_fetch_assoc($q_pas)) $pasangan[] = $r;

// Ambil Data Anak (Pisah Tabel I & II)
$anak1 = []; $anak2 = [];
$q_anak = mysqli_query($conn, "SELECT * FROM anak WHERE pegawai_id = $id ORDER BY tanggal_lahir ASC");
while($r = mysqli_fetch_assoc($q_anak)) {
    if($r['masuk_daftar_gaji'] == 'Ya') $anak1[] = $r; else $anak2[] = $r;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SKTK - <?= $peg['nama_lengkap'] ?></title>
    <style>
        body { font-family: "Times New Roman", serif; font-size: 11pt; margin: 0; padding: 20px; background: #525659; }
        
        /* Setting Halaman Dasar (Portrait) */
        .page { 
            background: white; width: 215mm; min-height: 330mm; 
            margin: 0 auto 20px auto; padding: 10mm 15mm; 
            box-sizing: border-box; position: relative; 
        }

        /* Setting Halaman Landscape (Khusus Hal 2) */
        .page-landscape {
            background: white; 
            width: 330mm; /* Lebar F4 Landscape */
            min-height: 215mm; 
            margin: 0 auto 20px auto; 
            padding: 10mm 15mm;
            box-sizing: border-box; position: relative;
        }

        /* CSS Print Magic */
        @media print {
            body { background: none; padding: 0; margin: 0; }
            .no-print { display: none !important; }
            
            /* Halaman Portrait */
            .page { margin: 0; box-shadow: none; page-break-after: always; page: portrait-page; }
            
            /* Halaman Landscape */
            .page-landscape { 
                width: 100%; margin: 0; box-shadow: none; 
                page-break-before: always; /* Paksa ganti halaman */
                page: landscape-page;      /* Panggil settingan landscape */
            }

            @page { size: portrait; margin: 10mm; } /* Default Portrait */
            @page landscape-page { size: landscape; margin: 10mm; } /* Definisi Page Landscape */
        }

        /* Tabel & Typography */
        .text-center { text-align: center; } .text-right { text-align: right; } .text-justify { text-align: justify; }
        .bold { font-weight: bold; } .upper { text-transform: uppercase; } .underline { text-decoration: underline; }
        .bio-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .bio-table td { vertical-align: top; padding: 2px 0; }
        .grid-table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 10pt; }
        .grid-table th, .grid-table td { border: 1px solid black; padding: 4px; text-align: center; vertical-align: middle; }
        .point-row { display: flex; } .point-label { width: 25px; } .point-content { flex: 1; }
        .sig-table { width: 100%; margin-top: 30px; page-break-inside: avoid; }
        .sig-table td { text-align: center; vertical-align: top; border: none; }
        .controls { background: white; padding: 10px; margin-bottom: 20px; display: flex; gap: 15px; justify-content: center; }
    </style>
</head>
<body>

    <div class="controls no-print">
        <a href="index.php" style="text-decoration:none;">&laquo; Dashboard</a>
        <form action="" method="GET">
            <input type="hidden" name="pejabat" value="<?= $id_pejabat_pilih ?>">
            <b>Pegawai:</b>
            <select name="id" onchange="this.form.submit()">
                <?php 
                $q_list = mysqli_query($conn, "SELECT id, nama_lengkap FROM pegawai ORDER BY nama_lengkap ASC");
                while($l = mysqli_fetch_assoc($q_list)) {
                    $sel = ($l['id'] == $id) ? 'selected' : '';
                    echo "<option value='".$l['id']."' $sel>".$l['nama_lengkap']."</option>";
                }
                ?>
            </select>
        </form>
        <form action="" method="GET">
            <input type="hidden" name="id" value="<?= $id ?>">
            <b>Pejabat:</b>
            <select name="pejabat" onchange="this.form.submit()">
                <?php
                $qp = mysqli_query($conn, "SELECT * FROM pejabat");
                while($rp = mysqli_fetch_assoc($qp)){
                    $sel = ($rp['id'] == $pej['id']) ? 'selected' : '';
                    echo "<option value='{$rp['id']}' $sel>{$rp['nama_pejabat']}</option>";
                }
                ?>
            </select>
        </form>
        <button onclick="window.print()">??? Cetak PDF</button>
    </div>

    <div class="page">
        <div class="text-right bold">Daftar Gaji<br>No. <?= $peg['no_daftar_gaji'] ?></div>
        <div class="text-center" style="margin-bottom: 20px;">
            <div class="bold underline upper" style="font-size: 12pt;">SURAT KETERANGAN</div>
            <div class="bold upper" style="font-size: 11pt;">UNTUK MENDAPATKAN PEMBAYARAN TUNJANGAN KELUARGA</div>
        </div>

        <div style="margin-bottom:5px;">Saya yang bertanda tangan dibawah ini :</div>
        <table class="bio-table">
            <tr><td width="25">1.</td><td width="220">Nama lengkap</td><td width="15">:</td><td class="upper bold"><?= $peg['nama_lengkap'] ?></td></tr>
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

        <div style="margin-bottom:5px;">Menerangkan dengan sesungguhnya bahwa Saya :</div>
        <div class="point-row"><div class="point-label">a.</div><div class="point-content">di samping jabatan utama tersebut bekerja pula sebagai: <?= $peg['jabatan_sampingan'] ?><br>dengan mendapatkan penghasilan sebesar Rp. <?= format_rupiah($peg['penghasilan_sampingan']) ?> sebulan</div></div>
        <div class="point-row"><div class="point-label">b.</div><div class="point-content">mempunyai pensiun / pensiun janda Rp. <?= format_rupiah($peg['pensiun_janda_rp']) ?> sebulan</div></div>
        <div class="point-row"><div class="point-label">c.</div><div class="point-content">kawin sah dengan
            <table class="grid-table">
                <thead><tr><th rowspan="2" width="30">No</th><th rowspan="2">Nama isteri / suami</th><th colspan="2">Tanggal</th><th rowspan="2">Pekerjaan</th><th rowspan="2">Penghasilan</th><th rowspan="2">Ket</th></tr><tr><th>Lahir (Umur)</th><th>Nikah</th></tr></thead>
                <tbody>
                    <?php if(empty($pasangan)): ?><tr><td colspan="7">-</td></tr><?php else: $no=1; foreach($pasangan as $p): $umur = ($p['tanggal_lahir']!='0000-00-00'&&$p['tanggal_lahir']!=NULL)?date_diff(date_create($p['tanggal_lahir']), date_create('today'))->y:'-'; ?>
                    <tr><td><?= $no++ ?></td><td><?= $p['nama_pasangan'] ?></td><td><?= tgl_indo($p['tanggal_lahir']) ?> (<?= $umur ?> Thn)</td><td><?= tgl_indo($p['tanggal_perkawinan']) ?></td><td><?= $p['pekerjaan'] ?></td><td>Rp. <?= format_rupiah($p['penghasilan_sebulan']) ?></td><td><?= $p['keterangan'] ?></td></tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div></div>
        <div class="point-row"><div class="point-label">d.</div><div class="point-content">mempunyai anak-anak seperti dalam daftar di sebelah ini yaitu:<div style="margin-top:5px;">i. ANAK KANDUNG (ak), ANAK TIRI (at), dan ANAK ANGKAT (aa) yang masih menjadi tanggungan...</div><div style="margin-top:5px;">ii. ANAK KANDUNG (ak), ANAK TIRI (at), dan ANAK ANGKAT (aa) yang masih menjadi tanggungan tetapi tidak masuk...</div></div></div>
        <div class="point-row" style="margin-top:5px;"><div class="point-label">e.</div><div class="point-content">Jumlah anak seluruhnya : <?= $peg['jumlah_anak_seluruhnya'] ?> orang (yang menjadi tanggungan termasuk yang tidak masuk dalam daftar gaji).</div></div>

        <p class="text-justify" style="margin-top:15px; font-size:10pt;">Keterangan ini saya buat dengan sesungguhnya...</p>

        <table class="sig-table">
            <tr><td width="50%">Mengetahui,<br>a.n. Kepala Dinas Perumahan dan Kawasan Permukiman<br>Kota Bandung<br><?= $pej['jabatan'] ?></td><td width="50%">Bandung, <?= tgl_indo(date('Y-m-d')) ?><br>Yang menerangkan,</td></tr>
            <tr><td style="height:70px;"></td><td></td></tr>
            <tr><td><span class="bold underline upper"><?= $pej['nama_pejabat'] ?></span><br>NIP. <?= $pej['nip'] ?></td><td><span class="bold underline upper"><?= $peg['nama_lengkap'] ?></span><br>NIP. <?= $peg['nip'] ?></td></tr>
        </table>
    </div>

    <div class="page-landscape">
        <h3 class="text-center bold upper" style="margin-top:0;">DAFTAR ANAK-ANAK</h3>

        <table class="grid-table" style="font-size:9pt;">
            <thead>
                <tr><th colspan="12" class="text-justify bold" style="padding:6px; background:#eee;">I. ANAK KANDUNG (ak), ANAK TIRI (at), ANAK ANGKAT (aa) yang masih menjadi tanggungan, belum mempunyai penghasilan sendiri dan yang masuk dalam daftar gaji</th></tr>
                <tr>
                    <th rowspan="2" width="25">No</th><th rowspan="2">Nama</th><th rowspan="2">Status</th><th rowspan="2">Tgl Lahir</th><th rowspan="2">Belum Kawin</th><th rowspan="2">Sekolah</th>
                    <th colspan="2">Tidak mendapat:</th><th colspan="3">LAHIR DARI PERKAWINAN</th><th rowspan="2">KETERANGAN</th>
                </tr>
                <tr><th>Beasiswa</th><th>Ikatan Dinas</th><th>Nama Ayah</th><th>Nama Ibu</th><th>Tgl Wafat/Cerai</th></tr>
            </thead>
            <tbody>
                <?php if(empty($anak1)): ?><tr><td colspan="12" style="height:30px;">-</td></tr><?php else: $no=1; foreach($anak1 as $a): ?>
                <tr><td><?=$no++?></td><td><?=$a['nama_anak']?></td><td><?=$a['status_anak']?></td><td><?=tgl_indo($a['tanggal_lahir'])?></td><td><?=$a['status_belum_kawin']?></td><td><?=$a['sekolah_kuliah_pada']?></td><td><?=$a['tidak_dapat_beasiswa']=='Ya'?'-':'Dapat'?></td><td><?=$a['tidak_dapat_ikatan_dinas']=='Ya'?'-':'Ada'?></td><td><?=$a['nama_ayah']?></td><td><?=$a['nama_ibu']?></td><td><?=tgl_indo($a['tgl_wafat_cerai_ortu'])?></td><td><?=$a['keterangan']?></td></tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <table class="grid-table" style="font-size:9pt; margin-top:15px;">
            <thead>
                <tr><th colspan="10" class="text-justify bold" style="padding:6px; background:#eee;">II. ANAK KANDUNG (ak), ANAK TIRI (at), ANAK ANGKAT (aa) yang menjadi tanggungan tetapi tidak masuk dalam daftar gaji</th></tr>
                <tr>
                    <th rowspan="2" width="25">No</th><th rowspan="2">Nama</th><th rowspan="2">Status</th><th rowspan="2">Tgl Lahir</th><th rowspan="2">Belum Kawin</th><th rowspan="2">Sekolah</th>
                    <th colspan="2">Tidak mendapat:</th><th rowspan="2">Bekerja/Tidak</th><th rowspan="2">Keterangan</th>
                </tr>
                <tr><th>Beasiswa</th><th>Ikatan Dinas</th></tr>
            </thead>
            <tbody>
                <?php if(empty($anak2)): ?><tr><td colspan="10" style="height:30px;">-</td></tr><?php else: $no=1; foreach($anak2 as $a): ?>
                <tr><td><?=$no++?></td><td><?=$a['nama_anak']?></td><td><?=$a['status_anak']?></td><td><?=tgl_indo($a['tanggal_lahir'])?></td><td><?=$a['status_belum_kawin']?></td><td><?=$a['sekolah_kuliah_pada']?></td><td><?=$a['tidak_dapat_beasiswa']=='Ya'?'-':'Dapat'?></td><td><?=$a['tidak_dapat_ikatan_dinas']=='Ya'?'-':'Ada'?></td><td><?=$a['status_bekerja']?></td><td><?=$a['keterangan']?></td></tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        
        <div style="font-size: 9pt; margin-top: 10px;">
            Catatan:<br>
            1. Supaya dilampirkan salinan surat keputusan Pengadilan Negeri yang telah disahkan.<br>
            2. Supaya diisi jika anak dilahirkan dari istri/suami yang telah meninggal dunia atau diceraikan.
        </div>
    </div>
</body>
</html>