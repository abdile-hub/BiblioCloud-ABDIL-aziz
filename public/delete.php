<?php
// delete.php
// Menghapus buku: object di S3 (ebook + cover) sekaligus metadata di database lokal

require_once __DIR__ . '/../src/functions.php';

$id = $_GET['id'] ?? null;
$book = $id ? get_book_metadata_by_id($id) : null;

if ($book) {
    s3_delete_book($book['s3_key']);
    if (!empty($book['cover_filename'])) {
        s3_delete_book($book['cover_filename']);
    }
    delete_book_metadata($book['id']);
}

header('Location: index.php');
exit;
