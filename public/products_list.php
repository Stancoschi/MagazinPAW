<?php
require_once __DIR__ . '/../includes/header.php'; // Asigură-te că $pdo este disponibil și BASE_URL e definit

// --- Logică Sortare ---
$sort_options = [
    'created_at_desc' => 'Cele mai noi',
    'price_asc' => 'Preț: Crescător',
    'price_desc' => 'Preț: Descrescător',
    'name_asc' => 'Nume: A-Z',
    'name_desc' => 'Nume: Z-A'
];
// Preia opțiunea de sortare din GET, default este 'created_at_desc'
$sort_by = isset($_GET['sort']) && array_key_exists($_GET['sort'], $sort_options) ? $_GET['sort'] : 'created_at_desc';

$order_by_sql_clause = "ORDER BY ";
switch ($sort_by) {
    case 'price_asc':
        $order_by_sql_clause .= "p.price ASC";
        break;
    case 'price_desc':
        $order_by_sql_clause .= "p.price DESC";
        break;
    case 'name_asc':
        $order_by_sql_clause .= "p.name ASC";
        break;
    case 'name_desc':
        $order_by_sql_clause .= "p.name DESC";
        break;
    case 'created_at_desc':
    default:
        $order_by_sql_clause .= "p.created_at DESC";
        break;
}
// --- Sfârșit Logică Sortare ---


// --- Logică Căutare (anticipăm puțin, dar o pregătim) ---
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$where_clauses = [];
$bind_params = []; // Parametri pentru prepared statement

if (!empty($search_term)) {
    // Adăugăm clauze pentru căutare în nume, descriere scurtă și detaliată
    // Folosim alias 'p' pentru tabela products pentru claritate
    $where_clauses[] = "(p.name LIKE :search_like OR p.short_description LIKE :search_like OR p.detailed_description LIKE :search_like)";
    $bind_params[':search_like'] = '%' . $search_term . '%';
}

$sql_where_clause = "";
if (!empty($where_clauses)) {
    $sql_where_clause = "WHERE " . implode(" AND ", $where_clauses);
}
// --- Sfârșit Logică Căutare ---


// --- Logică Paginare ---
$products_per_page = 12; // Numărul de produse pe pagină
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}
// Calculăm offset-ul DUPĂ ce știm pagina curentă și numărul total de pagini (pentru a evita offset negativ sau prea mare)

try {
    // Preluare număr total de produse (cu filtrele de căutare aplicate!)
    $sql_total = "SELECT COUNT(*) FROM products p {$sql_where_clause}";
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute($bind_params); // Execută cu parametrii de căutare
    $total_products = (int)$stmt_total->fetchColumn();

    $total_pages = ceil($total_products / $products_per_page);
    if ($total_pages == 0) $total_pages = 1; // Cel puțin o pagină

    if ($current_page > $total_pages) {
        $current_page = $total_pages;
    }
    // Acum calculăm offset-ul final
    $offset = ($current_page - 1) * $products_per_page;
    if ($offset < 0) $offset = 0;


    // Preluare produse pentru pagina curentă, cu sortare și căutare
    $sql_products = "SELECT p.id, p.name, p.price, p.short_description, p.main_photo_filename 
                     FROM products p
                     {$sql_where_clause}
                     {$order_by_sql_clause}
                     LIMIT :offset, :limit_per_page";
    
    $stmt_products = $pdo->prepare($sql_products);
    
    // Adaugă parametrii de căutare la bind_params dacă există
    $final_bind_params = $bind_params; // Copiem parametrii de căutare
    $final_bind_params[':offset'] = $offset;
    $final_bind_params[':limit_per_page'] = $products_per_page;

    // PDO necesită ca valorile pentru LIMIT/OFFSET să fie integeri.
    // Bind-ul se face corect dacă tipul e specificat sau dacă valoarea e deja integer.
    // Pentru a fi expliciți, putem folosi PDO::PARAM_INT, dar în acest caz PHP ar trebui să le trateze corect.
    // PDO tratează :offset și :limit_per_page ca placeholders, nu ca valori directe în query.
    // Asigură-te că $offset și $products_per_page sunt integeri.
    foreach($final_bind_params as $key => &$val) { // Trecem prin referință pentru a putea modifica tipul
        if ($key === ':offset' || $key === ':limit_per_page') {
            // Acest pas nu e strict necesar dacă variabilele sunt deja int, dar e o bună practică
            // $stmt_products->bindValue($key, (int)$val, PDO::PARAM_INT);
            // În cazul nostru, $stmt_products->execute($final_bind_params) ar trebui să funcționeze
            // atâta timp cât valorile pentru offset și limit sunt integeri în array.
        }
    }
    // Executăm cu toți parametrii (căutare + paginare)
    
    
    $stmt_products->execute($final_bind_params);
    $products = $stmt_products->fetchAll();

} catch (PDOException $e) {
    error_log("Eroare DB products_list.php: " . $e->getMessage());
    echo "<div class='container'><p class='error'>A apărut o eroare la preluarea produselor. Vă rugăm încercați mai târziu.</p></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
?>
<div class="container">
    <h2 style="margin-bottom: 20px;">
        Lista Noastră de Produse
        <?php if (!empty($search_term)): ?>
            <small>(Rezultate pentru: "<?php echo escape($search_term); ?>")</small>
        <?php endif; ?>
    </h2>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap;">
        <!-- Formular de Căutare -->
        <form action="products_list.php" method="get" class="search-form" style="margin-bottom:10px; display:flex; align-items:center;">
            <input type="text" name="search_term" placeholder="Caută produse..." 
                   value="<?php echo escape($search_term); ?>" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <?php
            // Păstrează parametrul de sortare (dacă există) când cauți
            if (!empty($sort_by) && $sort_by !== 'created_at_desc') {
                echo '<input type="hidden" name="sort" value="' . escape($sort_by) . '">';
            }
            ?>
            <button type="submit" class="button" style="margin-left: 5px;">Caută</button>
             <?php if (!empty($search_term)): ?>
                <a href="products_list.php<?php if (!empty($sort_by) && $sort_by !== 'created_at_desc') echo '?sort=' . escape($sort_by); ?>" class="button" style="margin-left: 5px; background-color: #aaa;">Șterge Căutare</a>
            <?php endif; ?>
        </form>
        <!-- Sfârșit Formular de Căutare -->

        <!-- Formular de Sortare -->
        <form action="products_list.php" method="get" class="sort-form" style="margin-bottom:10px; display:flex; align-items:center;">
            <label for="sort" style="margin-right: 5px;">Sortează după:</label>
            <select name="sort" id="sort" onchange="this.form.submit()" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <?php foreach ($sort_options as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php if ($sort_by === $value) echo 'selected'; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
            // Păstrează parametrul de căutare (dacă există) când sortezi
            if (!empty($search_term)) {
                echo '<input type="hidden" name="search_term" value="' . escape($search_term) . '">';
            }
            ?>
        </form>
        <!-- Sfârșit Formular de Sortare -->
    </div>


    <?php if (empty($products)): ?>
        <p>Nu s-au găsit produse care să corespundă criteriilor<?php if ($current_page > 1) echo " pe această pagină"; ?>.</p>
    <?php else: ?>
        <div class="product-list" style="display: flex; flex-wrap: wrap; gap: 20px;">
            <?php foreach ($products as $product): ?>
                <div class="product-item" style="border: 1px solid #ddd; padding: 15px; background: #fff; width: calc(33.333% - 20px); box-sizing: border-box;">
                    <?php // Pentru layout cu 4 coloane pe ecrane mai mari, ajustează width, ex: calc(25% - 20px) ?>
                    <?php if (!empty($product['main_photo_filename'])): ?>
                        <a href="product_detail.php?id=<?php echo escape($product['id']); ?>">
                            <img src="<?php echo BASE_URL . '/uploads/products_thumbnails/' . escape($product['main_photo_filename']); ?>" 
                                 alt="<?php echo escape($product['name']); ?>" 
                                 style="width: 100%; height: 200px; object-fit: cover; margin-bottom: 10px;">
                        </a>
                    <?php else: ?>
                        <div style="width: 100%; height: 200px; background: #eee; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                            <span>Imagine indisponibilă</span>
                        </div>
                    <?php endif; ?>
                    <h3 style="margin-top: 0; font-size: 1.1em;"><a href="product_detail.php?id=<?php echo escape($product['id']); ?>" style="text-decoration: none; color: #333;"><?php echo escape($product['name']); ?></a></h3>
                    <p class="price" style="font-weight: bold; color: green; margin-bottom: 10px;"><?php echo escape(number_format($product['price'], 2)); ?> RON</p>
                    <p style="font-size: 0.9em; color: #555; min-height: 60px;"><?php echo escape(substr($product['short_description'], 0, 100)) . (strlen($product['short_description']) > 100 ? '...' : ''); ?></p>
                    <a href="product_detail.php?id=<?php echo escape($product['id']); ?>" class="button">Vezi Detalii</a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Afișare Link-uri Paginare -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Paginare produse" class="pagination-nav" style="margin-top: 30px; text-align: center;">
            <ul class="pagination" style="display: inline-block; padding-left: 0; margin: 20px 0; border-radius: 4px; list-style: none;">
                <?php
                $query_params_for_pagination = [];
                if (!empty($sort_by) && $sort_by !== 'created_at_desc') {
                    $query_params_for_pagination['sort'] = $sort_by;
                }
                if (!empty($search_term)) {
                    $query_params_for_pagination['search_term'] = $search_term;
                }
                // Adaugă și alți parametri de filtrare aici dacă îi implementezi

                $base_pagination_url = "products_list.php";
                if (!empty($query_params_for_pagination)) {
                    $base_pagination_url .= "?" . http_build_query($query_params_for_pagination) . '&';
                } else {
                    $base_pagination_url .= "?";
                }
                ?>

                <?php if ($current_page > 1): ?>
                    <li style="display: inline;">
                        <a href="<?php echo $base_pagination_url; ?>page=<?php echo $current_page - 1; ?>" class="page-link" style="padding: 8px 12px; margin-left: -1px; line-height: 1.25; color: #007bff; background-color: #fff; border: 1px solid #dee2e6; text-decoration:none;">Anterior</a>
                    </li>
                <?php else: ?>
                    <li style="display: inline;"><span class="page-link" style="padding: 8px 12px; margin-left: -1px; line-height: 1.25; color: #6c757d; background-color: #fff; border: 1px solid #dee2e6; cursor:not-allowed;">Anterior</span></li>
                <?php endif; ?>

                <?php
                $num_links_around_current = 2; // Câte link-uri să afișezi în jurul paginii curente
                $ellipsis_printed_start = false;
                $ellipsis_printed_end = false;

                for ($i = 1; $i <= $total_pages; $i++):
                    if ($i == $current_page): ?>
                        <li style="display: inline;"><span class="page-link" style="padding: 8px 12px; margin-left: -1px; line-height: 1.25; color: #fff; background-color: #007bff; border: 1px solid #007bff; font-weight:bold;"><?php echo $i; ?></span></li>
                    <?php elseif ($i == 1 || $i == $total_pages || ($i >= $current_page - $num_links_around_current && $i <= $current_page + $num_links_around_current)): ?>
                        <li style="display: inline;">
                            <a href="<?php echo $base_pagination_url; ?>page=<?php echo $i; ?>" class="page-link" style="padding: 8px 12px; margin-left: -1px; line-height: 1.25; color: #007bff; background-color: #fff; border: 1px solid #dee2e6; text-decoration:none;"><?php echo $i; ?></a>
                        </li>
                    <?php elseif ($i < $current_page && !$ellipsis_printed_start && $i > 1):
                        $ellipsis_printed_start = true; ?>
                        <li style="display: inline;"><span class="page-link" style="padding: 8px 12px; margin-left: -1px; line-height: 1.25; color: #6c757d; background-color: #fff; border: 1px solid #dee2e6;">...</span></li>
                    <?php elseif ($i > $current_page && !$ellipsis_printed_end && $i < $total_pages):
                        $ellipsis_printed_end = true; ?>
                        <li style="display: inline;"><span class="page-link" style="padding: 8px 12px; margin-left: -1px; line-height: 1.25; color: #6c757d; background-color: #fff; border: 1px solid #dee2e6;">...</span></li>
                    <?php endif;
                endfor;
                ?>

                <?php if ($current_page < $total_pages): ?>
                    <li style="display: inline;">
                        <a href="<?php echo $base_pagination_url; ?>page=<?php echo $current_page + 1; ?>" class="page-link" style="padding: 8px 12px; margin-left: -1px; line-height: 1.25; color: #007bff; background-color: #fff; border: 1px solid #dee2e6; text-decoration:none;">Următor</a>
                    </li>
                <?php else: ?>
                     <li style="display: inline;"><span class="page-link" style="padding: 8px 12px; margin-left: -1px; line-height: 1.25; color: #6c757d; background-color: #fff; border: 1px solid #dee2e6; cursor:not-allowed;">Următor</span></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>