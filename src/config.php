<?php
define('PYTHON_PATH', 'python');
define('S3_HELPER_SCRIPT', __DIR__ . '/../python/s3_helper.py');

// Koneksi database metadata (SQLite biar simpel, tidak perlu setup server DB)
define('DB_PATH', __DIR__ . '/../database/bibliocloud.sqlite');

function get_db() {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}