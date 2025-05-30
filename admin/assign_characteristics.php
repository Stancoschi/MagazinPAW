<?php
// La începutul fiecărui fișier din /admin/ (ex: admin/index.php, admin/list_products_admin.php etc.)
session_start(); 

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Dacă nu e logat, redirecționează la pagina de login
    header('Location: login.php');
    exit;
}

// Dacă ajunge aici, utilizatorul este logat. Putem continua cu includerea header-ului și restul paginii.
// Notă: header.php din includes/ va porni și el session_start(), dar nu e o problemă dacă e apelat de două ori.
// Alternativ, poți avea un header specific pentru admin care face această verificare.
require_once __DIR__ . '/../includes/header_admin.php'; // Sau header.php dacă folosești același, DAR vezi nota de mai jos
// ... restul codului paginii admin
?>
<?php
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['product_id']) || !filter_var($_GET['product_id'], FILTER_VALIDATE_INT)) {
    $_SESSION['flash_message'] = "ID produs invalid.";
    $_SESSION['flash_type'] = 'error';
    header('Location: list_products_admin.php');
    exit;
}
$product_id = (int)$_GET['product_id'];

try {
    // Preluare detalii produs
    $stmt_prod = $pdo->prepare("SELECT id, name FROM products WHERE id = :id");
    $stmt_prod->execute([':id' => $product_id]);
    $product = $stmt_prod->fetch();

    if (!$product) {
        $_SESSION['flash_message'] = "Produsul nu a fost găsit.";
        $_SESSION['flash_type'] = 'error';
        header('Location: list_products_admin.php');
        exit;
    }

    // Preluare toate tipurile de caracteristici
    $stmt_chars = $pdo->query("SELECT id, name FROM characteristics ORDER BY name ASC");
    $all_characteristics_types = $stmt_chars->fetchAll();

    // Preluare valori caracteristici deja asignate acestui produs
    $stmt_assigned = $pdo->prepare("SELECT characteristic_id, value FROM product_characteristics_values WHERE product_id = :product_id");
    $stmt_assigned->execute([':product_id' => $product_id]);
    $assigned_values = $stmt_assigned->fetchAll(PDO::FETCH_KEY_PAIR); // [characteristic_id => value]

} catch (PDOException $e) {
    die("Eroare: " . $e->getMessage());
}
?>
<div class="container">
    <h2>Asignează/Modifică Caracteristici pentru: <?php echo escape($product['name']); ?></h2>

    <form action="process_assign_characteristics.php" method="post">
        <input type="hidden" name="product_id" value="<?php echo escape($product_id); ?>">

        <?php if (empty($all_characteristics_types)): ?>
            <p>Nu există tipuri de caracteristici definite. Mergeți la <a href="manage_characteristics.php">Gestionare Tipuri de Caracteristici</a> pentru a adăuga.</p>
        <?php else: ?>
            <?php foreach ($all_characteristics_types as $char_type): ?>
                <div>
                    <label for="char_<?php echo escape($char_type['id']); ?>"><?php echo escape($char_type['name']); ?>:</label>
                    <input type="text"
                           id="char_<?php echo escape($char_type['id']); ?>"
                           name="characteristics[<?php echo escape($char_type['id']); ?>]"
                           value="<?php echo isset($assigned_values[$char_type['id']]) ? escape($assigned_values[$char_type['id']]) : ''; ?>">
                    <em>Lăsați gol pentru a nu asigna/șterge valoarea.</em>
                </div>
            <?php endforeach; ?>
            <button type="submit">Salvează Caracteristici</button>
        <?php endif; ?>
         <a href="list_products_admin.php" class="button" style="background-color:#aaa;">Înapoi la Produse</a>
    </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
