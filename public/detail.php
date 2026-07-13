<?php
// detail.php
require_once __DIR__ . '/../src/functions.php';

$id = $_GET['id'] ?? null;
$book = $id ? get_book_metadata_by_id($id) : null;

if (!$book) {
    header('Location: index.php');
    exit;
}

$s3_detail = s3_detail_book($book['s3_key']);

function format_size($bytes) {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

function format_date($datestr) {
    $ts = strtotime($datestr);
    if (!$ts) return $datestr;
    return date('d M Y, H:i', $ts);
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="luxury">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book['title']) ?> — BiblioCloud</title>
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
                Kembali
            </a>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12 flex justify-center">
        <div class="card lg:card-side bg-base-100 shadow-2xl border border-base-300 w-full max-w-4xl overflow-hidden">
            <figure class="lg:w-2/5 bg-base-300 relative group aspect-[3/4] lg:aspect-auto">
                <img src="cover.php?file=<?= urlencode($book['cover_filename']) ?>" alt="Cover" class="object-cover w-full h-full lg:absolute lg:inset-0" />
            </figure>
            <div class="card-body lg:w-3/5 p-8">
                <h2 class="card-title text-4xl font-bold mb-2 leading-tight"><?= htmlspecialchars($book['title']) ?></h2>
                <div class="flex flex-wrap gap-2 mb-6 mt-2">
                    <div class="badge badge-primary badge-outline py-3 px-3">✍️ <?= htmlspecialchars($book['author']) ?></div>
                    <div class="badge badge-secondary badge-outline py-3 px-3">📂 <?= htmlspecialchars($book['genre']) ?></div>
                    <div class="badge badge-ghost py-3 px-3">📅 <?= format_date($book['uploaded_at']) ?></div>
                </div>

                <div class="divider text-base-content/50">Informasi Cloud Storage (S3)</div>

                <?php if ($s3_detail['status'] === 'success'): ?>
                    <div class="stats stats-vertical lg:stats-horizontal shadow-md bg-base-200 border border-base-300 w-full mb-6">
                        <div class="stat px-4 py-4">
                            <div class="stat-title text-xs">Ukuran File</div>
                            <div class="stat-value text-xl"><?= format_size($s3_detail['data']['size']) ?></div>
                        </div>
                        
                        <div class="stat px-4 py-4">
                            <div class="stat-title text-xs">Tipe File</div>
                            <div class="stat-value text-xl"><?= htmlspecialchars($s3_detail['data']['content_type']) ?></div>
                        </div>
                        
                        <div class="stat px-4 py-4 overflow-hidden">
                            <div class="stat-title text-xs">Modifikasi</div>
                            <div class="stat-value text-xl truncate"><?= date('d M Y', strtotime($s3_detail['data']['last_modified'])) ?></div>
                        </div>
                    </div>
                    
                    <!-- S3 Key Note -->
                    <div class="bg-base-300 rounded-lg p-3 text-xs font-mono text-base-content/70 break-all mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                        <?= htmlspecialchars($book['s3_key']) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-error mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>File tidak ditemukan di S3: <?= htmlspecialchars($s3_detail['message']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="card-actions justify-end mt-auto pt-4">
                    <a href="download.php?key=<?= urlencode($book['s3_key']) ?>" class="btn btn-primary shadow-lg shadow-primary/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Download
                    </a>
                    <a href="delete.php?id=<?= $book['id'] ?>" class="btn btn-error btn-outline" onclick="return confirm('⚠️ Yakin ingin menghapus buku ini? File S3 juga akan terhapus secara permanen.')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
