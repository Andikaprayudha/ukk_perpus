-- Menambahkan kolom file_buku ke tabel buku
ALTER TABLE buku ADD COLUMN file_buku VARCHAR(255) NULL COMMENT 'Path file buku yang diunggah';