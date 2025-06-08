<?php
// public/logout.php
session_start();

// Șterge variabilele de sesiune specifice utilizatorului
unset($_SESSION['user_logged_in']);
unset($_SESSION['user_id']);
unset($_SESSION['username']);


$_SESSION['flash_message'] = "Ai fost deconectat cu succes.";
$_SESSION['flash_type'] = 'info';
header('Location: index.php'); // Redirecționează la pagina principală
exit;
?>