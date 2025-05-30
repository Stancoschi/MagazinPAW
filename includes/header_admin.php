<?php
// includes/header_admin.php
// session_start() și verificarea de login sunt deja făcute în pagina admin care include acest header
if (session_status() === PHP_SESSION_NONE) { session_start(); } // doar ca fallback
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
// ... (codul pentru BASE_URL) ...
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrare Magazin Virtual</title>
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css"> 
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin_style.css"> <!-- ESTE ACEASTĂ LINIE AICI? -->
</head>
<body class="admin-body">
<header class="admin-header">
    <h1><a href="<?php echo BASE_URL; ?>/admin/index.php">Panou Admin</a></h1>
    
    <?php // Afișează navigația doar dacă adminul este logat și NU suntem pe pagina de login
    $currentPage = basename($_SERVER['PHP_SELF']); // Ia numele fișierului curent
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && $currentPage !== 'login.php'): 
    ?>
        <nav class="admin-nav">
            <ul>
                <li>
                    <a href="<?php echo BASE_URL; ?>/admin/index.php" 
                       class="<?php if ($currentPage === 'index.php') echo 'active'; ?>">
                       <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/admin/list_products_admin.php"
                       class="<?php if ($currentPage === 'list_products_admin.php' || $currentPage === 'add_product.php' || $currentPage === 'edit_product.php') echo 'active'; ?>">
                       <i class="fas fa-box-open"></i> Produse
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/admin/manage_characteristics.php"
                       class="<?php if ($currentPage === 'manage_characteristics.php' || $currentPage === 'assign_characteristics.php') echo 'active'; ?>">
                       <i class="fas fa-tags"></i> Caracteristici
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/admin/logout.php" style="color: #dc3545; font-weight: bold;">
                        <i class="fas fa-sign-out-alt"></i> Logout (<?php echo escape($_SESSION['admin_username'] ?? 'Admin'); ?>)
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</header>
<main class="admin-container">
    <?php
    // Afișare mesaje flash (dacă există)
    if (isset($_SESSION['flash_message'])) {
        // ... (codul tău pentru afișare flash message) ...
    }
    ?>
    <!-- Conținutul specific paginii admin va veni aici -->