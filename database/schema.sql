-- schema.sql
-- Skema database metadata buku untuk BiblioCloud
-- Jalankan dengan: sqlite3 bibliocloud.sqlite < schema.sql

CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    author TEXT NOT NULL,
    genre TEXT,
    s3_key TEXT NOT NULL,        -- lokasi file e-book di bucket S3 (contoh: books/12/novel.pdf)
    cover_filename TEXT,          -- lokasi cover di bucket S3 (contoh: covers/12/cover.jpg)
    uploaded_at TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_books_title ON books(title);
CREATE INDEX IF NOT EXISTS idx_books_genre ON books(genre);
