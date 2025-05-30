<?php
// public/register.php
require_once __DIR__ . '/../includes/header.php'; // Header-ul public

// Dacă utilizatorul este deja logat, redirecționează la pagina principală
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: index.php'); // Sau products_list.php
    exit;
}

// Preia datele din formular dacă există (pentru repopulare în caz de eroare)
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Șterge datele din sesiune după preluare
?>

<div class="container auth-page" style="max-width: 500px; margin-top: 30px; margin-bottom: 30px;">
    <h2 style="text-align: center; margin-bottom: 20px;">Creează Cont Nou</h2>

    <?php
    // Afișare mesaje flash (dacă există din process_register.php)
    if (isset($_SESSION['flash_message'])) {
        $flash_type = $_SESSION['flash_type'] ?? 'info';
        echo '<div class="flash-message ' . escape($flash_type) . '" style="padding: 10px; margin-bottom: 15px; border: 1px solid transparent; border-radius: 4px; color: #31708f; background-color: #d9edf7; border-color: #bce8f1;">' 
             . escape($_SESSION['flash_message']) 
             . '</div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
    ?>

    <form action="process_register.php" method="post" class="auth-form">
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="username">Nume Utilizator:</label>
            <input type="text" id="username" name="username" value="<?php echo escape($form_data['username'] ?? ''); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="email">Adresă Email:</label>
            <input type="email" id="email" name="email" value="<?php echo escape($form_data['email'] ?? ''); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="password">Parolă:</label>
            <input type="password" id="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <small>Minim 8 caractere.</small>
        </div>
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="confirm_password">Confirmă Parola:</label>
            <input type="password" id="confirm_password" name="confirm_password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <div class="form-group">
            <button type="submit" class="button" style="width: 100%; padding: 12px;">Înregistrează-te</button>
        </div>
    </form>
    <p style="text-align: center; margin-top: 20px;">
        Ai deja cont? <a href="login.php">Autentifică-te aici</a>.
    </p>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php'; // Footer-ul public
?>