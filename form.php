<?php
include 'koneksi.php';
$id = $_GET['id'] ?? null;
$p = null;
$pasangan = [];
$anak1 = []; // Anak Masuk Gaji
$anak2 = []; // Anak Tidak Masuk Gaji

if ($id) {
    $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pegawai WHERE id = $id"));
    
    $q_pas = mysqli_query($conn, "SELECT * FROM pasangan WHERE pegawai_id = $id");
    while($r = mysqli_fetch_assoc($q_pas)) $pasangan[] = $r;

    $q_anak = mysqli_query($conn, "SELECT * FROM anak WHERE pegawai_id = $id");
    while($r = mysqli_fetch_assoc($q_anak)) {
        if($r['masuk_daftar_gaji'] == 'Ya') {
            $anak1[] = $r;
        } else {
            $anak2[] = $r;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Form Pegawai SKTK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .table-input td { padding: 3px; vertical-align: middle; }
        .form-select-xs, .form-control-xs { padding: 0.2rem 0.4rem; font-size: 0.8rem; }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid mt-4 pb-5 px-4">
    <form action="proses.php" method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h5 class="mb-0"><?= $id ? 'Edit' : 'Tambah' ?> Data Pegawai</h5>
                <a href="index.php" class="btn btn-light btn-sm">Kembali</a>
            </div>
            <div class="card-body">

                <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#bio" type="button">1. Biodata</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pas" type="button">2. Pasangan</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#anak" type="button">3. Data Anak (Lengkap)</button></li>
                </ul>

                <div class="tab-content">
                    
                    <div class="tab-pane fade show active" id="bio">
                        <div class="row g-3">
                            <div class="col-md-3"><label>No. Daftar Gaji</label><input type="text" name="no_daftar_gaji" class="form-control" value="<?= $p['no_daftar_gaji']??'' ?>"></div>
                            <div class="col-md-5"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" class="form-control" value="<?= $p['nama_lengkap']??'' ?>" required></div>
                            <div class="col-md-4"><label>NIP</label><input type="text" name="nip" class="form-control" value="<?= $p['nip']??'' ?>" required></div>
                            
                            <div class="col-md-6"><label>Tempat Lahir</label><input type="text" name="tempat_lahir" class="form-control" value="<?= $p['tempat_lahir']??'' ?>"></div>
                            <div class="col-md-6"><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir" class="form-control" value="<?= $p['tanggal_lahir']??'' ?>"></div>
                            <div class="col-md-4"><label>Jenis Kelamin</label><select name="jenis_kelamin" class="form-select"><option value="Laki-laki" <?= ($p['jenis_kelamin']??'')=='Laki-laki'?'selected':'' ?>>Laki-laki</option><option value="Perempuan" <?= ($p['jenis_kelamin']??'')=='Perempuan'?'selected':'' ?>>Perempuan</option></select></div>
                            <div class="col-md-4">
							    <label class="form-label">Agama</label>
							    <select name="agama" class="form-select">
							        <option value="">-- Pilih Agama --</option>
							        <?php 
							        $list_agama = ['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'];
							        foreach ($list_agama as $ag) {
							            // Cek jika data database sama dengan opsi, maka tambahkan attribute 'selected'
							            $selected = ($p['agama'] ?? '') == $ag ? 'selected' : '';
							            echo "<option value='$ag' $selected>$ag</option>";
							        }
							        ?>
							    </select>
							</div>
                            <div class="col-md-4"><label>Kebangsaan</label><input type="text" name="kebangsaan" class="form-control" value="<?= $p['kebangsaan']??'Indonesia' ?>"></div>
                            
                            <div class="col-12"><label>Instansi</label><input type="text" name="instansi_induk" class="form-control" value="<?= $p['instansi_induk']??'' ?>"></div>
                            
                            <div class="col-md-3"><label>MK Tahun</label><input type="number" name="masa_kerja_tahun" class="form-control" value="<?= $p['masa_kerja_tahun']??0 ?>"></div>
                            <div class="col-md-3"><label>MK Bulan</label><input type="number" name="masa_kerja_bulan" class="form-control" value="<?= $p['masa_kerja_bulan']??0 ?>"></div>
                            <div class="col-md-6"><label>Ket MK</label><input type="text" name="keterangan_masa_kerja" class="form-control" value="<?= $p['keterangan_masa_kerja']??'' ?>"></div>
                            
                            <div class="col-md-6"><label>Peraturan Gaji</label><input type="text" name="peraturan_gaji" class="form-control" value="<?= $p['peraturan_gaji']??'PP Nomor 5 Tahun 2024' ?>"></div>
                            <div class="col-md-6"><label>Gaji Pokok</label><input type="number" name="gaji_pokok" class="form-control" value="<?= $p['gaji_pokok']??0 ?>"></div>
                            <div class="col-12"><label>Alamat</label><textarea name="alamat_lengkap" class="form-control"><?= $p['alamat_lengkap']??'' ?></textarea></div>
                            
                            <div class="col-md-6"><label>Jabatan Sampingan</label><input type="text" name="jabatan_sampingan" class="form-control" value="<?= $p['jabatan_sampingan']??'-' ?>"></div>
                            <div class="col-md-6"><label>Penghasilan Sampingan</label><input type="number" name="penghasilan_sampingan" class="form-control" value="<?= $p['penghasilan_sampingan']??0 ?>"></div>
                            <div class="col-md-6"><label>Pensiun Janda</label><input type="number" name="pensiun_janda_rp" class="form-control" value="<?= $p['pensiun_janda_rp']??0 ?>"></div>
                            <div class="col-md-6"><label>Jml Anak (Poin e)</label><input type="number" name="jumlah_anak_seluruhnya" class="form-control" value="<?= $p['jumlah_anak_seluruhnya']??0 ?>"></div>
                        </div>
						<div class="row">
							<div class="col-md-4 mb-3">
								<label class="form-label">Pangkat / Golongan</label>
								<input type="text" name="pangkat_golongan" class="form-control" value="<?= $p['pangkat_golongan']??'' ?>">
							</div>
							<div class="col-md-2 mb-3">
								<label class="form-label text-primary">TMT Golongan</label>
								<input type="date" name="tmt_golongan" class="form-control" value="<?= $p['tmt_golongan']??'' ?>">
							</div>
							
							<div class="col-md-4 mb-3">
								<label class="form-label">Jabatan</label>
								<input type="text" name="jabatan_struktural_fungsional" class="form-control" value="<?= $p['jabatan_struktural_fungsional']??'' ?>">
							</div>
							<div class="col-md-2 mb-3">
								<label class="form-label text-primary">TMT Jabatan</label>
								<input type="date" name="tmt_jabatan" class="form-control" value="<?= $p['tmt_jabatan']??'' ?>">
							</div>
						</div>
                    </div>

                    <div class="tab-pane fade" id="pas">
                         <table class="table table-bordered table-input mt-3" id="tblPas">
                            <thead class="table-light"><tr><th>Nama</th><th>Tgl Lahir</th><th>Tgl Nikah</th><th>Pekerjaan</th><th>Penghasilan</th><th>Ket</th><th><button type="button" class="btn btn-sm btn-success" onclick="addPas()">+</button></th></tr></thead>
                            <tbody>
                                <?php if($pasangan): foreach($pasangan as $ps): ?>
                                <tr>
                                    <td><input type="text" name="nm_pas[]" class="form-control form-control-xs" value="<?= $ps['nama_pasangan'] ?>"></td>
                                    <td><input type="date" name="lhr_pas[]" class="form-control form-control-xs" value="<?= $ps['tanggal_lahir'] ?>"></td>
                                    <td><input type="date" name="nik_pas[]" class="form-control form-control-xs" value="<?= $ps['tanggal_perkawinan'] ?>"></td>
                                    <td><input type="text" name="job_pas[]" class="form-control form-control-xs" value="<?= $ps['pekerjaan'] ?>"></td>
                                    <td><input type="number" name="inc_pas[]" class="form-control form-control-xs" value="<?= $ps['penghasilan_sebulan'] ?>"></td>
                                    <td><input type="text" name="ket_pas[]" class="form-control form-control-xs" value="<?= $ps['keterangan'] ?>"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">x</button></td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="anak">
                        
                        <div class="card mt-3 border-success">
                            <div class="card-header bg-success text-white py-1 d-flex justify-content-between">
                                <small class="fw-bold">I. Anak Yang Masuk Daftar Gaji (Tabel I)</small>
                                <small>Ket: Pilih 'Ya' jika TIDAK mendapat (Sesuai kolom di PDF)</small>
                            </div>
                            <div class="card-body p-2 table-responsive">
                                <table class="table table-bordered table-input mb-0" id="tblAnak1" style="min-width: 1300px;">
                                    <thead class="table-light text-center small">
                                        <tr>
                                            <th rowspan="2" width="150">Nama</th>
                                            <th rowspan="2" width="60">Sts</th>
                                            <th rowspan="2" width="100">Tgl Lahir</th>
                                            <th rowspan="2" width="120">Sekolah</th>
                                            <th colspan="2">Tidak Mendapat:</th> <th rowspan="2" width="100">Nama Ayah</th>
                                            <th rowspan="2" width="100">Nama Ibu</th>
                                            <th rowspan="2" width="100">Tgl Wafat/Cerai</th>
                                            <th rowspan="2">Ket</th>
                                            <th rowspan="2" width="40"><button type="button" class="btn btn-sm btn-success" onclick="addAnak1()">+</button></th>
                                        </tr>
                                        <tr>
                                            <th width="80">Beasiswa</th>
                                            <th width="80">Ikatan Dinas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($anak1): foreach($anak1 as $a): ?>
                                        <tr>
                                            <input type="hidden" name="msk_gaji[]" value="Ya">
                                            <input type="hidden" name="kerja_anak[]" value="Tidak">
                                            
                                            <td><input type="text" name="nm_anak[]" class="form-control form-control-xs" value="<?= $a['nama_anak'] ?>"></td>
                                            <td><select name="st_anak[]" class="form-select form-select-xs"><option value="ak" <?=$a['status_anak']=='ak'?'selected':''?>>ak</option><option value="at" <?=$a['status_anak']=='at'?'selected':''?>>at</option><option value="aa" <?=$a['status_anak']=='aa'?'selected':''?>>aa</option></select></td>
                                            <td><input type="date" name="lhr_anak[]" class="form-control form-control-xs" value="<?= $a['tanggal_lahir'] ?>"></td>
                                            <td><input type="text" name="sek_anak[]" class="form-control form-control-xs" value="<?= $a['sekolah_kuliah_pada'] ?>"></td>
                                            
                                            <td>
                                                <select name="bea_anak[]" class="form-select form-select-xs">
                                                    <option value="Ya" <?= ($a['tidak_dapat_beasiswa']??'Ya')=='Ya'?'selected':'' ?>>Ya (Tdk Dapat)</option>
                                                    <option value="Tidak" <?= ($a['tidak_dapat_beasiswa']??'Ya')=='Tidak'?'selected':'' ?>>Tidak (Dapat)</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="dinas_anak[]" class="form-select form-select-xs">
                                                    <option value="Ya" <?= ($a['tidak_dapat_ikatan_dinas']??'Ya')=='Ya'?'selected':'' ?>>Ya (Tdk Dapat)</option>
                                                    <option value="Tidak" <?= ($a['tidak_dapat_ikatan_dinas']??'Ya')=='Tidak'?'selected':'' ?>>Tidak (Dapat)</option>
                                                </select>
                                            </td>

                                            <td><input type="text" name="ayah_anak[]" class="form-control form-control-xs" value="<?= $a['nama_ayah'] ?>"></td>
                                            <td><input type="text" name="ibu_anak[]" class="form-control form-control-xs" value="<?= $a['nama_ibu'] ?>"></td>
                                            <td><input type="date" name="ortu_anak[]" class="form-control form-control-xs" value="<?= $a['tgl_wafat_cerai_ortu'] ?>"></td>
                                            <td><input type="text" name="ket_anak[]" class="form-control form-control-xs" value="<?= $a['keterangan'] ?>"></td>
                                            <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">x</button></td>
                                        </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card mt-4 border-warning">
                            <div class="card-header bg-warning text-dark py-1">
                                <small class="fw-bold">II. Anak Yang TIDAK Masuk Daftar Gaji (Tabel II)</small>
                            </div>
                            <div class="card-body p-2 table-responsive">
                                <table class="table table-bordered table-input mb-0" id="tblAnak2">
                                    <thead class="table-light text-center small">
                                        <tr>
                                            <th rowspan="2">Nama</th>
                                            <th rowspan="2" width="60">Sts</th>
                                            <th rowspan="2" width="100">Tgl Lahir</th>
                                            <th rowspan="2">Sekolah</th>
                                            <th colspan="2">Tidak Mendapat:</th> <th rowspan="2">Bekerja/Tidak?</th>
                                            <th rowspan="2">Ket</th>
                                            <th rowspan="2" width="40"><button type="button" class="btn btn-sm btn-success" onclick="addAnak2()">+</button></th>
                                        </tr>
                                        <tr>
                                            <th width="80">Beasiswa</th>
                                            <th width="80">Ikatan Dinas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($anak2): foreach($anak2 as $a): ?>
                                        <tr>
                                            <input type="hidden" name="msk_gaji[]" value="Tidak">
                                            <input type="hidden" name="ayah_anak[]" value="">
                                            <input type="hidden" name="ibu_anak[]" value="">
                                            <input type="hidden" name="ortu_anak[]" value="">

                                            <td><input type="text" name="nm_anak[]" class="form-control form-control-xs" value="<?= $a['nama_anak'] ?>"></td>
                                            <td><select name="st_anak[]" class="form-select form-select-xs"><option value="ak" <?=$a['status_anak']=='ak'?'selected':''?>>ak</option><option value="at" <?=$a['status_anak']=='at'?'selected':''?>>at</option><option value="aa" <?=$a['status_anak']=='aa'?'selected':''?>>aa</option></select></td>
                                            <td><input type="date" name="lhr_anak[]" class="form-control form-control-xs" value="<?= $a['tanggal_lahir'] ?>"></td>
                                            <td><input type="text" name="sek_anak[]" class="form-control form-control-xs" value="<?= $a['sekolah_kuliah_pada'] ?>"></td>
                                            
                                            <td>
                                                <select name="bea_anak[]" class="form-select form-select-xs">
                                                    <option value="Ya" <?= ($a['tidak_dapat_beasiswa']??'Ya')=='Ya'?'selected':'' ?>>Ya</option>
                                                    <option value="Tidak" <?= ($a['tidak_dapat_beasiswa']??'Ya')=='Tidak'?'selected':'' ?>>Tidak</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="dinas_anak[]" class="form-select form-select-xs">
                                                    <option value="Ya" <?= ($a['tidak_dapat_ikatan_dinas']??'Ya')=='Ya'?'selected':'' ?>>Ya</option>
                                                    <option value="Tidak" <?= ($a['tidak_dapat_ikatan_dinas']??'Ya')=='Tidak'?'selected':'' ?>>Tidak</option>
                                                </select>
                                            </td>

                                            <td><select name="kerja_anak[]" class="form-select form-select-xs"><option value="Tidak" <?= $a['status_bekerja']=='Tidak'?'selected':'' ?>>Tidak</option><option value="Bekerja" <?= $a['status_bekerja']=='Bekerja'?'selected':'' ?>>Bekerja</option></select></td>
                                            <td><input type="text" name="ket_anak[]" class="form-control form-control-xs" value="<?= $a['keterangan'] ?>"></td>
                                            <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">x</button></td>
                                        </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
                
                <div class="card-footer text-end mt-3">
                    <button type="submit" name="simpan" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Simpan Semua Data</button>
                </div>

            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function addPas(){
    let row = `<tr><td><input type="text" name="nm_pas[]" class="form-control form-control-xs"></td><td><input type="date" name="lhr_pas[]" class="form-control form-control-xs"></td><td><input type="date" name="nik_pas[]" class="form-control form-control-xs"></td><td><input type="text" name="job_pas[]" class="form-control form-control-xs" value="Ibu Rumah Tangga"></td><td><input type="number" name="inc_pas[]" class="form-control form-control-xs" value="0"></td><td><input type="text" name="ket_pas[]" class="form-control form-control-xs" value="Istri"></td><td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">x</button></td></tr>`;
    document.querySelector('#tblPas tbody').insertAdjacentHTML('beforeend', row);
}

function addAnak1(){
    let row = `<tr>
        <input type="hidden" name="msk_gaji[]" value="Ya">
        <input type="hidden" name="kerja_anak[]" value="Tidak">
        <td><input type="text" name="nm_anak[]" class="form-control form-control-xs"></td>
        <td><select name="st_anak[]" class="form-select form-select-xs"><option value="ak">ak</option><option value="at">at</option><option value="aa">aa</option></select></td>
        <td><input type="date" name="lhr_anak[]" class="form-control form-control-xs"></td>
        <td><input type="text" name="sek_anak[]" class="form-control form-control-xs" value="Belum Sekolah"></td>
        
        <td><select name="bea_anak[]" class="form-select form-select-xs"><option value="Ya">Ya</option><option value="Tidak">Tidak</option></select></td>
        <td><select name="dinas_anak[]" class="form-select form-select-xs"><option value="Ya">Ya</option><option value="Tidak">Tidak</option></select></td>
        
        <td><input type="text" name="ayah_anak[]" class="form-control form-control-xs"></td>
        <td><input type="text" name="ibu_anak[]" class="form-control form-control-xs"></td>
        <td><input type="date" name="ortu_anak[]" class="form-control form-control-xs"></td>
        <td><input type="text" name="ket_anak[]" class="form-control form-control-xs"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">x</button></td>
    </tr>`;
    document.querySelector('#tblAnak1 tbody').insertAdjacentHTML('beforeend', row);
}

function addAnak2(){
    let row = `<tr>
        <input type="hidden" name="msk_gaji[]" value="Tidak">
        <input type="hidden" name="ayah_anak[]" value="">
        <input type="hidden" name="ibu_anak[]" value="">
        <input type="hidden" name="ortu_anak[]" value="">
        <td><input type="text" name="nm_anak[]" class="form-control form-control-xs"></td>
        <td><select name="st_anak[]" class="form-select form-select-xs"><option value="ak">ak</option><option value="at">at</option><option value="aa">aa</option></select></td>
        <td><input type="date" name="lhr_anak[]" class="form-control form-control-xs"></td>
        <td><input type="text" name="sek_anak[]" class="form-control form-control-xs" value="-"></td>
        
        <td><select name="bea_anak[]" class="form-select form-select-xs"><option value="Ya">Ya</option><option value="Tidak">Tidak</option></select></td>
        <td><select name="dinas_anak[]" class="form-select form-select-xs"><option value="Ya">Ya</option><option value="Tidak">Tidak</option></select></td>
        
        <td><select name="kerja_anak[]" class="form-select form-select-xs"><option value="Tidak">Tidak</option><option value="Bekerja">Bekerja</option></select></td>
        <td><input type="text" name="ket_anak[]" class="form-control form-control-xs"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">x</button></td>
    </tr>`;
    document.querySelector('#tblAnak2 tbody').insertAdjacentHTML('beforeend', row);
}
</script>
</body>

</html>
