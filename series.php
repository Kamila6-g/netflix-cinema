<?php
/**
 * series.php — список сериалов.
 *
 * Поддерживает фильтр ?category=slug так же, как movies.php.
 */

require 'config/config.php';

$category = trim((string)($_GET['category'] ?? ($_GET['cat'] ?? '')));
$categoryName = '';
$params = [];

$sql = "SELECT c.* FROM content c
        LEFT JOIN categories cat ON cat.id = c.category_id
        WHERE c.type = 'series'";

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

$pageTitle = ($categoryName !== '' ? 'Сериалы: ' . $categoryName : 'Все сериалы') . ' · NETFLIX';
require 'includes/header.php';
?>
<section class="section" style="padding-top:36px">
    <div class="section-head">
        <h2 class="section-title"><?= $categoryName !== '' ? 'Сериалы · ' . e($categoryName) : 'Все сериалы' ?></h2>
        <span class="section-link">
            <?= count($items) ?> шт. ·
            <a href="catalog.php?type=series" class="section-link">фильтры каталога</a>
        </span>
    </div>

    <?php if ($items): ?>
        <div class="row">
            <?php foreach ($items as $item) echo render_card($item, $favIds); ?>
        </div>
    <?php else: ?>
        <p class="empty-state">В этой категории пока нет сериалов. <a href="series.php">Показать все сериалы</a>.</p>
    <?php endif; ?>
</section>
<?php require 'includes/footer.php'; ?>
