

<?php
require_once __DIR__ . '/../includes/header.php';

try {
    $stmt = $pdo->query("SELECT id, name, price, main_photo_filename FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Eroare la preluarea produselor: " . $e->getMessage());
}
?>

<div class="container">
    <h2>Gestionare Produse</h2>
    <p><a href="add_product.php" class="button">Adaugă Produs Nou</a></p>

    <?php if (empty($products)): ?>
        <p>Nu există produse în baza de date.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagine</th>
                    <th>Nume</th>
                    <th>Preț</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo escape($product['id']); ?></td>
                        <td>
                            <?php if (!empty($product['main_photo_filename'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/products_thumbnails/' . escape($product['main_photo_filename']); ?>" alt="<?php echo escape($product['name']); ?>" style="width:50px; height:auto;">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo escape($product['name']); ?></td>
                        <td><?php echo escape(number_format($product['price'], 2)); ?> RON</td>
                        <td>
                            <a href="edit_product.php?id=<?php echo escape($product['id']); ?>" class="button edit">Editează</a>
                            <a href="delete_product.php?id=<?php echo escape($product['id']); ?>" class="button delete" onclick="return confirm('Sigur doriți să ștergeți acest produs?');">Șterge</a>
                            <!-- Link către pagina de asignare caracteristici (Student B) -->
                            <a href="assign_characteristics.php?product_id=<?php echo escape($product['id']); ?>" class="button">Asignează Caracteristici</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
