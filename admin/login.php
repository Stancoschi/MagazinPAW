<?php
// admin/login.php
// Nu includem header-ul standard aici, deoarece este o pagină specială
session_start(); // Pornim sesiunea pentru a stoca starea de logat
require_once __DIR__ . '/../includes/db.php'; // Doar conexiunea la DB, dacă folosim tabela admin_users

// Dacă utilizatorul este deja logat, redirecționează către dashboard-ul admin

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = trim($_POST['username'] ?? '');
    $password_input = trim($_POST['password'] ?? '');

    // --- Folosind Opțiunea 2: Tabelă admin_users ---
    try {
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = :username");
        $stmt->execute([':username' => $username_input]);
        $admin = $stmt->fetch();

        if ($admin) { // Verifică dacă adminul a fost găsit
            // Comparație directă a parolei introduse cu cea stocată în DB (în clar)
            if ($password_input === $admin['password_hash']) { // Asigură-te că în DB coloana conține parola în clar
                // Login cu succes
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_user_id'] = $admin['id'];
                
                header('Location: index.php'); // Redirecționează către dashboard-ul admin sau prima pagină admin
                exit;
            } else {
                $error_message = 'Nume de utilizator sau parolă incorectă.';
            }
        } else {
            $error_message = 'Nume de utilizator sau parolă incorectă.';
        }
    } catch (PDOException $e) {
        // Loghează eroarea $e->getMessage()
        $error_message = 'A apărut o eroare la procesarea login-ului. Încercați din nou.';
    }
    // --- Sfârșit Opțiunea 2 ---


    /* // --- Folosind Opțiunea 1: Hardcodat (NU RECOMANDAT PENTRU PRODUCȚIE) ---
    $admin_user = 'admin';
    // Generează acest hash o singură dată: echo password_hash('parolaAdmin123!', PASSWORD_DEFAULT);
    $admin_pass_hash = '$2y$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'; // Înlocuiește cu hash-ul tău real

    if ($username_input === $admin_user && password_verify($password_input, $admin_pass_hash)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin_user;
        header('Location: index.php'); // Sau list_products_admin.php
        exit;
    } else {
        $error_message = 'Nume de utilizator sau parolă incorectă.';
    }
    // --- Sfârșit Opțiunea 1 --- */
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Magazin Virtual</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Ajustează calea dacă e necesar -->
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f4f4; }
        .login-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-container h1 { text-align: center; margin-bottom: 20px; }
        .login-container .form-group { margin-bottom: 15px; }
        .login-container label { display: block; margin-bottom: 5px; }
        .login-container input[type="text"], .login-container input[type="password"] {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;
        }
        .login-container button { width: 100%; padding: 10px; background-color: #5cb85c; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .login-container button:hover { background-color: #4cae4c; }
        .error-message { color: red; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login Administrator</h1>
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Nume utilizator:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Parolă:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>