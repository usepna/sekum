<?php
include 'koneksi.php';

// Logika Pencarian
$keyword = "";
$where = "";
if (isset($_POST['cari'])) {
    $keyword = $_POST['keyword'];
    $where = "WHERE nama_lengkap LIKE '%$keyword%' OR nip LIKE '%$keyword%'";
}

$query = "SELECT * FROM pegawai $where ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard SKTK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    
    <?php if (isset($_GET['pesan'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['pesan']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-file-invoice"></i> Dashboard Data SKTK</h4>
            <div>
                <a href="pejabat.php" class="btn btn-success btn-sm fw-bold me-2">
                    <i class="fas fa-user-tie"></i> Tambah/Atur Pejabat
                </a>
                
                <a href="form.php" class="btn btn-light btn-sm fw-bold">
                    <i class="fas fa-plus"></i> Tambah Pegawai
                </a>
            </div>
        </div>
        <div class="card-body">
            
            <form action="" method="POST" class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="keyword" class="form-control" placeholder="Cari Nama / NIP..." value="<?= $keyword ?>">
                        <button class="btn btn-primary" type="submit" name="cari"><i class="fas fa-search"></i> Cari</button>
                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-sync"></i> Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama Lengkap</th>
                            <th>Jabatan</th>
                            <th>Golongan</th>
                            <th width="200" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        if (mysqli_num_rows($result) > 0):
                            while($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['nip'] ?></td>
                            <td class="fw-bold"><?= $row['nama_lengkap'] ?></td>
                            <td><?= $row['jabatan_struktural_fungsional'] ?></td> 
                            <td><?= $row['pangkat_golongan'] ?></td>
                            <td class="text-center">
                                <a href="form.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm text-white" title="Edit"><i class="fas fa-edit"></i></a>
                                
                                <a href="proses.php?aksi=hapus&id=<?= $row['id'] ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus data pegawai ini beserta seluruh data anak & pasangannya?');" 
                                   title="Hapus">
                                   <i class="fas fa-trash"></i>
                                </a>
                                
                                <a href="cetak_sktk.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm" title="Cetak"><i class="fas fa-print"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data pegawai.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>