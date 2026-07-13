<?php
// functions.php
// Kumpulan fungsi PHP untuk memanggil s3_helper.py dan mengelola metadata buku

require_once __DIR__ . '/config.php';

/**
 * Menjalankan s3_helper.py dengan argumen tertentu, lalu decode hasil JSON-nya.
 */
function call_s3_helper($args) {
    $cmd = escapeshellarg(PYTHON_PATH) . ' ' . escapeshellarg(S3_HELPER_SCRIPT);
    foreach ($args as $arg) {
        $cmd .= ' ' . escapeshellarg($arg);
    }
    $output = shell_exec($cmd . ' 2>&1');
    $result = json_decode($output, true);

    if ($result === null) {
        return ['status' => 'error', 'message' => 'Gagal menjalankan script Python: ' . $output];
    }
    return $result;
}

function s3_list_books() {
    return call_s3_helper(['list']);
}

function s3_upload_book($local_path, $s3_key) {
    return call_s3_helper(['upload', $local_path, $s3_key]);
}

function s3_delete_book($s3_key) {
    return call_s3_helper(['delete', $s3_key]);
}

function s3_detail_book($s3_key) {
    return call_s3_helper(['detail', $s3_key]);
}

/**
 * Simpan metadata buku (judul, penulis, genre, s3_key, cover) ke database lokal.
 */
function save_book_metadata($title, $author, $genre, $s3_key, $cover_filename) {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'INSERT INTO books (title, author, genre, s3_key, cover_filename, uploaded_at)
         VALUES (:title, :author, :genre, :s3_key, :cover, :uploaded_at)'
    );
    $stmt->execute([
        ':title' => $title,
        ':author' => $author,
        ':genre' => $genre,
        ':s3_key' => $s3_key,
        ':cover' => $cover_filename,
        ':uploaded_at' => date('Y-m-d H:i:s'),
    ]);
    return $pdo->lastInsertId();
}

function get_all_books_metadata() {
    $pdo = get_db();
    $stmt = $pdo->query('SELECT * FROM books ORDER BY uploaded_at DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_book_metadata_by_id($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function delete_book_metadata($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare('DELETE FROM books WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

function search_books_metadata($keyword) {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'SELECT * FROM books WHERE title LIKE :kw OR author LIKE :kw ORDER BY uploaded_at DESC'
    );
    $stmt->execute([':kw' => '%' . $keyword . '%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
