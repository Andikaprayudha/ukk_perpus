<?php
/**
 * SQLite Adapter untuk menggantikan mysqli
 * File ini menyediakan fungsi-fungsi yang kompatibel dengan mysqli
 * untuk digunakan sebagai alternatif ketika mysqli tidak tersedia
 */

// Cek apakah SQLite tersedia
if (!class_exists('SQLite3')) {
    die("Error: SQLite3 tidak tersedia di PHP Anda. Silakan install XAMPP/WAMP untuk menjalankan aplikasi ini.");
}

// Lokasi database SQLite
define('SQLITE_DB_PATH', __DIR__ . '/../db/perpustakaan.db');

// Kelas untuk mengemulasi mysqli
class MySQLiEmulator {
    private $db;
    private $last_error = '';
    private $last_query = '';
    private $affected_rows = 0;
    private $insert_id = 0;
    
    public function __construct($host, $user, $pass, $dbname) {
        try {
            // Buat database SQLite jika belum ada
            $this->db = new SQLite3(SQLITE_DB_PATH);
            $this->db->exec('PRAGMA foreign_keys = ON;');
            
            // Cek apakah tabel sudah ada, jika belum maka import skema
            $result = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='user'");
            if (!$result->fetchArray()) {
                $this->importSchema();
            }
        } catch (Exception $e) {
            $this->last_error = $e->getMessage();
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    
    // Import skema database dari MySQL ke SQLite
    private function importSchema() {
        // Buat tabel user
        $this->db->exec("CREATE TABLE IF NOT EXISTS user (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            alamat TEXT,
            telepon TEXT,
            level TEXT DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Buat tabel kategori
        $this->db->exec("CREATE TABLE IF NOT EXISTS kategori (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Buat tabel buku
        $this->db->exec("CREATE TABLE IF NOT EXISTS buku (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            judul TEXT NOT NULL,
            penulis TEXT NOT NULL,
            penerbit TEXT NOT NULL,
            tahun_terbit INTEGER,
            isbn TEXT,
            kategori_id INTEGER,
            stok INTEGER DEFAULT 0,
            gambar TEXT,
            deskripsi TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (kategori_id) REFERENCES kategori(id)
        )");
        
        // Buat tabel peminjaman
        $this->db->exec("CREATE TABLE IF NOT EXISTS peminjaman (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            buku_id INTEGER,
            tanggal_pinjam DATE,
            tanggal_kembali DATE,
            status TEXT DEFAULT 'dipinjam',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(id),
            FOREIGN KEY (buku_id) REFERENCES buku(id)
        )");
        
        // Buat tabel ulasan
        $this->db->exec("CREATE TABLE IF NOT EXISTS ulasan (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            buku_id INTEGER,
            rating INTEGER,
            ulasan TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(id),
            FOREIGN KEY (buku_id) REFERENCES buku(id)
        )");
        
        // Buat tabel notifikasi
        $this->db->exec("CREATE TABLE IF NOT EXISTS notifikasi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            peminjaman_id INTEGER,
            tipe TEXT,
            pesan TEXT,
            dibaca INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(id),
            FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id)
        )");
        
        // Tambahkan admin default
        $this->db->exec("INSERT INTO user (nama, email, username, password, level) 
                        VALUES ('Administrator', 'admin@perpus.com', 'admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')");
        
        // Tambahkan kategori default
        $this->db->exec("INSERT INTO kategori (nama) VALUES ('Fiksi'), ('Non-Fiksi'), ('Pendidikan'), ('Komik'), ('Biografi')");
    }
    
    // Fungsi query untuk mengemulasi mysqli_query
    public function query($sql) {
        $this->last_query = $sql;
        
        // Konversi query MySQL ke SQLite
        $sql = $this->convertMySQLToSQLite($sql);
        
        try {
            $result = $this->db->query($sql);
            
            if ($result === false) {
                $this->last_error = $this->db->lastErrorMsg();
                return false;
            }
            
            // Cek jika query adalah INSERT, UPDATE, atau DELETE
            if (preg_match('/^\s*(INSERT|UPDATE|DELETE)/i', $sql)) {
                $this->affected_rows = $this->db->changes();
                if (preg_match('/^\s*INSERT/i', $sql)) {
                    $this->insert_id = $this->db->lastInsertRowID();
                }
                return true;
            }
            
            return new SQLiteResult($result);
        } catch (Exception $e) {
            $this->last_error = $e->getMessage();
            return false;
        }
    }
    
    // Konversi query MySQL ke SQLite
    private function convertMySQLToSQLite($sql) {
        // Ganti NOW() dengan CURRENT_TIMESTAMP
        $sql = preg_replace('/NOW\(\)/i', 'CURRENT_TIMESTAMP', $sql);
        
        // Ganti LIMIT x,y dengan LIMIT y OFFSET x
        $sql = preg_replace('/LIMIT\s+(\d+)\s*,\s*(\d+)/i', 'LIMIT $2 OFFSET $1', $sql);
        
        return $sql;
    }
    
    // Fungsi untuk mengemulasi mysqli_real_escape_string
    public function real_escape_string($string) {
        return SQLite3::escapeString($string);
    }
    
    // Fungsi untuk mengemulasi mysqli_error
    public function error() {
        return $this->last_error;
    }
    
    // Fungsi untuk mengemulasi mysqli_affected_rows
    public function affected_rows() {
        return $this->affected_rows;
    }
    
    // Fungsi untuk mengemulasi mysqli_insert_id
    public function insert_id() {
        return $this->insert_id;
    }
    
    // Fungsi untuk menutup koneksi
    public function close() {
        if ($this->db) {
            $this->db->close();
        }
    }
}

// Kelas untuk mengemulasi hasil query mysqli
class SQLiteResult {
    private $result;
    
    public function __construct($result) {
        $this->result = $result;
    }
    
    // Fungsi untuk mengemulasi mysqli_fetch_assoc
    public function fetch_assoc() {
        return $this->result->fetchArray(SQLITE3_ASSOC);
    }
    
    // Fungsi untuk mengemulasi mysqli_fetch_array
    public function fetch_array($result_type = SQLITE3_BOTH) {
        return $this->result->fetchArray($result_type);
    }
    
    // Fungsi untuk mengemulasi mysqli_num_rows
    public function num_rows() {
        // SQLite tidak memiliki fungsi num_rows, jadi kita harus menghitung manual
        $count = 0;
        $this->result->reset();
        while ($this->result->fetchArray()) {
            $count++;
        }
        $this->result->reset();
        return $count;
    }
    
    // Fungsi untuk mengemulasi mysqli_free_result
    public function free() {
        // SQLite tidak memerlukan free result
        return true;
    }
}

// Fungsi-fungsi kompatibilitas untuk mengemulasi fungsi mysqli

// Fungsi untuk mengemulasi mysqli_connect
function mysqli_connect($host, $user, $pass, $dbname) {
    return new MySQLiEmulator($host, $user, $pass, $dbname);
}

// Fungsi untuk mengemulasi mysqli_query
function mysqli_query($conn, $sql) {
    return $conn->query($sql);
}

// Fungsi untuk mengemulasi mysqli_fetch_assoc
function mysqli_fetch_assoc($result) {
    return $result->fetch_assoc();
}

// Fungsi untuk mengemulasi mysqli_fetch_array
function mysqli_fetch_array($result, $result_type = SQLITE3_BOTH) {
    return $result->fetch_array($result_type);
}

// Fungsi untuk mengemulasi mysqli_num_rows
function mysqli_num_rows($result) {
    return $result->num_rows();
}

// Fungsi untuk mengemulasi mysqli_affected_rows
function mysqli_affected_rows($conn) {
    return $conn->affected_rows();
}

// Fungsi untuk mengemulasi mysqli_insert_id
function mysqli_insert_id($conn) {
    return $conn->insert_id();
}

// Fungsi untuk mengemulasi mysqli_error
function mysqli_connect_error() {
    global $last_connect_error;
    return $last_connect_error;
}

// Fungsi untuk mengemulasi mysqli_error
function mysqli_error($conn) {
    return $conn->error();
}

// Fungsi untuk mengemulasi mysqli_real_escape_string
function mysqli_real_escape_string($conn, $string) {
    return $conn->real_escape_string($string);
}

// Fungsi untuk mengemulasi mysqli_close
function mysqli_close($conn) {
    return $conn->close();
}

// Fungsi untuk mengemulasi mysqli_free_result
function mysqli_free_result($result) {
    return $result->free();
}
?>