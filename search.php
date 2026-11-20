<?php
/**
 * search.php — поиск по фильмам и сериалам.
 *
 * Запрос приходит методом GET (?q=...). Ищем по:
 *   - названию тайтла;
 *   - описанию;
 *   - имени актёра или режиссёра.
 *
 * Спецсимволы LIKE (% и _) экранируются, поэтому запрос «100%» не ломает выборку.
 * Пустой и отсутствующий запрос обрабатываются отдельными сообщениями.
 */

require 'config/config.php';

$q = trim((string)($_GET['q'] ?? ''));
$q = mb_substr($q, 0, 100);          // ограничение длины запроса
$items = [];

if ($q !== '') {
    // Экранируем спецсимволы шаблона LIKE, чтобы они искались буквально.
    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';

    $stmt = $pdo->prepare("
        SELECT c.*,
               CASE WHEN c.title LIKE :title_exact ESCAPE '\\\\' THEN 0 ELSE 1 END AS relevance
        FROM content c
        WHERE c.title LIKE :title ESCAPE '\\\\'
           OR c.description LIKE :descr ESCAPE '\\\\'
           OR EXISTS (
                SELECT 1
                FROM content_people cp
                JOIN people p ON p.id = cp.person_id
                WHERE cp.content_id = c.id AND p.name LIKE :person ESCAPE '\\\\'
           )
        ORDER BY relevance, c.rating DESC, c.year DESC
    ");
    $stmt->execute([
        ':title_exact' => $like,
        ':title'       => $like,
        ':descr'       => $like,
        ':person'      => $like,
    ]);
    $items = $stmt->fetchAll();
}

$pageTitle = ($q !== '' ? 'Поиск: ' . $q : 'Поиск') . ' · NETFLIX';
require 'includes/header.php';
?>
<section class="section" style="padding-top:36px">
    <div class="section-head">
        <h2 class="section-title">
            <?= $q !== '' ? 'Результаты поиска: «' . e($q) . '»' : 'Поиск по каталогу' ?>
        </h2>
        <?php if ($q !== ''): ?>
            <span class="section-link"><?= count($items) ?> найдено</span>
        <?php endif; ?>
    </div>

    <!-- Форма остаётся на странице, чтобы можно было уточнить запрос -->
    <form class="search-big" action="search.php" method="get" role="search" style="margin-bottom:26px">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Название, актёр или слово из описания…"
               aria-label="Поисковый запрос" autofocus>
        <button class="btn btn-primary" type="submit">Искать</button>
    </form>

    <?php if ($q === ''): ?>
        <p class="empty-state">Введите название фильма или сериала, имя актёра или режиссёра.</p>
    <?php elseif ($items): ?>
        <div class="row">
            <?php foreach ($items as $item) echo render_card($item, $favIds); ?>
        </div>
    <?php else: ?>
        <p class="empty-state">
            По запросу «<?= e($q) ?>» ничего не найдено. Попробуйте другое слово
            или <a href="catalog.php">откройте каталог</a>.
        </p>
    <?php endif; ?>
</section>
<?php require 'includes/footer.php'; ?>
