<?php
// public/index.php
require_once __DIR__ . '/../includes/header.php'; // Header-ul public

// --- Preluare Date Specifice pentru Pagina Acasă (Opțional) ---
// De exemplu, preluăm câteva produse noi (sau populare, sau dintr-o categorie specifică)
$featured_products_title = "Noutăți în Magazin";
$featured_products = [];
$home_page_error = null;

try {
    if (isset($pdo)) { // Verifică dacă $pdo este disponibil (ar trebui să fie din header.php)
        // Preluăm, de exemplu, ultimele 4 produse adăugate
        $stmt_featured = $pdo->query("SELECT id, name, price, short_description, main_photo_filename 
                                      FROM products 
                                      ORDER BY created_at DESC 
                                      LIMIT 4"); // Ajustează numărul după preferințe
        if ($stmt_featured) {
            $featured_products = $stmt_featured->fetchAll();
        }
    } else {
        $home_page_error = "Eroare critică: Conexiunea la baza de date nu este disponibilă.";
    }
} catch (PDOException $e) {
    error_log("Eroare DB pe public/index.php (featured products): " . $e->getMessage());
    $home_page_error = "Nu s-au putut încărca produsele recomandate.";
}
// Poți prelua și alte date aici, de ex: număr total de produse pentru un mic "despre noi", categorii etc.
?>

<div class="page-home">

    <!-- 1. Secțiunea Hero -->
    <section class="hero-section" style="background-image: url('<?php echo BASE_URL; ?>/images/banner_magazin.jpg'); /* Înlocuiește cu imaginea ta */
                                        background-size: cover; background-position: center; color: white; 
                                        text-align: center; padding: 80px 20px; margin-bottom: 40px;">
        <div class="hero-overlay" style="background-color: rgba(0,0,0,0.4); display: inline-block; padding: 30px 40px; border-radius: 8px;">
            <h1 style="font-size: 3em; margin-bottom: 15px; text-shadow: 2px 2px 4px #000000; font-weight: 700;">Bun Venit la Magazinul Nostru!</h1>
            <p style="font-size: 1.5em; margin-bottom: 30px; text-shadow: 1px 1px 3px #000000;">Calitate excepțională, prețuri imbatabile și o experiență de cumpărături unică.</p>
            <a href="<?php echo BASE_URL; ?>/public/products_list.php" class="button button-primary-hero" 
               style="background-color: #007bff; color: white; padding: 15px 35px; font-size: 1.2em; text-decoration: none; border-radius: 5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                Explorați Produsele
            </a>
        </div>
    </section>

    <!-- 2. Scurtă Prezentare a Magazinului / Despre Noi -->
    <section class="about-us-snippet container" style="margin-bottom: 40px; text-align: center; padding: 20px 0;">
        <h2 style="font-size: 2.2em; color: #333; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; display: inline-block;">Cine Suntem?</h2>
        <p style="font-size: 1.1em; line-height: 1.7; color: #555; max-width: 800px; margin: 0 auto 20px auto;">
            La [Numele Magazinului Tău], ne dedicăm să aducem clienților noștri cele mai inovatoare și calitative produse.
            Cu o pasiune pentru [domeniul tău de activitate] și un angajament ferm față de satisfacția clientului, 
            suntem aici pentru a vă oferi o experiență de cumpărături plăcută și eficientă.
        </p>
        <!-- Poți adăuga un link către o pagină "Despre Noi" mai detaliată -->
        <!-- <a href="#" class="button button-secondary">Află Mai Multe</a> -->
    </section>

    <!-- 3. Secțiunea "Produse Recomandate/Noi" -->
    <?php if (isset($home_page_error)): ?>
        <div class="container">
            <p class="flash-message error"><?php echo $home_page_error; ?></p>
        </div>
    <?php elseif (!empty($featured_products)): ?>
    <section class="featured-products-section container" style="margin-bottom: 40px;">
        <h2 style="text-align: center; font-size: 2.2em; color: #333; margin-bottom: 30px; border-bottom: 2px solid #007bff; padding-bottom: 10px; display: inline-block;"><?php echo escape($featured_products_title); ?></h2>
        
        <div class="product-list-condensed" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px;">
            <?php foreach ($featured_products as $product): ?>
                <article class="product-card-condensed" style="border: 1px solid #e0e0e0; border-radius: 6px; background: #fff; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 3px 10px rgba(0,0,0,0.07); transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <a href="<?php echo BASE_URL; ?>/public/product_detail.php?id=<?php echo escape($product['id']); ?>" style="text-decoration: none; color: inherit; display:block;">
                        <div class="product-image-wrapper" style="height: 220px; overflow: hidden;">
                            <?php if (!empty($product['main_photo_filename'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/products_thumbnails/' . escape($product['main_photo_filename']); ?>" 
                                     alt="<?php echo escape($product['name']); ?>" 
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #777;">
                                    <span>Imagine indisponibilă</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-content-wrapper" style="padding: 15px;">
                            <h3 style="margin-top: 0; font-size: 1.15em; color: #333; min-height: 45px; line-height:1.3; margin-bottom:8px;"><?php echo escape($product['name']); ?></h3>
                            <p class="price" style="font-weight: bold; color: #28a745; margin-bottom: 12px; font-size:1.25em;"><?php echo escape(number_format($product['price'], 2)); ?> RON</p>
                        </div>
                    </a>
                    <div style="padding: 0 15px 15px 15px; margin-top: auto;"> <!-- Container pentru buton, îl împinge în jos -->
                        <a href="<?php echo BASE_URL; ?>/public/product_detail.php?id=<?php echo escape($product['id']); ?>" class="button" 
                           style="display: block; text-align: center; width:100%; background-color: #007bff;">
                           Vezi Produs
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p style="text-align: center; margin-top: 30px;">
            <a href="<?php echo BASE_URL; ?>/public/products_list.php" class="button button-outline" 
               style="background-color: transparent; color: #007bff; border: 2px solid #007bff; padding: 12px 25px; font-weight: bold;">
               Vezi Toate Produsele
            </a>
        </p>
    </section>
    <?php endif; ?>

    <!-- 4. Alte Secțiuni (Opțional) -->
    <!-- De exemplu, un call to action pentru abonare la newsletter, link-uri către social media etc. -->
    <section class="cta-section container" style="background-color: #e9ecef; padding: 40px; text-align: center; border-radius: 5px; margin-bottom: 40px;">
        <h3 style="font-size: 1.8em; color: #333; margin-bottom: 15px;">Fii la Curent cu Ultimele Noutăți!</h3>
        <p style="margin-bottom: 25px; color: #555;">Abonează-te la newsletter-ul nostru pentru oferte exclusive și informații despre noile produse.</p>
        <form action="#" method="post" style="max-width: 400px; margin: 0 auto; display: flex;">
            <input type="email" name="newsletter_email" placeholder="Adresa ta de email" required style="flex-grow: 1; padding: 12px; border: 1px solid #ccc; border-radius: 4px 0 0 4px; border-right: none;">
            <button type="submit" class="button" style="border-radius: 0 4px 4px 0; padding: 12px 20px;">Abonează-te</button>
        </form>
    </section>

</div> <!-- Sfârșit .page-home -->

<?php
require_once __DIR__ . '/../includes/footer.php'; // Footer-ul public
?>cd 