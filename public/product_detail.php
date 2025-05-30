<?php
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['flash_message'] = "ID produs invalid.";
    $_SESSION['flash_type'] = 'error';
    // Ideal ar fi o pagină de eroare 404, dar pentru simplitate redirecționăm la lista de produse
    header('Location: products_list.php');
    exit;
}
$product_id = (int)$_GET['id'];

try {
    // Preluare detalii produs
    $stmt_product = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt_product->execute([':id' => $product_id]);
    $product = $stmt_product->fetch();

    if (!$product) {
        $_SESSION['flash_message'] = "Produsul nu a fost găsit.";
        $_SESSION['flash_type'] = 'error';
        header('Location: products_list.php');
        exit;
    }

    // Preluare fotografii secundare
    $stmt_photos = $pdo->prepare("SELECT filename FROM product_photos WHERE product_id = :product_id AND is_main = 0 ORDER BY id ASC");
    $stmt_photos->execute([':product_id' => $product_id]);
    $secondary_photos = $stmt_photos->fetchAll(PDO::FETCH_COLUMN);

    // Preluare caracteristici și valorile lor
    $sql_chars = "SELECT c.name AS char_name, pcv.value AS char_value
                  FROM product_characteristics_values pcv
                  JOIN characteristics c ON pcv.characteristic_id = c.id
                  WHERE pcv.product_id = :product_id
                  ORDER BY c.name ASC";
    $stmt_chars = $pdo->prepare($sql_chars);
    $stmt_chars->execute([':product_id' => $product_id]);
    $characteristics_values = $stmt_chars->fetchAll();

} catch (PDOException $e) {
    // Loghează eroarea și afișează un mesaj prietenos
    error_log("Eroare DB product_detail.php: " . $e->getMessage());
    die("A apărut o eroare la încărcarea detaliilor produsului. Vă rugăm încercați mai târziu.");
}
?>
<div class="container">
    <div class="product-detail-container">
        <div class="product-images">
            <?php if (!empty($product['main_photo_filename'])): ?>
                <img src="<?php echo BASE_URL . '/uploads/products/' . escape($product['main_photo_filename']); ?>"
                     alt="Imagine principală <?php echo escape($product['name']); ?>" class="main-image" id="mainProductImage">
            <?php else: ?>
                <div style="width:100%; height:300px; background:#eee; text-align:center; line-height:300px; margin-bottom:10px; border:1px solid #ccc;">Imagine principală indisponibilă</div>
            <?php endif; ?>

            <?php if (!empty($secondary_photos)): ?>
                <div class="secondary-images">
                    <?php foreach ($secondary_photos as $photo_filename): ?>
                        <img src="<?php echo BASE_URL . '/uploads/products_thumbnails/' . escape($photo_filename); ?>"
                             data-large-src="<?php echo BASE_URL . '/uploads/products/' . escape($photo_filename); ?>"
                             alt="Imagine secundară" class="secondary-thumbnail">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <h2><?php echo escape($product['name']); ?></h2>
            <p class="price" style="font-size:1.5em; color:green;"><?php echo escape(number_format($product['price'], 2)); ?> RON</p>

            <h3>Descriere Scurtă</h3>
            <p><?php echo nl2br(escape($product['short_description'])); ?></p>

            <?php if(!empty($product['detailed_description'])): ?>
            <h3>Descriere Detaliată</h3>
            <div><?php echo nl2br(escape($product['detailed_description'])); // Sau un parser Markdown/HTML Purifier dacă permiți HTML ?></div>
            <?php endif; ?>

            <?php if (!empty($characteristics_values)): ?>
                <h3>Caracteristici Produs</h3>
                <dl class="characteristics-list">
                    <?php foreach ($characteristics_values as $cv): ?>
                        <dt><?php echo escape($cv['char_name']); ?>:</dt>
                        <dd><?php echo escape($cv['char_value']); ?></dd>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>

            <!-- Aici poți adăuga un buton "Adaugă în coș" dacă extinzi funcționalitatea -->
        </div>
    </div>
    <p style="margin-top:20px;"><a href="products_list.php" class="button">&laquo; Înapoi la listă</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
