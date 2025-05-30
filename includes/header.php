<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';       // Presupunând că nu e nevoie de DB direct în header-ul public
require_once __DIR__ . '/functions.php'; // Pentru escape() și BASE_URL

// ... (codul pentru BASE_URL) ...
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magazin Virtual</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/lightbox.min.css">
    <!-- Fonturi și alte CSS-uri -->
</head>
<body>
    <header>
        <h1><a href="<?php echo BASE_URL; ?>/public/index.php">Magazin Virtual</a></h1>
        <nav>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/public/index.php">Acasă</a></li>
                <li><a href="<?php echo BASE_URL; ?>/public/products_list.php">Produse</a></li>
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <li><span style="padding: 0 15px; color: #555;">Salut, <?php echo escape($_SESSION['username']); ?>!</span></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/logout.php">Logout</a></li>
                    <!-- Poți adăuga aici link către "Contul Meu" etc. -->
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>/public/login.php">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/register.php">Înregistrare</a></li>
                <?php endif; ?>
                 <li><a href="<?php echo BASE_URL; ?>/admin/login.php" style="font-size:0.8em; opacity:0.7;">(Admin)</a></li>
            </ul>
        </nav>
    </header>
    <main class="container"> <?php // Adaugă clasa container aici sau în fiecare pagină ?>
        <?php
        // Afișare mesaje flash
        if (isset($_SESSION['flash_message'])) {
            $flash_type = $_SESSION['flash_type'] ?? 'info';
            echo '<div class="flash-message ' . escape($flash_type) . '" style="padding: 10px; margin-bottom: 15px; border: 1px solid; border-radius: 4px;">' 
                 . escape($_SESSION['flash_message']) 
                 . '</div>';
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        }
        ?>
        <!-- Conținutul specific paginii va veni aici -->