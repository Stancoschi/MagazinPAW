
<?php
require_once __DIR__ . '/../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    if (!$product_id) {
        $_SESSION['flash_message'] = "ID produs invalid.";
        $_SESSION['flash_type'] = 'error';
        header('Location: list_products_admin.php');
        exit;
    }

    $characteristics_posted = $_POST['characteristics'] ?? [];

    try {
        $pdo->beginTransaction();

        // Șterge mai întâi toate caracteristicile vechi pentru acest produs, apoi le adaugă pe cele noi
        // Alternativ, poți face UPDATE sau INSERT ON DUPLICATE KEY UPDATE
        $stmt_delete = $pdo->prepare("DELETE FROM product_characteristics_values WHERE product_id = :product_id");
        $stmt_delete->execute([':product_id' => $product_id]);

        $stmt_insert = $pdo->prepare("INSERT INTO product_characteristics_values (product_id, characteristic_id, value) VALUES (:product_id, :characteristic_id, :value)");

        foreach ($characteristics_posted as $char_id => $char_value) {
            $char_id_sanitized = filter_var($char_id, FILTER_VALIDATE_INT);
            $char_value_trimmed = trim($char_value);

            if ($char_id_sanitized && !empty($char_value_trimmed)) { // Adaugă doar dacă valoarea nu e goală
                $stmt_insert->execute([
                    ':product_id' => $product_id,
                    ':characteristic_id' => $char_id_sanitized,
                    ':value' => $char_value_trimmed
                ]);
            }
        }

        $pdo->commit();
        $_SESSION['flash_message'] = 'Caracteristicile produsului au fost actualizate.';
        $_SESSION['flash_type'] = 'success';

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['flash_message'] = 'Eroare la salvarea caracteristicilor: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
    header('Location: assign_characteristics.php?product_id=' . $product_id);
    exit;

} else {
    header('Location: list_products_admin.php');
    exit;
}
?>
