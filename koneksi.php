<?php
// koneksi.php
$host = 'k0kc04o4gswwoggo0o04440s'; 
$user = 'mysql'; 
$pass = '60jlkD9DXehj1cWq7ncfMa6UdGh8Qgq5YxWKM5EmtDnEaqbDXUJYlg5lw5thLdfd'; 
$db   = 'default'; 

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) { die("Koneksi gagal: " . mysqli_connect_error()); }

// Helper fungsi format rupiah
function format_rupiah($angka){
    if($angka == 0) return "-";
    return number_format($angka, 0, ',', '.');
}

// Helper fungsi tanggal indo
function tgl_indo($tanggal){
    if(empty($tanggal) || $tanggal == '0000-00-00') return "-";
    $bulan = array (1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[ (int)$split[1] ] . ' ' . $split[0];
}
?>






