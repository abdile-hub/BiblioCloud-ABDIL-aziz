<?php
// download.php
// Mengambil file e-book dari S3 lalu stream ke browser sebagai download.
// Catatan: pendekatan sederhana ini men-download file ke folder sementara di server
// dulu lewat Python, lalu PHP membacanya. Kalau nanti pakai AWS asli, bisa diganti
// generate presigned URL langsung dari s3_helper.py agar lebih efisien.

require_once __DIR__ . '/../src/config.php';

$key = $_GET['key'] ?? null;
if (!$key) {
    http_response_code(400);
    exit('Parameter key wajib diisi.');
}

$temp_local_path = sys_get_temp_dir() . '/' . basename($key);

// Panggil python untuk download object dari S3 ke temp_local_path
$cmd = escapeshellarg(PYTHON_PATH) . ' ' . escapeshellarg(__DIR__ . '/../python/download_helper.py')
     . ' ' . escapeshellarg($key) . ' ' . escapeshellarg($temp_local_path);
shell_exec($cmd . ' 2>&1');

if (!file_exists($temp_local_path)) {
    http_response_code(404);
    exit('File tidak ditemukan di S3.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($key) . '"');
header('Content-Length: ' . filesize($temp_local_path));
readfile($temp_local_path);
unlink($temp_local_path);
exit;
