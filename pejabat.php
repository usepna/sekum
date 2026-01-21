<?php
include 'koneksi.php';

$edit = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pejabat WHERE id=$id"));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kelola Pejabat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><?= $edit ? 'Edit' : 'Tambah' ?> Pejabat</h5>
                </div>
                <div class="card-body">
                    <form action="proses.php" method="POST">
                        <input type="hidden" name="id_pejabat" value="<?= $edit['id'] ?? '' ?>">
                        <div class="mb-3">
                            <label>Nama Pejabat (Beserta Gelar)</label>
                            <input type="text" name="nama" class="form-control" value="<?= $edit['nama_pejabat'] ?? '' ?>" required placeholder="Contoh: USAN SUPRIATNA, SP.,MM">
                        </div>
                        <div class="mb-3">
                            <label>NIP</label>
                            <input type="text" name="nip" class="form-control" value="<?= $edit['nip'] ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Jabatan (di Surat)</label>
                            <input type="text" name="jabatan" class="form-control" value="<?= $edit['jabatan'] ?? 'Sekretaris' ?>">
                        </div>
                        <div class="mb-3">
                            <label>Instansi</label>
                            <input type="text" name="instansi" class="form-control" value="<?= $edit['instansi'] ?? 'Dinas Perumahan dan Kawasan Permukiman Kota Bandung' ?>">
                        </div>
                        <button type="submit" name="simpan_pejabat" class="btn btn-success w-100">Simpan Pejabat</button>
                        <?php if($edit): ?>
                            <a href="pejabat.php" class="btn btn-secondary w-100 mt-2">Batal Edit</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <div class="mt-3 text-center">
                <a href="index.php" class="btn btn-outline-primary">&laquo; Kembali ke Dashboard</a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Daftar Pejabat Penandatangan</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle"></i> Pejabat yang dipilih saat mencetak surat adalah yang datanya tersedia di sini.
                    </div>
                    <table class="table table-bordered table-hover">
                        <thead class="table-secondary">
                            <tr>
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Jabatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($conn, "SELECT * FROM pejabat ORDER BY id DESC");
                            while($r = mysqli_fetch_assoc($q)):
                            ?>
                            <tr>
                                <td><?= $r['nama_pejabat'] ?></td>
                                <td><?= $r['nip'] ?></td>
                                <td><?= $r['jabatan'] ?></td>
                                <td class="text-center">
                                    <a href="pejabat.php?edit=<?= $r['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="proses.php?aksi=hapus_pejabat&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pejabat ini?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>