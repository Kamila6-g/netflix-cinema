<?php
/**
 * catalog.php — общий каталог фильмов и сериалов с фильтрами.
 *
 * Поддерживаемые GET-параметры:
 *   category — slug категории (drama, comedy, horror …);
 *   type     — movie | series;
 *   sort     — rating | new | title;
 *   page     — номер страницы (по 12 карточек).
 *
 * Фильтры объединяются в один подготовленный SQL-запрос,
 * параметры передаются отдельно — SQL-инъекция невозможна.
 */

require 'config/config.php';

// --- Разбор и валидация фильтров -------------------------------------------
$category = trim((string)($_GET['category'] ?? ''));
$type     = (string)($_GET['type'] ?? '');
$sort     = (string)($_GET['sort'] ?? 'rating');
$page     = max(1, (int)($_GET['page'] ?? 1));

$allowedTypes = ['movie', 'series'];
if (!in_array($type, $allowedTypes, true)) {
    $type = '';
}

$sortMap = [
    'rating' => 'c.rating DESC, c.year DESC',
    'new'    => 'c.created_at DESC, c.id DESC',
    'title'  => 'c.title ASC',
];
if (!isset($sortMap[$sort])) {
    $sort = 'rating';
}
$orderBy = $sortMap[$sort];

// Категория проверяется по БД: если slug неизвестен — фильтр игнорируется.
$categoryName = '';
$categoryId = 0;
if ($category !== '') {
    $catStmt = $pdo->prepare('SELECT id, name FROM categories WHERE slug = ?');
    $catStmt->execute([$category]);
    if ($catRow = $catStmt->fetch()) {
        $categoryId = (int)$catRow['id'];
        $categoryName = $catRow['name'];
    } else {
        $category = '';
    }
}

// --- Сборка WHERE ----------------------------------------------------------
$where = [];
$params = [];

if ($categoryId > 0) {
    $where[] = 'c.category_id = :category_id';
    $params[':category_id'] = $categoryId;
}
if ($type !== '') {
    $where[] = 'c.type = :type';
    $params[':type'] = $type;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// --- Количество и страница --------------------------------------------------
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM content c $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$perPage = 12;
$pages = max(1, (int)ceil($total / $perPage));
if ($page > $pages) {
    $page = $pages;
}
$offset = ($page - 1) * $perPage;

$sql = "SELECT c.*, cat.name AS cat_name, cat.slug AS cat_slug
        FROM content c
        LEFT JOIN categories cat ON cat.id = c.category_id
        $whereSql
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

/** Ссылка с сохранением текущих фильтров (для пагинации). */
function catalog_url(array $override = []): string
{
    $query = array_merge($_GET, $override);
    $query = array_filter($query, static fn($v) => $v !== '' && $v !== null);
    return 'catalog.php' . ($query ? '?' . http_build_query($query) : '');
}

// Заголовок страницы зависит от выбранных фильтров.
if ($categoryName !== '') {
    $title = $categoryName;
} elseif ($type === 'movie') {
    $title = 'Все фильмы';
} elseif ($type === 'series') {
    $title = 'Все сериалы';
} else {
    $title = 'Каталог';
}

$pageTitle = $title . ' · NETFLIX';
require 'includes/header.php';
?>
<section class="section" style="padding-top:36px">
    <div class="section-head">
        <h2 class="section-title"><?= e($title) ?></h2>
        <span class="section-link"><?= $total ?> шт.</span>
    </div>

    <!-- Фильтры каталога: тип, сортировка, сброс -->
    <form class="filters" method="get" action="catalog.php">
        <?php if ($category !== ''): ?>
            <input type="hidden" name="category" value="<?= e($category) ?>">
        <?php endif; ?>

        <label>Тип
            <select name="type" onchange="this.form.submit()">
                <option value=""        <?= $type === '' ? 'selected' : '' ?>>Все</option>
                <option value="movie"   <?= $type === 'movie' ? 'selected' : '' ?>>Фильмы</option>
                <option value="series"  <?= $type === 'series' ? 'selected' : '' ?>>Сериалы</option>
            </select>
        </label>

        <label>Сортировка
            <select name="sort" onchange="this.form.submit()">
                <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>По рейтингу</option>
                <option value="new"    <?= $sort === 'new' ? 'selected' : '' ?>>Сначала новые</option>
                <option value="title"  <?= $sort === 'title' ? 'selected' : '' ?>>По алфавиту</option>
            </select>
        </label>

        <noscript><button class="btn btn-ghost" type="submit">Применить</button></noscript>

        <?php if ($category !== '' || $type !== '' || $sort !== 'rating'): ?>
            <a class="filter-reset" href="catalog.php">Сбросить фильтры</a>
        <?php endif; ?>
    </form>

    <?php if ($items): ?>
        <div class="row">
            <?php foreach ($items as $item) echo render_card($item, $favIds); ?>
        </div>

        <?php if ($pages > 1): ?>
            <!-- Пагинация: ссылки сохраняют выбранную категорию и сортировку -->
            <nav class="pagination" aria-label="Страницы каталога">
                <?php if ($page > 1): ?>
                    <a href="<?= e(catalog_url(['page' => $page - 1])) ?>">← Назад</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= e(catalog_url(['page' => $i])) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $pages): ?>
                    <a href="<?= e(catalog_url(['page' => $page + 1])) ?>">Вперёд →</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <p class="empty-state">
            В этой подборке пока нет тайтлов.
            <a href="catalog.php">Показать весь каталог</a>.
        </p>
    <?php endif; ?>
</section>
<?php require 'includes/footer.php'; ?>
