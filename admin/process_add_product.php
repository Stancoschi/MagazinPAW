
<?php
require_once __DIR__ . '/../includes/header.php'; // Include db.php, functions.php, session_start()

// Definește căile către directoarele de upload
// Asigură-te că aceste directoare există și au permisiuni de scriere pentru serverul web (www-data)
// De exemplu, din rădăcina proiectului:
// mkdir -p uploads/products uploads/products_thumbnails
// sudo chown -R www-data:www-data uploads
// sudo chmod -R 775 uploads
// Path-ul relativ la directorul scriptului curent (__DIR__)
$originalUploadDir = __DIR__ . '/../uploads/products/';
$thumbnailUploadDir = __DIR__ . '/../uploads/products_thumbnails/';
$thumbnailWidth = 150; // Lățimea dorită pentru thumbnail-uri

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prelucrare date formular
    $name = trim($_POST['name'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $detailed_description = trim($_POST['detailed_description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    // Validare simplă
    $errors = [];
    if (empty($name)) {
        $errors[] = "Numele produsului este obligatoriu.";
    }
    if ($price === false || $price <= 0) {
        $errors[] = "Prețul este invalid.";
    }

    // Procesare imagine principală
    $mainPhotoFilename = null;
    if (isset($_FILES['main_photo']) && $_FILES['main_photo']['error'] === UPLOAD_ERR_OK) {
        $mainPhotoTmpName = $_FILES['main_photo']['tmp_name'];
        $mainPhotoOrigName = $_FILES['main_photo']['name'];
        $mainPhotoExt = strtolower(pathinfo($mainPhotoOrigName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($mainPhotoExt, $allowedExtensions)) {
            $mainPhotoFilename = uniqid('prod_main_', true) . '.' . $mainPhotoExt;
            $mainPhotoPath = $originalUploadDir . $mainPhotoFilename;
            $mainThumbnailPath = $thumbnailUploadDir . $mainPhotoFilename;

            if (move_uploaded_file($mainPhotoTmpName, $mainPhotoPath)) {
                // Creează thumbnail pentru imaginea principală
                if (!resizeImage($mainPhotoPath, $mainThumbnailPath, $thumbnailWidth)) {
                     $errors[] = "Eroare la redimensionarea imaginii principale.";
                     // Poți decide să ștergi fișierul original dacă thumbnail-ul eșuează
                     // unlink($mainPhotoPath); $mainPhotoFilename = null;
                }
            } else {
                $errors[] = "Eroare la încărcarea imaginii principale.";
                $mainPhotoFilename = null;
            }
        } else {
            $errors[] = "Formatul imaginii principale nu este permis.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['flash_message'] = implode("<br>", $errors);
        $_SESSION['flash_type'] = 'error';
        // Poți salva și datele introduse în sesiune pentru a repopula formularul
        // $_SESSION['form_data'] = $_POST;
        header('Location: add_product.php');
        exit;
    }

    // Inserare în baza de date
    try {
        $pdo->beginTransaction();

        $sqlProduct = "INSERT INTO products (name, short_description, detailed_description, price, main_photo_filename)
                       VALUES (:name, :short_desc, :detailed_desc, :price, :main_photo)";
        $stmtProduct = $pdo->prepare($sqlProduct);
        $stmtProduct->execute([
            ':name' => $name,
            ':short_desc' => $short_description,
            ':detailed_desc' => $detailed_description,
            ':price' => $price,
            ':main_photo' => $mainPhotoFilename // Salvează numele fișierului imaginii principale
        ]);
        $productId = $pdo->lastInsertId();

        // Procesare imagini secundare (dacă există)
        if (isset($_FILES['secondary_photos'])) {
            $sqlPhoto = "INSERT INTO product_photos (product_id, filename, is_main) VALUES (:product_id, :filename, 0)";
            $stmtPhoto = $pdo->prepare($sqlPhoto);

            foreach ($_FILES['secondary_photos']['name'] as $key => $origName) {
                if ($_FILES['secondary_photos']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['secondary_photos']['tmp_name'][$key];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                    if (in_array($ext, $allowedExtensions)) {
                        $secondaryPhotoFilename = uniqid('prod_sec_', true) . '.' . $ext;
                        $secondaryPhotoPath = $originalUploadDir . $secondaryPhotoFilename;
                        $secondaryThumbnailPath = $thumbnailUploadDir . $secondaryPhotoFilename;

                        if (move_uploaded_file($tmpName, $secondaryPhotoPath)) {
                            if (resizeImage($secondaryPhotoPath, $secondaryThumbnailPath, $thumbnailWidth)) {
                                $stmtPhoto->execute([
                                    ':product_id' => $productId,
                                    ':filename' => $secondaryPhotoFilename
                                ]);
                            } else {
                                 $_SESSION['flash_message'] = (isset($_SESSION['flash_message']) ? $_SESSION['flash_message']."<br>" : "") . "Eroare redimensionare imagine secundară: " . escape($origName);
                                 // unlink($secondaryPhotoPath); // Option: delete original if thumbnail fails
                            }
                        } else {
                            $_SESSION['flash_message'] = (isset($_SESSION['flash_message']) ? $_SESSION['flash_message']."<br>" : "") . "Eroare încărcare imagine secundară: " . escape($origName);
                        }
                    } else {
                         $_SESSION['flash_message'] = (isset($_SESSION['flash_message']) ? $_SESSION['flash_message']."<br>" : "") . "Format invalid imagine secundară: " . escape($origName);
                    }
                }
            }
        }

        $pdo->commit();
        $_SESSION['flash_message'] = 'Produsul a fost adăugat cu succes!';
        $_SESSION['flash_type'] = 'success';
        header('Location: list_products_admin.php'); // Sau add_product.php dacă vrei să adaugi altul
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['flash_message'] = 'Eroare la salvarea produsului în baza de date: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
        // Log $e->getMessage() for debugging
        header('Location: add_product.php');
        exit;
    }
} else {
    // Nu este POST, redirecționează
    header('Location: add_product.php');
    exit;
}
?>
