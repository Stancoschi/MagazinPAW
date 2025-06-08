
<?php
require_once __DIR__ . '/../includes/header.php';

// Preluare caracteristici existente
try {
    $stmt = $pdo->query("SELECT id, name FROM characteristics ORDER BY name ASC");
    $characteristics = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Eroare la preluarea caracteristicilor: " . $e->getMessage());
}
?>
<div class="container">
    <h2>Gestionare Tipuri de Caracteristici</h2>

    <form action="process_manage_characteristics.php" method="post" style="margin-bottom: 20px;">
        <input type="hidden" name="action" value="add">
        <div>
            <label for="char_name">Nume Caracteristică Nouă (ex: Culoare, Mărime):</label>
            <input type="text" id="char_name" name="char_name" required>
        </div>
        <button type="submit">Adaugă Tip Caracteristică</button>
    </form>

    <h3>Tipuri de Caracteristici Existente</h3>
    <?php if (empty($characteristics)): ?>
        <p>Nu există tipuri de caracteristici definite.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nume</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($characteristics as $char): ?>
                <tr>
                    <td><?php echo escape($char['id']); ?></td>
                    <td><?php echo escape($char['name']); ?></td>
                    <td>
                       
                         <a href="process_manage_characteristics.php?action=delete&id=<?php echo $char['id']; ?>"
                           onclick="return confirm('Sigur doriți să ștergeți acest tip de caracteristică? Aceasta va șterge și toate valorile asociate la produse!');"
                           class="button delete">Șterge</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
