
<?php
require_once __DIR__ . '/../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $char_name = trim($_POST['char_name'] ?? '');
    if (empty($char_name)) {
        $_SESSION['flash_message'] = "Numele caracteristicii este obligatoriu.";
        $_SESSION['flash_type'] = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO characteristics (name) VALUES (:name)");
            $stmt->execute([':name' => $char_name]);
            $_SESSION['flash_message'] = "Tipul de caracteristică '" . escape($char_name) . "' a fost adăugat.";
            $_SESSION['flash_type'] = 'success';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Cod pentru duplicate entry
                $_SESSION['flash_message'] = "Acest tip de caracteristică există deja.";
            } else {
                $_SESSION['flash_message'] = "Eroare la adăugarea caracteristicii: " . $e->getMessage();
            }
            $_SESSION['flash_type'] = 'error';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $char_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($char_id) {
        try {
            $pdo->beginTransaction();
            // Șterge mai întâi valorile asociate din product_characteristics_values
            $stmt_del_values = $pdo->prepare("DELETE FROM product_characteristics_values WHERE characteristic_id = :id");
            $stmt_del_values->execute([':id' => $char_id]);

            // Apoi șterge tipul de caracteristică
            $stmt_del_char = $pdo->prepare("DELETE FROM characteristics WHERE id = :id");
            $stmt_del_char->execute([':id' => $char_id]);
            $pdo->commit();

            $_SESSION['flash_message'] = "Tipul de caracteristică și valorile asociate au fost șterse.";
            $_SESSION['flash_type'] = 'success';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['flash_message'] = "Eroare la ștergerea caracteristicii: " . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        }
    } else {
        $_SESSION['flash_message'] = "ID caracteristică invalid pentru ștergere.";
        $_SESSION['flash_type'] = 'error';
    }
}

header('Location: manage_characteristics.php');
exit;
?>
