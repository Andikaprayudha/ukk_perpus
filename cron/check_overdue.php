<?php
/**
 * Script para verificar livros com prazo de devolução próximo
 * Este script deve ser executado diariamente via cron job
 */

// Define o caminho base
define('BASE_PATH', dirname(__DIR__));

// Carrega as dependências
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/notifikasi.php';

// Verifica livros com prazo próximo ou vencidos
checkOverdueBooks($conn);

echo "Verificação de livros com prazo de devolução concluída em " . date('Y-m-d H:i:s') . "\n";
?>