<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Import Data JSON</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow col-md-7 mx-auto">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-file-code"></i> Import Data Pegawai (JSON)</h5>
        </div>
        <div class="card-body text-center">
            
            <p>Format JSON lebih aman dan akurat untuk data bertingkat (Pegawai + Istri + Anak).</p>
            
            <a href="template_pegawai.json" class="btn btn-warning btn-sm mb-4" download>
                <i class="fas fa-download"></i> Download Template JSON
            </a>

            <form action="proses_import_json.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3 text-start">
                    <label class="form-label fw-bold">Upload File JSON</label>
                    <input type="file" name="file_json" class="form-control" accept=".json" required>
                </div>
                <button type="submit" name="import" class="btn btn-primary w-100">
                    <i class="fas fa-upload"></i> Proses Import JSON
                </button>
            </form>
            
            <hr>
            <a href="index.php" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
        </div>
    </div>
</div>
</body>
</html>