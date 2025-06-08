<?php
session_start(); // Pornim sesiunea pentru mesaje flash și repopulare formular
require_once __DIR__ . '/../includes/db.php';   // Pentru $pdo
require_once __DIR__ . '/../includes/functions.php'; // Pentru escape()

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Nu facem trim la parolă
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    // Validări
    if (empty($username)) {
        $errors[] = "Numele de utilizator este obligatoriu.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Numele de utilizator trebuie să aibă între 3 și 50 de caractere.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Numele de utilizator poate conține doar litere, cifre și underscore (_).";
    }

    if (empty($email)) {
        $errors[] = "Adresa de email este obligatorie.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresa de email nu este validă.";
    }

    if (empty($password)) {
        $errors[] = "Parola este obligatorie.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Parola trebuie să aibă minim 8 caractere.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Parolele nu se potrivesc.";
    }

    // Verifică dacă username-ul sau email-ul există deja (dacă nu sunt erori de validare)
    if (empty($errors)) {
        try {
            // Verifică username
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            if ($stmt->fetch()) {
                $errors[] = "Acest nume de utilizator este deja folosit.";
            }

            // Verifică email
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = "Această adresă de email este deja înregistrată.";
            }
        } catch (PDOException $e) {
            error_log("Eroare DB la verificare unicitate in process_register: " . $e->getMessage());
            $errors[] = "A apărut o eroare la verificarea datelor. Încercați din nou.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['flash_message'] = implode("<br>", $errors);
        $_SESSION['flash_type'] = 'error';
        // Salvează datele introduse pentru a repopula formularul (cu excepția parolelor)
        $_SESSION['form_data'] = ['username' => $username, 'email' => $email];
        header('Location: register.php');
        exit;
    } else {
        // Nu sunt erori, putem crea utilizatorul
        $password_hash = password_hash($password, PASSWORD_DEFAULT); // Hash-uieșc parola!

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $password_hash
            ]);

            $_SESSION['flash_message'] = "Contul tău a fost creat cu succes! Te poți autentifica acum.";
            $_SESSION['flash_type'] = 'success';
            header('Location: login.php'); // Redirecționează la pagina de login
            exit;

        } catch (PDOException $e) {
            error_log("Eroare DB la inserare user in process_register: " . $e->getMessage());
            $_SESSION['flash_message'] = "A apărut o eroare la crearea contului. Încercați din nou mai târziu.";
            $_SESSION['flash_type'] = 'error';
            $_SESSION['form_data'] = ['username' => $username, 'email' => $email]; // Repopulează
            header('Location: register.php');
            exit;
        }
    }
} else {
    // Dacă nu e POST, redirecționează
    header('Location: register.php');
    exit;
}
?>