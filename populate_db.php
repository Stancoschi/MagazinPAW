<?php
// Rulează din linia de comandă: php populate_db.php
// Asigură-te că ai rulat 'composer require fakerphp/faker' în directorul proiectului.

require_once __DIR__ . '/vendor/autoload.php'; // Dacă folosești Composer
require_once __DIR__ . '/includes/db.php';     // Conexiunea la BD
require_once __DIR__ . '/includes/functions.php'; // Pentru resizeImage
use Faker\Factory;
// Setează căile pentru imagini
$placeholderImageDir = __DIR__ . '/sample_images/'; // Creează acest director și pune câteva imagini .jpg, .png
$originalUploadDir = __DIR__ . '/uploads/products/';
$thumbnailUploadDir = __DIR__ . '/uploads/products_thumbnails/';
$thumbnailWidth = 150;

// Creează directoarele de upload dacă nu există
if (!is_dir($originalUploadDir)) mkdir($originalUploadDir, 0775, true);
if (!is_dir($thumbnailUploadDir)) mkdir($thumbnailUploadDir, 0775, true);

// Creează directorul sample_images și adaugă câteva imagini (ex: image1.jpg, image2.png etc.)
// Puteți descărca imagini placeholder de pe unsplash.com, pexels.com etc.
// Asigură-te că ai câteva imagini în $placeholderImageDir
if (!is_dir($placeholderImageDir)) mkdir($placeholderImageDir, 0755, true);
$sampleImages = glob($placeholderImageDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

if (empty($sampleImages)) {
    die("EROARE: Nu au fost găsite imagini în directorul '{$placeholderImageDir}'.\nAdăugați câteva imagini (jpg, png, gif) și reîncercați.\n");
}

$faker = \Faker\Factory::create('ro_RO'); // Generează date în format românesc

$numberOfProducts = 150; // Setează la 100 sau 300+
$maxSecondaryPhotosPerProduct = 4;

echo "Se populează baza de date...\\n";

try {
    $pdo->beginTransaction();

    // 1. Adaugă tipuri de caracteristici
    $characteristicTypes = ['Culoare', 'Mărime', 'Material', 'Greutate', 'Dimensiuni (LxWxH)', 'Garanție', 'Producător'];
    $characteristicColors = ['Roșu', 'Albastru', 'Verde', 'Galben', 'Negru', 'Alb', 'Gri', 'Portocaliu', 'Mov'];
    $characteristicSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '36', '38', '40', '42', '44', 'Universală'];
    $characteristicMaterials = ['Bumbac', 'Poliester', 'Lână', 'Mătase', 'Plastic', 'Metal', 'Lemn', 'Piele naturală', 'Piele ecologică'];

    $charIds = [];
    $stmtChar = $pdo->prepare("INSERT INTO characteristics (name) VALUES (:name) ON DUPLICATE KEY UPDATE name=name"); // Evită erori dacă rulezi de mai multe ori
    foreach ($characteristicTypes as $typeName) {
        $stmtChar->execute([':name' => $typeName]);
        // Nu putem lua lastInsertId() fiabil cu ON DUPLICATE KEY UPDATE fără alt query, deci le preluăm pe toate după
    }
    // Preluăm ID-urile caracteristicilor adăugate/existente
    $stmtGetChars = $pdo->query("SELECT id, name FROM characteristics");
    $allDbCharacteristics = $stmtGetChars->fetchAll(PDO::FETCH_KEY_PAIR); // [id => name]
    // Inversăm pentru a căuta ușor ID-ul după nume
    $charNameToId = array_flip($allDbCharacteristics);


    // 2. Adaugă produse
    $stmtProduct = $pdo->prepare("INSERT INTO products (name, short_description, detailed_description, price, main_photo_filename) VALUES (:name, :short_desc, :detailed_desc, :price, :main_photo)");
    $stmtPhoto = $pdo->prepare("INSERT INTO product_photos (product_id, filename, is_main) VALUES (:product_id, :filename, :is_main)");
    $stmtProdCharVal = $pdo->prepare("INSERT INTO product_characteristics_values (product_id, characteristic_id, value) VALUES (:product_id, :characteristic_id, :value)");

    for ($i = 0; $i < $numberOfProducts; $i++) {
        $productName = $faker->productName . ' ' . $faker->colorName . ' ' . $faker->numberBetween(100, 2000);
        $shortDesc = $faker->sentence(10);
        $detailedDesc = $faker->paragraphs(3, true);
        $price = $faker->randomFloat(2, 10, 5000);

        // Adaugă imagine principală
        $mainPhotoFilename = null;
        $randomImageSource = $sampleImages[array_rand($sampleImages)];
        $ext = strtolower(pathinfo($randomImageSource, PATHINFO_EXTENSION));
        $mainPhotoFilename = uniqid('prod_main_pop_', true) . '.' . $ext;
        $mainPhotoPath = $originalUploadDir . $mainPhotoFilename;
        $mainThumbnailPath = $thumbnailUploadDir . $mainPhotoFilename;

        if (copy($randomImageSource, $mainPhotoPath)) {
            resizeImage($mainPhotoPath, $mainThumbnailPath, $thumbnailWidth);
        } else {
            $mainPhotoFilename = null; // Nu s-a putut copia
        }

        $stmtProduct->execute([
            ':name' => $productName,
            ':short_desc' => $shortDesc,
            ':detailed_desc' => $detailedDesc,
            ':price' => $price,
            ':main_photo' => $mainPhotoFilename
        ]);
        $productId = $pdo->lastInsertId();

        // Adaugă imagini secundare (0 până la $maxSecondaryPhotosPerProduct)
        $numSecondary = $faker->numberBetween(0, $maxSecondaryPhotosPerProduct);
        for ($j = 0; $j < $numSecondary; $j++) {
            $randomImageSourceSec = $sampleImages[array_rand($sampleImages)];
            $extSec = strtolower(pathinfo($randomImageSourceSec, PATHINFO_EXTENSION));
            $secondaryPhotoFilename = uniqid('prod_sec_pop_', true) . '.' . $extSec;
            $secondaryPhotoPath = $originalUploadDir . $secondaryPhotoFilename;
            $secondaryThumbnailPath = $thumbnailUploadDir . $secondaryPhotoFilename;

            if (copy($randomImageSourceSec, $secondaryPhotoPath)) {
                resizeImage($secondaryPhotoPath, $secondaryThumbnailPath, $thumbnailWidth);
                $stmtPhoto->execute([
                    ':product_id' => $productId,
                    ':filename' => $secondaryPhotoFilename,
                    ':is_main' => 0
                ]);
            }
        }

        // Adaugă caracteristici aleatorii produsului (3-5 caracteristici per produs)
        $numCharsForProduct = $faker->numberBetween(2, 5);
        $shuffledCharTypes = $characteristicTypes; // Copie
        shuffle($shuffledCharTypes); // Amestecă

        for ($k = 0; $k < $numCharsForProduct && $k < count($shuffledCharTypes); $k++) {
            $charTypeName = $shuffledCharTypes[$k];
            $charId = $charNameToId[$charTypeName] ?? null;

            if ($charId) {
                $charValue = "N/A";
                switch ($charTypeName) {
                    case 'Culoare': $charValue = $characteristicColors[array_rand($characteristicColors)]; break;
                    case 'Mărime': $charValue = $characteristicSizes[array_rand($characteristicSizes)]; break;
                    case 'Material': $charValue = $characteristicMaterials[array_rand($characteristicMaterials)]; break;
                    case 'Greutate': $charValue = $faker->randomFloat(2, 0.1, 50) . ' kg'; break;
                    case 'Dimensiuni (LxWxH)': $charValue = $faker->numberBetween(10,100).'x'.$faker->numberBetween(10,100).'x'.$faker->numberBetween(5,50).' cm'; break;
                    case 'Garanție': $charValue = $faker->numberBetween(1, 5) . ' ani'; break;
                    case 'Producător': $charValue = $faker->company; break;
                }
                try {
                     $stmtProdCharVal->execute([
                        ':product_id' => $productId,
                        ':characteristic_id' => $charId,
                        ':value' => $charValue
                    ]);
                } catch(PDOException $e) {
                    // Ignoră erorile de cheie duplicată dacă o caracteristică e aleasă de 2 ori (puțin probabil cu shuffle)
                    if($e->getCode() != 23000) throw $e;
                }
            }
        }
        if (($i + 1) % 10 == 0) {
            echo "Adăugat produs " . ($i + 1) . "/" . $numberOfProducts . "\\n";
        }
    }

    $pdo->commit();
    echo "\\nBaza de date a fost populată cu succes cu {$numberOfProducts} produse!\\n";
    echo "Au fost create și imaginile asociate în directoarele 'uploads/'.\\n";

} catch (PDOException $e) {
    $pdo->rollBack();
    die("EROARE la popularea bazei de date: " . $e->getMessage() . "\\n");
} catch (Exception $e) {
    die("EROARE generală: " . $e->getMessage() . "\\n");
}

?>
