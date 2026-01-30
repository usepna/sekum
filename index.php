<?php
include 'koneksi.php';

// --- LOGIKA SORTING & PENCARIAN ---
$keyword = $_GET['keyword'] ?? ""; // Pakai GET agar sort tidak hilang saat search
$sort_col = $_GET['sort'] ?? "id"; // Default sort by ID
$sort_ord = $_GET['order'] ?? "DESC"; // Default urutan DESC
$new_ord = ($sort_ord == "ASC") ? "DESC" : "ASC"; // Toggle untuk link

// Validasi kolom agar aman dari SQL Injection
$allowed_cols = ['nip', 'nama_lengkap', 'jabatan_struktural_fungsional', 'pangkat_golongan', 'tmt_golongan', 'tmt_jabatan'];
if (!in_array($sort_col, $allowed_cols)) $sort_col = "id";

$where = "";
if ($keyword) {
    $where = "WHERE nama_lengkap LIKE '%$keyword%' OR nip LIKE '%$keyword%'";
}

// Query Gabungan Search + Sort
$query = "SELECT * FROM pegawai $where ORDER BY $sort_col $sort_ord";
$result = mysqli_query($conn, $query);

// Helper Icon Sort
function get_sort_icon($col_name, $current_col, $current_ord) {
    if ($col_name != $current_col) return '<i class="fas fa-sort text-muted small"></i>';
    return ($current_ord == "ASC") ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard SKTK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .th-sort { cursor: pointer; text-decoration: none; color: white; display: flex; justify-content: space-between; align-items: center; }
        .th-sort:hover { color: #ccc; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid px-4 mt-5">
    
    <?php if (isset($_GET['pesan'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['pesan']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-file-invoice"></i> Dashboard Data SKTK</h4>
            <div>
                <a href="import_json.php" class="btn btn-dark btn-sm fw-bold me-2"><i class="fas fa-file-code"></i> JSON</a>
                <a href="import_excel.php" class="btn btn-warning btn-sm fw-bold me-2"><i class="fas fa-file-import"></i> CSV</a>
                <a href="pejabat.php" class="btn btn-success btn-sm fw-bold me-2"><i class="fas fa-user-tie"></i> Pejabat</a>
                <a href="form.php" class="btn btn-light btn-sm fw-bold"><i class="fas fa-plus"></i> Tambah</a>
            </div>
        </div>

        <div class="card-body">
            <form action="" method="GET" class="row g-3 mb-4">
                <input type="hidden" name="sort" value="<?= $sort_col ?>">
                <input type="hidden" name="order" value="<?= $sort_ord ?>">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="keyword" class="form-control" placeholder="Cari Nama / NIP..." value="<?= $keyword ?>">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-sync"></i> Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle table-sm" style="font-size: 0.9rem;">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th><a href="?sort=nip&order=<?=$new_ord?>&keyword=<?=$keyword?>" class="th-sort">NIP <?= get_sort_icon('nip', $sort_col, $sort_ord) ?></a></th>
                            <th><a href="?sort=nama_lengkap&order=<?=$new_ord?>&keyword=<?=$keyword?>" class="th-sort">Nama Lengkap <?= get_sort_icon('nama_lengkap', $sort_col, $sort_ord) ?></a></th>
                            
                            <th><a href="?sort=jabatan_struktural_fungsional&order=<?=$new_ord?>&keyword=<?=$keyword?>" class="th-sort">Jabatan <?= get_sort_icon('jabatan_struktural_fungsional', $sort_col, $sort_ord) ?></a></th>
                            <th><a href="?sort=tmt_jabatan&order=<?=$new_ord?>&keyword=<?=$keyword?>" class="th-sort">TMT Jab <?= get_sort_icon('tmt_jabatan', $sort_col, $sort_ord) ?></a></th>
                            
                            <th><a href="?sort=pangkat_golongan&order=<?=$new_ord?>&keyword=<?=$keyword?>" class="th-sort">Gol <?= get_sort_icon('pangkat_golongan', $sort_col, $sort_ord) ?></a></th>
                            <th><a href="?sort=tmt_golongan&order=<?=$new_ord?>&keyword=<?=$keyword?>" class="th-sort">TMT Gol <?= get_sort_icon('tmt_golongan', $sort_col, $sort_ord) ?></a></th>
                            
                            <th width="150" class="text-center">Aksi</th>
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
                            <td><?= tgl_indo($row['tmt_jabatan']) ?></td> <td><?= $row['pangkat_golongan'] ?></td>
                            <td><?= tgl_indo($row['tmt_golongan']) ?></td> <td class="text-center">
                                <a href="form.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm text-white" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="proses.php?aksi=hapus&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                <a href="cetak_sktk.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm" title="Cetak"><i class="fas fa-print"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="8" class="text-center">Data tidak ditemukan.</td></tr>
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