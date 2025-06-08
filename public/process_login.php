<?php
// public/process_login.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_identifier = trim($_POST['login_identifier'] ?? ''); // Poate fi username sau email
    $password_input = $_POST['password'] ?? '';
    $errors = [];

    if (empty($login_identifier)) {
        $errors[] = "Numele de utilizator sau emailul este obligatoriu.";
    }
    if (empty($password_input)) {
        $errors[] = "Parola este obligatorie.";
    }

    if (empty($errors)) {
        try {
            // Verificăm dacă login_identifier este email sau username
            $field_type = filter_var($login_identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE {$field_type} = :identifier");
            $stmt->execute([':identifier' => $login_identifier]);
            $user = $stmt->fetch();

            if ($user && password_verify($password_input, $user['password_hash'])) {
                // Login cu succes
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username']; // Sau $user['first_name'] dacă vreau să afișez prenumele
                
                // Redirecționează la pagina principală sau la pagina de unde a venit
                header('Location: index.php'); // Sau products_list.php
                exit;
            } else {
                $errors[] = 'Nume de utilizator/email sau parolă incorectă.';
            }
        } catch (PDOException $e) {
            error_log("Eroare DB la process_login: " . $e->getMessage());
            $errors[] = "A apărut o eroare la procesarea autentificării. Încercați din nou.";
        }
    }

    // Dacă sunt erori, redirecționează înapoi la login cu mesaj
    $_SESSION['flash_message'] = implode("<br>", $errors);
    $_SESSION['flash_type'] = 'error';
    header('Location: login.php');
    exit;

} else {
    header('Location: login.php');
    exit;
}
?>