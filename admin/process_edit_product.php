
<?php
require_once __DIR__ . '/../includes/header.php';

$originalUploadDir = __DIR__ . '/../uploads/products/';
$thumbnailUploadDir = __DIR__ . '/../uploads/products_thumbnails/';
$thumbnailWidth = 150;
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    if (!$product_id) {
        $_SESSION['flash_message'] = "ID produs invalid pentru actualizare.";
        $_SESSION['flash_type'] = 'error';
        header('Location: list_products_admin.php');
        exit;
    }

    // Preluare date produs existent pentru a gestiona fișierele vechi
    $stmt_old = $pdo->prepare("SELECT main_photo_filename FROM products WHERE id = :id");
    $stmt_old->execute([':id' => $product_id]);
    $old_product_data = $stmt_old->fetch();
    if (!$old_product_data) {
        $_SESSION['flash_message'] = "Produsul nu a fost găsit pentru actualizare.";
        $_SESSION['flash_type'] = 'error';
        header('Location: list_products_admin.php');
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $detailed_description = trim($_POST['detailed_description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    $errors = [];
    if (empty($name)) $errors[] = "Numele produsului este obligatoriu.";
    if ($price === false || $price <= 0) $errors[] = "Prețul este invalid.";

    $currentMainPhotoFilename = $old_product_data['main_photo_filename'];
    $newMainPhotoFilename = $currentMainPhotoFilename; // Implicit, păstrăm fotografia veche

    // Gestionare ștergere fotografie principală existentă
    if (isset($_POST['delete_main_photo']) && $currentMainPhotoFilename) {
        if (file_exists($originalUploadDir . $currentMainPhotoFilename)) unlink($originalUploadDir . $currentMainPhotoFilename);
        if (file_exists($thumbnailUploadDir . $currentMainPhotoFilename)) unlink($thumbnailUploadDir . $currentMainPhotoFilename);
        $newMainPhotoFilename = null; // Va fi null în DB
    }

    // Procesare înlocuire/adăugare fotografie principală nouă
    if (isset($_FILES['main_photo']) && $_FILES['main_photo']['error'] === UPLOAD_ERR_OK) {
        $mainPhotoTmpName = $_FILES['main_photo']['tmp_name'];
        $mainPhotoOrigName = $_FILES['main_photo']['name'];
        $mainPhotoExt = strtolower(pathinfo($mainPhotoOrigName, PATHINFO_EXTENSION));

        if (in_array($mainPhotoExt, $allowedExtensions)) {
            // Șterge vechea fotografie principală (dacă există și e diferită)
            if ($currentMainPhotoFilename && $newMainPhotoFilename !== null) { // Doar dacă nu am șters-o deja explicit
                if (file_exists($originalUploadDir . $currentMainPhotoFilename)) unlink($originalUploadDir . $currentMainPhotoFilename);
                if (file_exists($thumbnailUploadDir . $currentMainPhotoFilename)) unlink($thumbnailUploadDir . $currentMainPhotoFilename);
            }

            $uploadedMainPhotoFilename = uniqid('prod_main_', true) . '.' . $mainPhotoExt;
            $mainPhotoPath = $originalUploadDir . $uploadedMainPhotoFilename;
            $mainThumbnailPath = $thumbnailUploadDir . $uploadedMainPhotoFilename;

            if (move_uploaded_file($mainPhotoTmpName, $mainPhotoPath)) {
                if (resizeImage($mainPhotoPath, $mainThumbnailPath, $thumbnailWidth)) {
                    $newMainPhotoFilename = $uploadedMainPhotoFilename; // Noul nume de fișier
                } else {
                    $errors[] = "Eroare la redimensionarea noii imagini principale.";
                    // unlink($mainPhotoPath); // Opcional: șterge originalul dacă thumbnail-ul eșuează
                }
            } else {
                $errors[] = "Eroare la încărcarea noii imagini principale.";
            }
        } else {
            $errors[] = "Formatul noii imagini principale nu este permis.";
        }
    }


    // Gestionare ștergere fotografii secundare
    $photos_to_delete_ids = $_POST['delete_secondary_photos'] ?? [];
    if (!empty($photos_to_delete_ids)) {
        $placeholders = implode(',', array_fill(0, count($photos_to_delete_ids), '?'));
        $stmt_get_filenames = $pdo->prepare("SELECT filename FROM product_photos WHERE id IN ($placeholders) AND product_id = ?");
        $params = array_merge($photos_to_delete_ids, [$product_id]);
        $stmt_get_filenames->execute($params);
        $files_to_delete_db = $stmt_get_filenames->fetchAll(PDO::FETCH_COLUMN);

        foreach ($files_to_delete_db as $filename) {
            if (file_exists($originalUploadDir . $filename)) unlink($originalUploadDir . $filename);
            if (file_exists($thumbnailUploadDir . $filename)) unlink($thumbnailUploadDir . $filename);
        }
        $stmt_delete_photos = $pdo->prepare("DELETE FROM product_photos WHERE id IN ($placeholders) AND product_id = ?");
        $stmt_delete_photos->execute($params);
    }


    if (!empty($errors)) {
        $_SESSION['flash_message'] = implode("<br>", $errors);
        $_SESSION['flash_type'] = 'error';
        header('Location: edit_product.php?id=' . $product_id);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $sqlProductUpdate = "UPDATE products SET
                            name = :name,
                            short_description = :short_desc,
                            detailed_description = :detailed_desc,
                            price = :price,
                            main_photo_filename = :main_photo
                           WHERE id = :product_id";
        $stmtProductUpdate = $pdo->prepare($sqlProductUpdate);
        $stmtProductUpdate->execute([
            ':name' => $name,
            ':short_desc' => $short_description,
            ':detailed_desc' => $detailed_description,
            ':price' => $price,
            ':main_photo' => $newMainPhotoFilename, // Numele actualizat al fotografiei principale
            ':product_id' => $product_id
        ]);

        // Adăugare fotografii secundare noi
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
                                    ':product_id' => $product_id,
                                    ':filename' => $secondaryPhotoFilename
                                ]);
                            } else {
                                 $_SESSION['flash_message'] = (isset($_SESSION['flash_message']) ? $_SESSION['flash_message']."<br>" : "") . "Eroare redimensionare imagine secundară nouă: " . escape($origName);
                            }
                        } else {
                            $_SESSION['flash_message'] = (isset($_SESSION['flash_message']) ? $_SESSION['flash_message']."<br>" : "") . "Eroare încărcare imagine secundară nouă: " . escape($origName);
                        }
                    } else {
                         $_SESSION['flash_message'] = (isset($_SESSION['flash_message']) ? $_SESSION['flash_message']."<br>" : "") . "Format invalid imagine secundară nouă: " . escape($origName);
                    }
                }
            }
        }

        $pdo->commit();
        $_SESSION['flash_message'] = 'Produsul a fost actualizat cu succes!' . (isset($_SESSION['flash_message']) ? '<br>Notă: ' . $_SESSION['flash_message'] : '');
        if (isset($_SESSION['flash_message']) && !isset($_SESSION['flash_type'])) { /* if only image errors */ } else { $_SESSION['flash_type'] = 'success';}

        header('Location: list_products_admin.php');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['flash_message'] = 'Eroare la actualizarea produsului: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
        header('Location: edit_product.php?id=' . $product_id);
        exit;
    }
} else {
    header('Location: list_products_admin.php');
    exit;
}
?>
