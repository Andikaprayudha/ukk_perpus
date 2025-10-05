<?php
/**
 * File fungsi-fungsi pembantu untuk aplikasi perpustakaan
 */

/**
 * Fungsi untuk mengatur pesan flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Fungsi untuk mendapatkan pesan flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Fungsi untuk menampilkan pesan flash
 */
function displayFlashMessage() {
    $message = getFlashMessage();
    if ($message) {
        $type = $message['type'];
        $text = $message['message'];
        
        $alertClass = 'alert-info';
        if ($type == 'success') $alertClass = 'alert-success';
        if ($type == 'error' || $type == 'danger') $alertClass = 'alert-danger';
        if ($type == 'warning') $alertClass = 'alert-warning';
        
        echo "<div class='alert {$alertClass} alert-dismissible fade show' role='alert'>
                {$text}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

/**
 * Fungsi untuk memvalidasi input
 */
function validateInput($data, $rules = []) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        // Cek required
        if (strpos($rule, 'required') !== false && (empty($data[$field]) && $data[$field] !== '0')) {
            $errors[$field] = ucfirst($field) . ' tidak boleh kosong';
            continue;
        }
        
        // Skip validasi lain jika field kosong dan tidak required
        if (empty($data[$field]) && strpos($rule, 'required') === false) {
            continue;
        }
        
        // Cek email
        if (strpos($rule, 'email') !== false && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[$field] = 'Format email tidak valid';
        }
        
        // Cek min length
        if (preg_match('/min:(\d+)/', $rule, $matches)) {
            $min = $matches[1];
            if (strlen($data[$field]) < $min) {
                $errors[$field] = ucfirst($field) . ' minimal ' . $min . ' karakter';
            }
        }
        
        // Cek max length
        if (preg_match('/max:(\d+)/', $rule, $matches)) {
            $max = $matches[1];
            if (strlen($data[$field]) > $max) {
                $errors[$field] = ucfirst($field) . ' maksimal ' . $max . ' karakter';
            }
        }
        
        // Cek numeric
        if (strpos($rule, 'numeric') !== false && !is_numeric($data[$field])) {
            $errors[$field] = ucfirst($field) . ' harus berupa angka';
        }
    }
    
    return $errors;
}

/**
 * Fungsi untuk mengupload file
 */
function uploadFile($file, $destination, $allowedTypes = [], $maxSize = 2097152) {
    // Cek apakah ada error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'Error saat upload file: ' . $file['error']
        ];
    }
    
    // Cek ukuran file
    if ($file['size'] > $maxSize) {
        return [
            'success' => false,
            'message' => 'Ukuran file terlalu besar (maksimal ' . ($maxSize / 1048576) . 'MB)'
        ];
    }
    
    // Cek tipe file
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
        return [
            'success' => false,
            'message' => 'Tipe file tidak diizinkan (hanya ' . implode(', ', $allowedTypes) . ')'
        ];
    }
    
    // Buat nama file unik
    $fileName = uniqid() . '.' . $fileType;
    $targetPath = $destination . '/' . $fileName;
    
    // Buat direktori jika belum ada
    if (!is_dir($destination)) {
        mkdir($destination, 0777, true);
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'file_name' => $fileName,
            'file_path' => $targetPath
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal mengupload file'
        ];
    }
}

/**
 * Fungsi untuk format tanggal Indonesia
 */
function formatTanggal($date) {
    $bulan = [
        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $tanggal = date('j', strtotime($date));
    $bulan_index = date('n', strtotime($date));
    $tahun = date('Y', strtotime($date));
    
    return $tanggal . ' ' . $bulan[$bulan_index] . ' ' . $tahun;
}

/**
 * Fungsi untuk menghitung selisih hari
 */
function hitungSelisihHari($tanggal1, $tanggal2) {
    $date1 = new DateTime($tanggal1);
    $date2 = new DateTime($tanggal2);
    $interval = $date1->diff($date2);
    return $interval->days;
}

/**
 * Fungsi untuk membatasi teks
 */
function limitText($text, $limit = 100) {
    if (strlen($text) <= $limit) {
        return $text;
    }
    
    return substr($text, 0, $limit) . '...';
}
?>