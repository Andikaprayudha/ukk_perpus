-- Membuat database
CREATE DATABASE IF NOT EXISTS perpustakaan_db;
USE perpustakaan_db;

-- Tabel pengguna
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telepon VARCHAR(20),
    alamat TEXT,
    role ENUM('admin', 'petugas', 'anggota') NOT NULL DEFAULT 'anggota',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel kategori buku
CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel buku
CREATE TABLE IF NOT EXISTS buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100) NOT NULL,
    tahun_terbit YEAR NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    kategori_id INT NOT NULL,
    jumlah_halaman INT,
    stok INT NOT NULL DEFAULT 0,
    gambar VARCHAR(255),
    file_path VARCHAR(255),
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
);

-- Tabel peminjaman
CREATE TABLE IF NOT EXISTS peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    buku_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    status ENUM('dipinjam', 'dikembalikan', 'terlambat') NOT NULL DEFAULT 'dipinjam',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
);

-- Tabel ulasan buku
CREATE TABLE IF NOT EXISTS ulasan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    buku_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    ulasan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
);

-- Tambahkan admin default
INSERT INTO users (nama, username, password, email, role) VALUES 
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@perpustakaan.com', 'admin');

-- Tambahkan beberapa kategori
INSERT INTO kategori (nama) VALUES 
('Fiksi'), 
('Non-Fiksi'), 
('Pendidikan'), 
('Teknologi'), 
('Sastra');

-- Tabel notifikasi
CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    peminjaman_id INT DEFAULT NULL,
    type ENUM('reminder', 'overdue', 'info') NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE SET NULL
);

-- Tambahkan beberapa buku
INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori_id, jumlah_halaman, stok, deskripsi) VALUES 
('Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, '9789793062792', 1, 529, 10, 'Novel tentang perjuangan anak-anak di Belitung untuk mendapatkan pendidikan'),
('Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, '9799731234', 5, 535, 5, 'Novel sejarah Indonesia pada masa kolonial Belanda'),
('Atomic Habits', 'James Clear', 'Penguin Random House', 2018, '9781847941831', 2, 320, 8, 'Buku tentang bagaimana membangun kebiasaan baik dan menghilangkan kebiasaan buruk');