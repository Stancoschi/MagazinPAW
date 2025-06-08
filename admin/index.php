<?php
// La începutul absolut al fișierului
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Pornim sesiunea aici, o singură dată pentru această pagină


// 1. Verifică dacă adminul este logat
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // echo "DEBUG index.php: NU e logat, redirect la login.php<br>";
    header('Location: login.php');
    exit;
}
// echo "DEBUG index.php: ESTE logat, continua.<br>";

// Include header-ul (care va include db.php și functions.php)
require_once __DIR__ . '/../includes/header_admin.php';

// De aici încolo, $pdo, BASE_URL și escape() ar trebui să fie disponibile.

// Inițializăm variabilele pentru statistici
$total_products = "N/A"; // Valori default în caz de eroare
$total_characteristics_types = "N/A";
$latest_products = [];
$dashboard_error = null;

// echo "DEBUG index.php: Inainte de try-catch pentru statistici.<br>";
// if (!isset($pdo)) { echo "DEBUG index.php: EROARE - PDO NU este setat inainte de query-uri!<br>"; }

try {
    if (isset($pdo)) { // Verifică dacă $pdo este disponibil
        // Numărul total de produse
        $stmt_total_products = $pdo->query("SELECT COUNT(*) FROM products");
        if ($stmt_total_products) {
            $total_products = (int)$stmt_total_products->fetchColumn();
        }

        // Numărul total de tipuri de caracteristici
        $stmt_total_char_types = $pdo->query("SELECT COUNT(*) FROM characteristics");
        if ($stmt_total_char_types) {
            $total_characteristics_types = (int)$stmt_total_char_types->fetchColumn();
        }

        // Ultimele 5 produse adăugate
        $stmt_latest_products = $pdo->query("SELECT id, name, created_at FROM products ORDER BY created_at DESC LIMIT 5");
        if ($stmt_latest_products) {
            $latest_products = $stmt_latest_products->fetchAll();
        }
    } else {
        $dashboard_error = "Eroare critică: Conexiunea la baza de date nu este disponibilă.";
    }
} catch (PDOException $e) {
    error_log("Eroare DB pe admin dashboard: " . $e->getMessage());
    $dashboard_error = "Nu s-au putut încărca statisticile. Eroare: " . escape($e->getMessage());
}
// echo "DEBUG index.php: Dupa try-catch. Total produse: " . $total_products . "<br>";

?>

<div class="admin-dashboard-content" style="padding-top: 0px;"> 
    <h2>Panou de Control Administrator</h2>
    <p style="font-size: 1.1em; margin-bottom: 25px;">Bine ai venit, <strong><?php echo escape($_SESSION['admin_username'] ?? 'Admin'); ?></strong>!</p>

    <?php if (isset($dashboard_error)): ?>
        <p class="flash-message error" style="color:red; border:1px solid red; padding:10px;"><?php echo $dashboard_error; ?></p>
    <?php endif; ?>

    <!-- Secțiunea de Statistici -->
    <div class="dashboard-stats" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
        <div class="stat-card" style="background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 5px; padding: 20px; flex: 1; min-width: 200px; text-align: center;">
            <h3 style="margin-top: 0; color: #337ab7;">Produse Totale</h3>
            <p style="font-size: 2em; font-weight: bold; margin-bottom: 0;"><?php echo $total_products; ?></p>
        </div>
        <div class="stat-card" style="background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 5px; padding: 20px; flex: 1; min-width: 200px; text-align: center;">
            <h3 style="margin-top: 0; color: #5cb85c;">Tipuri Caracteristici</h3>
            <p style="font-size: 2em; font-weight: bold; margin-bottom: 0;"><?php echo $total_characteristics_types; ?></p>
        </div>
    </div>

    <!-- Secțiunea de Link-uri Rapide -->
    <div class="dashboard-quick-links" style="margin-bottom: 30px;">
        <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">Link-uri Rapide</h3>
        <p>
            <a href="<?php echo BASE_URL; ?>/admin/list_products_admin.php" class="button" style="margin-right: 10px; margin-bottom: 10px; background-color: #337ab7;">Gestionează Produse</a>
            <a href="<?php echo BASE_URL; ?>/admin/add_product.php" class="button" style="margin-right: 10px; margin-bottom: 10px; background-color: #5cb85c;">Adaugă Produs Nou</a>
            <a href="<?php echo BASE_URL; ?>/admin/manage_characteristics.php" class="button" style="margin-right: 10px; margin-bottom: 10px;">Gestionează Tipuri Caracteristici</a>
        </p>
    </div>

    <!-- Secțiunea Ultimele Produse Adăugate -->
    <?php if (!empty($latest_products)): ?>
    <div class="dashboard-latest-products">
        <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">Ultimele Produse Adăugate</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Nume Produs</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Data Adăugării</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latest_products as $product): ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo escape($product['name']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo date("d M Y, H:i", strtotime($product['created_at'])); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                        <a href="<?php echo BASE_URL; ?>/admin/edit_product.php?id=<?php echo $product['id']; ?>" class="button edit" style="padding: 5px 10px; font-size: 0.9em;">Editează</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p>Nu există produse adăugate recent.</p>
    <?php endif; ?>

</div>

<?php
require_once __DIR__ . '/../includes/footer_admin.php';
?>