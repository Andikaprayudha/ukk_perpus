<?php
/**
 * Sistema de notificação para devolução de livros
 * Este arquivo contém funções para gerenciar notificações de devolução
 */

/**
 * Verifica empréstimos com prazo próximo do vencimento ou já vencidos
 * e gera notificações para os usuários
 */
function checkOverdueBooks($conn) {
    // Busca empréstimos que estão próximos do vencimento (3 dias) ou já vencidos
    $query = "
        SELECT p.*, u.nama as nama_user, u.email as email_user, b.judul as judul_buku
        FROM peminjaman p
        JOIN user u ON p.user_id = u.id
        JOIN buku b ON p.buku_id = b.id
        WHERE p.status = 'dipinjam' AND (
            (p.tanggal_kembali BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY))
            OR (p.tanggal_kembali < CURDATE())
        )
    ";
    
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tanggal_kembali = new DateTime($row['tanggal_kembali']);
            $today = new DateTime();
            
            // Verifica se já está vencido
            if ($today > $tanggal_kembali) {
                $days_overdue = $today->diff($tanggal_kembali)->days;
                createNotification(
                    $conn, 
                    $row['user_id'], 
                    'overdue', 
                    "Buku '{$row['judul_buku']}' terlambat {$days_overdue} hari. Harap segera kembalikan.",
                    $row['id']
                );
            } else {
                // Está próximo do vencimento
                $days_remaining = $today->diff($tanggal_kembali)->days;
                createNotification(
                    $conn, 
                    $row['user_id'], 
                    'reminder', 
                    "Buku '{$row['judul_buku']}' harus dikembalikan dalam {$days_remaining} hari.",
                    $row['id']
                );
            }
        }
    }
}

/**
 * Cria uma notificação no banco de dados
 */
function createNotification($conn, $user_id, $type, $message, $peminjaman_id) {
    // Verifica se já existe uma notificação similar para evitar duplicatas
    $check_query = "
        SELECT id FROM notifikasi 
        WHERE user_id = ? AND peminjaman_id = ? AND type = ? AND DATE(created_at) = CURDATE()
    ";
    
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("iis", $user_id, $peminjaman_id, $type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Se não existir notificação similar hoje, cria uma nova
    if ($result->num_rows == 0) {
        $insert_query = "
            INSERT INTO notifikasi (user_id, peminjaman_id, type, message, is_read, created_at)
            VALUES (?, ?, ?, ?, 0, NOW())
        ";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iiss", $user_id, $peminjaman_id, $type, $message);
        $stmt->execute();
    }
}

/**
 * Busca notificações não lidas para um usuário
 */
function getUnreadNotifications($conn, $user_id) {
    $query = "
        SELECT n.*, p.buku_id, b.judul as judul_buku
        FROM notifikasi n
        LEFT JOIN peminjaman p ON n.peminjaman_id = p.id
        LEFT JOIN buku b ON p.buku_id = b.id
        WHERE n.user_id = ? AND n.is_read = 0
        ORDER BY n.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    return $stmt->get_result();
}

/**
 * Busca todas as notificações para um usuário
 */
function getAllNotifications($conn, $user_id, $limit = 10, $offset = 0) {
    $query = "
        SELECT n.*, p.buku_id, b.judul as judul_buku
        FROM notifikasi n
        LEFT JOIN peminjaman p ON n.peminjaman_id = p.id
        LEFT JOIN buku b ON p.buku_id = b.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    
    return $stmt->get_result();
}

/**
 * Marca uma notificação como lida
 */
function markNotificationAsRead($conn, $notification_id) {
    $query = "UPDATE notifikasi SET is_read = 1 WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $notification_id);
    
    return $stmt->execute();
}

/**
 * Marca todas as notificações de um usuário como lidas
 */
function markAllNotificationsAsRead($conn, $user_id) {
    $query = "UPDATE notifikasi SET is_read = 1 WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    
    return $stmt->execute();
}

/**
 * Conta o número de notificações não lidas para um usuário
 */
function countUnreadNotifications($conn, $user_id) {
    $query = "SELECT COUNT(*) as count FROM notifikasi WHERE user_id = ? AND is_read = 0";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'];
}
?>