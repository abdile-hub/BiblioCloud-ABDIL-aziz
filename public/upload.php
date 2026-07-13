<?php
// upload.php
require_once __DIR__ . '/../src/functions.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $genre = trim($_POST['genre'] ?? '');

    if ($title === '' || $author === '' || empty($_FILES['ebook_file']['tmp_name'])) {
        $error = 'Judul, penulis, dan file e-book wajib diisi.';
    } else {
        $temp_id = time();
        $ebook_ext = pathinfo($_FILES['ebook_file']['name'], PATHINFO_EXTENSION);
        $s3_key = "books/{$temp_id}/ebook.{$ebook_ext}";

        $cover_filename = null;
        if (!empty($_FILES['cover_file']['tmp_name'])) {
            $cover_ext = pathinfo($_FILES['cover_file']['name'], PATHINFO_EXTENSION);
            $cover_filename = "covers/{$temp_id}/cover.{$cover_ext}";
        }

        $upload_result = s3_upload_book($_FILES['ebook_file']['tmp_name'], $s3_key);

        if ($upload_result['status'] === 'success') {
            if ($cover_filename !== null) {
                s3_upload_book($_FILES['cover_file']['tmp_name'], $cover_filename);
            }
            save_book_metadata($title, $author, $genre, $s3_key, $cover_filename);
            $success = 'Buku berhasil diupload! Anda dapat melihatnya di katalog.';
        } else {
            $error = 'Gagal upload ke S3: ' . $upload_result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="luxury">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Buku — BiblioCloud</title>
    <!-- Tailwind CSS & DaisyUI via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #000; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
    </style>
</head>
<body class="min-h-screen bg-base-200">

    <div class="navbar bg-base-100 shadow-xl sticky top-0 z-50 px-4 lg:px-12">
        <div class="flex-1">
            <a href="index.php" class="btn btn-ghost text-xl font-bold gap-2 hover:bg-transparent">
                <span class="text-2xl">📚</span>
                <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">BiblioCloud</span>
            </a>
        </div>
        <div class="flex-none">
            <a href="index.php" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Kembali ke Katalog
            </a>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12 flex justify-center">
        <div class="card w-full max-w-2xl bg-base-100 shadow-2xl border border-base-300">
            <div class="card-body">
                <div class="text-center mb-6">
                    <div class="inline-block p-4 rounded-full bg-primary/20 text-primary mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                    </div>
                    <h2 class="card-title text-3xl justify-center">Upload Buku Baru</h2>
                    <p class="text-base-content/60">Tambahkan e-book Anda ke Cloud Storage</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error shadow-lg mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success shadow-lg mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <form action="upload.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                    
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-semibold">Judul Buku</span></label>
                        <input type="text" name="title" placeholder="Contoh: Laskar Pelangi" class="input input-bordered w-full focus:input-primary" required />
                    </div>

                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-semibold">Penulis</span></label>
                        <input type="text" name="author" placeholder="Nama penulis..." class="input input-bordered w-full focus:input-primary" required />
                    </div>

                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-semibold">Genre</span></label>
                        <select name="genre" class="select select-bordered w-full focus:select-primary">
                            <option value="Fiksi">Fiksi</option>
                            <option value="Non-Fiksi">Non-Fiksi</option>
                            <option value="Akademik">Akademik</option>
                            <option value="Komik">Komik</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="divider">File Upload</div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">File E-book (PDF / EPUB)</span>
                            <span class="label-text-alt text-error font-semibold">Wajib</span>
                        </label>
                        <input type="file" name="ebook_file" accept=".pdf,.epub" class="file-input file-input-bordered file-input-primary w-full" required />
                        <label class="label"><span class="label-text-alt text-base-content/60">Maksimal 50MB</span></label>
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Cover Buku (Gambar)</span>
                            <span class="label-text-alt font-semibold">Opsional</span>
                        </label>
                        <input type="file" name="cover_file" accept="image/*" class="file-input file-input-bordered file-input-secondary w-full" />
                    </div>

                    <div class="form-control mt-8 pt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow-xl shadow-primary/30 w-full">
                            Upload ke Cloud
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
