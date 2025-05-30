<?php
// public/logout.php
session_start();

// Șterge variabilele de sesiune specifice utilizatorului
unset($_SESSION['user_logged_in']);
unset($_SESSION['user_id']);
unset($_SESSION['username']);

// Opțional, poți distruge complet sesiunea dacă nu mai ai nevoie de nimic altceva în ea
// $_SESSION = array();
// if (ini_get("session.use_cookies")) {
//    $params = session_get_cookie_params();
//    setcookie(session_name(), '', time() - 42000,
//        $params["path"], $params["domain"],
//        $params["secure"], $params["httponly"]
//    );
// }
// session_destroy();

$_SESSION['flash_message'] = "Ai fost deconectat cu succes.";
$_SESSION['flash_type'] = 'info';
header('Location: index.php'); // Redirecționează la pagina principală
exit;
?>