<?php
/**
 * movies.php — список фильмов.
 *
 * Поддерживает необязательный фильтр ?category=slug (например, ?category=action).
 * Фильтрация выполняется подготовленным SQL-запросом, без подстановки строки в SQL.
 */

require 'config/config.php';

$category = trim((string)($_GET['category'] ?? ($_GET['cat'] ?? ''))); // cat — старый формат ссылок
$categoryName = '';
$params = [];

$sql = "SELECT c.* FROM content c
        LEFT JOIN categories cat ON cat.id = c.category_id
        WHERE c.type = 'movie'";

if ($category !== '') {
    $catStmt = $pdo->prepare('SELECT name FROM categories WHERE slug = ?');
    $catStmt->execute([$category]);
    $row = $catStmt->fetch();
    if ($row) {
        $categoryName = $row['name'];
        $sql .= ' AND cat.slug = ?';
        $params[] = $category;
    }
}
$sql .= ' ORDER BY c.created_at DESC, c.rating DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$pageTitle = ($categoryName !== '' ? 'Фильмы: ' . $categoryName : 'Все фильмы') . ' · NETFLIX';
require 'includes/header.php';
?>
<section class="section" style="padding-top:36px">
    <div class="section-head">
        <h2 class="section-title"><?= $categoryName !== '' ? 'Фильмы · ' . e($categoryName) : 'Все фильмы' ?></h2>
        <span class="section-link">
            <?= count($items) ?> шт. ·
            <a href="catalog.php?type=movie" class="section-link">фильтры каталога</a>
        </span>
    </div>

    <?php if ($items): ?>
        <div class="row">
            <?php foreach ($items as $item) echo render_card($item, $favIds); ?>
        </div>
    <?php else: ?>
        <p class="empty-state">В этой категории пока нет фильмов. <a href="movies.php">Показать все фильмы</a>.</p>
    <?php endif; ?>
</section>
<?php require 'includes/footer.php'; ?>
