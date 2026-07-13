<?php
// index.php
require_once __DIR__ . '/../src/functions.php';

$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$books = $keyword !== '' ? search_books_metadata($keyword) : get_all_books_metadata();
$total_books = count($books);

$genres = [];
foreach ($books as $b) {
    $g = $b['genre'] ?: 'Lainnya';
    $genres[$g] = ($genres[$g] ?? 0) + 1;
}
$total_genres = count($genres);
?>
<!DOCTYPE html>
<html lang="id" data-theme="luxury">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioCloud — Perpustakaan Digital</title>
    <!-- Tailwind CSS & DaisyUI via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom scrollbar for a premium feel */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #000; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
</head>
<body class="min-h-screen bg-base-200 pb-12">

    <!-- Navbar -->
    <div class="navbar bg-base-100 shadow-xl sticky top-0 z-50 px-4 lg:px-12">
        <div class="flex-1">
            <a href="index.php" class="btn btn-ghost text-xl font-bold gap-2 hover:bg-transparent">
                <span class="text-2xl">📚</span>
                <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">BiblioCloud</span>
            </a>
        </div>
        <div class="flex-none gap-4">
            <form action="index.php" method="GET" class="form-control hidden sm:block relative">
                <input type="text" name="q" placeholder="Cari judul/penulis..." class="input input-bordered w-24 md:w-auto" value="<?= htmlspecialchars($keyword) ?>" />
            </form>
            <a href="upload.php" class="btn btn-primary shadow-lg shadow-primary/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Upload
            </a>
        </div>
    </div>

    <!-- Hero / Stats -->
    <div class="container mx-auto px-4 mt-8 lg:mt-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl lg:text-5xl font-bold mb-4">
                <?php if ($keyword !== ''): ?>
                    Hasil: <span class="text-primary">"<?= htmlspecialchars($keyword) ?>"</span>
                <?php else: ?>
                    Perpustakaan <span class="text-primary">Digital</span> Anda
                <?php endif; ?>
            </h1>
            <p class="text-base-content/70 max-w-xl mx-auto">
                Kelola e-book Anda menggunakan teknologi Cloud Storage S3 dan antarmuka open-source premium.
            </p>

            <?php if ($keyword === '' && $total_books > 0): ?>
            <div class="stats stats-vertical lg:stats-horizontal shadow mt-8 bg-base-100 border border-base-300">
                <div class="stat place-items-center">
                    <div class="stat-title">Total Koleksi</div>
                    <div class="stat-value text-primary"><?= $total_books ?></div>
                    <div class="stat-desc">Buku tersimpan</div>
                </div>
                <div class="stat place-items-center">
                    <div class="stat-title">Kategori</div>
                    <div class="stat-value text-secondary"><?= $total_genres ?></div>
                    <div class="stat-desc">Genre berbeda</div>
                </div>
                <div class="stat place-items-center">
                    <div class="stat-title">Storage</div>
                    <div class="stat-value">Amazon S3</div>
                    <div class="stat-desc">Object Storage</div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Catalog Grid -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">
                <?= $keyword !== '' ? 'Hasil Pencarian' : 'Semua Koleksi' ?>
            </h2>
            <div class="badge badge-outline p-3"><?= $total_books ?> Buku</div>
        </div>

        <?php if (empty($books)): ?>
            <div class="hero bg-base-100 rounded-box border border-base-300 py-16 shadow-xl">
                <div class="hero-content text-center">
                    <div class="max-w-md">
                        <div class="text-6xl mb-6">📖</div>
                        <h1 class="text-3xl font-bold">Koleksi Kosong</h1>
                        <p class="py-6 text-base-content/70">
                            <?= $keyword !== '' ? 'Buku tidak ditemukan. Coba kata kunci yang lain.' : 'Belum ada buku di perpustakaan Anda. Yuk mulai koleksimu sekarang!' ?>
                        </p>
                        <?php if ($keyword === ''): ?>
                            <a href="upload.php" class="btn btn-primary btn-lg shadow-xl shadow-primary/30">Upload Buku Pertama</a>
                        <?php else: ?>
                            <a href="index.php" class="btn btn-outline">Lihat Semua Buku</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <?php foreach ($books as $book): ?>
                    <div class="card bg-base-100 shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-base-300 group overflow-hidden">
                        <figure class="px-4 pt-4 relative aspect-[3/4]">
                            <img src="cover.php?file=<?= urlencode($book['cover_filename']) ?>" alt="Cover" class="rounded-xl object-cover w-full h-full" loading="lazy" />
                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center rounded-xl mx-4 mt-4 mb-0">
                                <a href="detail.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-sm rounded-full">Lihat Detail</a>
                            </div>
                        </figure>
                        <div class="card-body p-5">
                            <h2 class="card-title text-base line-clamp-2 leading-tight" title="<?= htmlspecialchars($book['title']) ?>">
                                <?= htmlspecialchars($book['title']) ?>
                            </h2>
                            <p class="text-sm text-base-content/70 line-clamp-1">✍️ <?= htmlspecialchars($book['author']) ?></p>
                            <div class="card-actions justify-start mt-2">
                                <div class="badge badge-secondary badge-outline text-xs"><?= htmlspecialchars($book['genre']) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
