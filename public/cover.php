<?php
// cover.php
// Menampilkan gambar cover dari S3 sebagai <img src="cover.php?file=...">
// Kalau tidak ada cover, tampilkan placeholder default.

require_once __DIR__ . '/../src/config.php';

$file = $_GET['file'] ?? '';
$placeholder = __DIR__ . '/assets/img/placeholder-cover.png';

if ($file === '') {
    header('Content-Type: image/png');
    readfile($placeholder);
    exit;
}

$temp_local_path = sys_get_temp_dir() . '/' . basename($file);
$cmd = escapeshellarg(PYTHON_PATH) . ' ' . escapeshellarg(__DIR__ . '/../python/download_helper.py')
     . ' ' . escapeshellarg($file) . ' ' . escapeshellarg($temp_local_path);
shell_exec($cmd . ' 2>&1');

if (!file_exists($temp_local_path)) {
    header('Content-Type: image/png');
    readfile($placeholder);
    exit;
}

$mime = mime_content_type($temp_local_path);
header('Content-Type: ' . $mime);
readfile($temp_local_path);
unlink($temp_local_path);
exit;
