<?php
// public/login.php
require_once __DIR__ . '/../includes/header.php'; // Header-ul public

// Dacă utilizatorul este deja logat, redirecționează
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: index.php'); // Sau products_list.php
    exit;
}
?>

<div class="container auth-page" style="max-width: 500px; margin-top: 30px; margin-bottom: 30px;">
    <h2 style="text-align: center; margin-bottom: 20px;">Autentificare Utilizator</h2>

    <?php
    if (isset($_SESSION['flash_message'])) {
        $flash_type = $_SESSION['flash_type'] ?? 'info';
        echo '<div class="flash-message ' . escape($flash_type) . '">' . escape($_SESSION['flash_message']) . '</div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
    ?>

    <form action="process_login.php" method="post" class="auth-form">
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="login_identifier">Nume Utilizator sau Email:</label>
            <input type="text" id="login_identifier" name="login_identifier" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="password">Parolă:</label>
            <input type="password" id="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <div class="form-group">
            <button type="submit" class="button" style="width: 100%; padding: 12px;">Autentificare</button>
        </div>
    </form>
    <p style="text-align: center; margin-top: 20px;">
        Nu ai cont? <a href="register.php">Creează unul aici</a>.
    </p>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php'; // Footer-ul public
?>