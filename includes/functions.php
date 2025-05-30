<?php


// Funcție pentru redimensionarea imaginilor (necesită extensia GD)
function resizeImage($sourcePath, $destinationPath, $newWidth, $newHeight = null, $quality = 85) {
    list($width, $height, $type) = getimagesize($sourcePath);

    if ($newHeight === null) {
        $newHeight = ($height / $width) * $newWidth;
    }

    $thumb = imagecreatetruecolor($newWidth, $newHeight);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagejpeg($thumb, $destinationPath, $quality);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            imagealphablending($thumb, false); // Păstrează transparența
            imagesavealpha($thumb, true);
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagepng($thumb, $destinationPath, floor($quality/10-1)); // Calitatea PNG e 0-9
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagegif($thumb, $destinationPath);
            break;
        default:
            // Tip de imagine neacceptat
            imagedestroy($thumb);
            if (isset($source)) imagedestroy($source);
            return false;
    }

    imagedestroy($thumb);
    if (isset($source)) imagedestroy($source);
    return true;
}

// Funcție simplă pentru sanitizarea output-ului HTML
function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8', true);
}
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // Ajustează '/magazin_virtual' dacă:
    // - Proiectul este într-un alt subfolder al DocumentRoot-ului.
    // - DocumentRoot-ul Apache este setat direct la directorul proiectului (caz în care $project_subdir ar fi '').
    $project_subdir = ''; 
    define('BASE_URL', $protocol . $host . $project_subdir);
}

// Pentru debug, poți adăuga aici:
// echo "DEBUG functions.php: BASE_URL a fost definit ca: " . BASE_URL . "<br>";
?>
