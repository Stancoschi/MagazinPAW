
<?php
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['flash_message'] = "ID produs invalid.";
    $_SESSION['flash_type'] = 'error';
    header('Location: list_products_admin.php');
    exit;
}
$product_id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['flash_message'] = "Produsul nu a fost găsit.";
        $_SESSION['flash_type'] = 'error';
        header('Location: list_products_admin.php');
        exit;
    }

    // Preluare imagini secundare
    $stmt_photos = $pdo->prepare("SELECT id, filename FROM product_photos WHERE product_id = :product_id AND is_main = 0");
    $stmt_photos->execute([':product_id' => $product_id]);
    $secondary_photos_db = $stmt_photos->fetchAll();

} catch (PDOException $e) {
    die("Eroare: " . $e->getMessage());
}
?>

<div class="container">
    <h2>Editare Produs: <?php echo escape($product['name']); ?></h2>

    <form action="process_edit_product.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?php echo escape($product['id']); ?>">

        <div>
            <label for="name">Nume Produs:</label>
            <input type="text" id="name" name="name" value="<?php echo escape($product['name']); ?>" required>
        </div>
        <div>
            <label for="short_description">Descriere Scurtă:</label>
            <textarea id="short_description" name="short_description"><?php echo escape($product['short_description']); ?></textarea>
        </div>
        <div>
            <label for="detailed_description">Descriere Detaliată:</label>
            <textarea id="detailed_description" name="detailed_description"><?php echo escape($product['detailed_description']); ?></textarea>
        </div>
        <div>
            <label for="price">Preț (RON):</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo escape($product['price']); ?>" required>
        </div>

        <div>
            <label>Fotografie Principală Actuală:</label>
            <?php if (!empty($product['main_photo_filename'])): ?>
                <img src="<?php echo BASE_URL . '/uploads/products_thumbnails/' . escape($product['main_photo_filename']); ?>" alt="Main photo" style="max-width: 100px; display:block; margin-bottom:10px;">
                <input type="checkbox" name="delete_main_photo" id="delete_main_photo"> <label for="delete_main_photo">Șterge fotografia principală actuală</label>
            <?php else: ?>
                <p>Nicio fotografie principală setată.</p>
            <?php endif; ?>
            <label for="main_photo">Înlocuiește/Adaugă Fotografie Principală:</label>
            <input type="file" id="main_photo" name="main_photo" accept="image/jpeg, image/png, image/gif">
        </div>

        <div>
            <label>Fotografii Secundare Actuale:</label>
            <?php if (!empty($secondary_photos_db)): ?>
                <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
                <?php foreach($secondary_photos_db as $photo): ?>
                    <div style="text-align:center;">
                        <img src="<?php echo BASE_URL . '/uploads/products_thumbnails/' . escape($photo['filename']); ?>" alt="Sec photo" style="max-width: 80px; display:block;">
                        <input type="checkbox" name="delete_secondary_photos[]" value="<?php echo escape($photo['id']); ?>" id="del_sec_<?php echo escape($photo['id']); ?>">
                        <label for="del_sec_<?php echo escape($photo['id']); ?>">Șterge</label>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Nicio fotografie secundară.</p>
            <?php endif; ?>
            <label for="secondary_photos">Adaugă Fotografii Secundare Noi (max 5):</label>
            <input type="file" id="secondary_photos" name="secondary_photos[]" multiple accept="image/jpeg, image/png, image/gif">
        </div>

        <button type="submit">Actualizează Produs</button>
        <a href="list_products_admin.php" class="button" style="background-color:#aaa;">Anulează</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
