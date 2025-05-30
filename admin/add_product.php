



<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container">
    <h2>Adăugare Produs Nou</h2>

    <form action="process_add_product.php" method="post" enctype="multipart/form-data">
        <div>
            <label for="name">Nume Produs:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div>
            <label for="short_description">Descriere Scurtă:</label>
            <textarea id="short_description" name="short_description"></textarea>
        </div>
        <div>
            <label for="detailed_description">Descriere Detaliată:</label>
            <textarea id="detailed_description" name="detailed_description"></textarea>
        </div>
        <div>
            <label for="price">Preț (RON):</label>
            <input type="number" id="price" name="price" step="0.01" required>
        </div>
        <div>
            <label for="main_photo">Fotografie Principală:</label>
            <input type="file" id="main_photo" name="main_photo" accept="image/jpeg, image/png, image/gif">
        </div>
        <div>
            <label for="secondary_photos">Fotografii Secundare (max 5):</label>
            <input type="file" id="secondary_photos" name="secondary_photos[]" multiple accept="image/jpeg, image/png, image/gif">
        </div>
        <button type="submit">Adaugă Produs</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
