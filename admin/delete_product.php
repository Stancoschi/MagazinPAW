
<?php
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['flash_message'] = "ID produs invalid pentru ștergere.";
    $_SESSION['flash_type'] = 'error';
    header('Location: list_products_admin.php');
    exit;
}
$product_id = (int)$_GET['id'];

// Pre-verificare / afișare confirmare


try {
    // Preluare nume fișiere imagini pentru a le șterge de pe disc
    $stmt_main = $pdo->prepare("SELECT main_photo_filename FROM products WHERE id = :id");
    $stmt_main->execute([':id' => $product_id]);
    $main_photo = $stmt_main->fetchColumn();

    $stmt_sec = $pdo->prepare("SELECT filename FROM product_photos WHERE product_id = :id");
    $stmt_sec->execute([':id' => $product_id]);
    $secondary_photos = $stmt_sec->fetchAll(PDO::FETCH_COLUMN);

    // Inițiază tranzacția
    $pdo->beginTransaction();

    // Șterge înregistrările din product_characteristics_values (dacă există)
    $stmt_char_val = $pdo->prepare("DELETE FROM product_characteristics_values WHERE product_id = :id");
    $stmt_char_val->execute([':id' => $product_id]);

    // Șterge înregistrările din product_photos
    $stmt_photos_db = $pdo->prepare("DELETE FROM product_photos WHERE product_id = :id");
    $stmt_photos_db->execute([':id' => $product_id]);

    // Șterge produsul din tabela products
    $stmt_product = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt_product->execute([':id' => $product_id]);

    // Dacă totul a mers bine în DB, șterge fișierele
    $originalUploadDir = __DIR__ . '/../uploads/products/';
    $thumbnailUploadDir = __DIR__ . '/../uploads/products_thumbnails/';

    if ($main_photo && file_exists($originalUploadDir . $main_photo)) {
        unlink($originalUploadDir . $main_photo);
    }
    if ($main_photo && file_exists($thumbnailUploadDir . $main_photo)) {
        unlink($thumbnailUploadDir . $main_photo);
    }

    foreach ($secondary_photos as $photo_filename) {
        if (file_exists($originalUploadDir . $photo_filename)) {
            unlink($originalUploadDir . $photo_filename);
        }
        if (file_exists($thumbnailUploadDir . $photo_filename)) {
            unlink($thumbnailUploadDir . $photo_filename);
        }
    }

    $pdo->commit();

    $_SESSION['flash_message'] = 'Produsul a fost șters cu succes!';
    $_SESSION['flash_type'] = 'success';

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['flash_message'] = 'Eroare la ștergerea produsului: ' . $e->getMessage();
    $_SESSION['flash_type'] = 'error';
}

header('Location: list_products_admin.php');
exit;
?>
