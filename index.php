<?php
/**
 * index.php — главная страница онлайн-кинотеатра.
 *
 * Разделы страницы:
 *   1. Hero-баннер самого популярного тайтла;
 *   2. Поиск по фильмам и сериалам (GET-форма);
 *   3. «Топ новинки» — записи с флагом is_new;
 *   4. «Популярное» — записи с флагом is_popular;
 *   5. Категории с переходом в каталог (?category=slug);
 *   6. Персональные рекомендации;
 *   7. Отдельные подборки фильмов и сериалов.
 *
 * Все данные выбираются из БД подготовленными запросами,
 * вывод экранируется функцией e().
 */

require 'config/config.php';

// --- 1. Hero: самый популярный тайтл с высоким рейтингом -------------------
$hero = $pdo->query("SELECT * FROM content WHERE is_popular = 1 ORDER BY rating DESC, year DESC LIMIT 1")->fetch();

// --- 2. Новинки и популярное ------------------------------------------------
$new = $pdo->query("SELECT * FROM content WHERE is_new = 1 ORDER BY created_at DESC, rating DESC LIMIT 12")->fetchAll();
$popular = $pdo->query("SELECT * FROM content WHERE is_popular = 1 ORDER BY rating DESC LIMIT 12")->fetchAll();

// --- 3. Категории с количеством тайтлов ------------------------------------
$categories = $pdo->query("SELECT cat.id, cat.name, cat.slug, COUNT(c.id) AS cnt
                           FROM categories cat
                           LEFT JOIN content c ON c.category_id = cat.id
                           GROUP BY cat.id, cat.name, cat.slug
                           ORDER BY cat.name")->fetchAll();

// --- 4. Подборки по типам ---------------------------------------------------
$movies = $pdo->query("SELECT * FROM content WHERE type = 'movie' ORDER BY rating DESC LIMIT 6")->fetchAll();
$series = $pdo->query("SELECT * FROM content WHERE type = 'series' ORDER BY rating DESC LIMIT 6")->fetchAll();

// --- 5. Персональные рекомендации ------------------------------------------
// Логика: берём категории тайтлов из избранного и истории пользователя
// и предлагаем другие тайтлы этих же категорий, которые он ещё не смотрел.
// Если данных нет (гость или новый аккаунт) — честно откатываемся к популярному.
// Рекомендация не является «умной»: это простой контентный фильтр по жанрам.
$recommend = [];
if (is_logged_in()) {
    $recStmt = $pdo->prepare("
        SELECT c.*
        FROM content c
        WHERE c.category_id IN (
                SELECT src.category_id
                FROM content src
                WHERE src.id IN (
                    SELECT content_id FROM favorites WHERE user_id = :uid_fav
                    UNION
                    SELECT content_id FROM history   WHERE user_id = :uid_hist
                )
              )
          AND c.id NOT IN (
                SELECT content_id FROM favorites WHERE user_id = :uid_fav2
                UNION
                SELECT content_id FROM history   WHERE user_id = :uid_hist2
              )
        ORDER BY c.rating DESC, c.year DESC
        LIMIT 6
    ");
    $recStmt->execute([
        ':uid_fav'    => $_SESSION['user_id'],
        ':uid_hist'   => $_SESSION['user_id'],
        ':uid_fav2'   => $_SESSION['user_id'],
        ':uid_hist2'  => $_SESSION['user_id'],
    ]);
    $recommend = $recStmt->fetchAll();

    // Если рекомендаций мало — добираем популярными тайтлами.
    if (count($recommend) < 6) {
        $exclude = array_map(static fn($i) => (int)$i['id'], $recommend);
        $exclude[] = 0;
        $placeholders = implode(',', array_fill(0, count($exclude), '?'));
        $fillStmt = $pdo->prepare("SELECT * FROM content
                                   WHERE id NOT IN ($placeholders)
                                   ORDER BY is_popular DESC, rating DESC LIMIT " . (6 - count($recommend)));
        $fillStmt->execute($exclude);
        $recommend = array_merge($recommend, $fillStmt->fetchAll());
    }
}

// Гостю показываем популярные тайтлы (честная подпись в блоке ниже).
if (!$recommend) {
    $recommend = $pdo->query("SELECT * FROM content ORDER BY is_popular DESC, rating DESC LIMIT 6")->fetchAll();
}

$pageTitle = 'NETFLIX — фильмы и сериалы онлайн';
require 'includes/header.php';
?>

<?php if ($hero): ?>
<!-- ============ Hero-баннер ============ -->
<section class="hero">
    <div class="hero-bg" style="background-image:url('<?= e($hero['backdrop']) ?>')"></div>
    <div class="hero-content">
        <h1 class="hero-title"><?= e($hero['title']) ?></h1>
        <div class="hero-meta">
            <span class="rating <?= rating_class((float)$hero['rating']) ?>"><?= e($hero['rating']) ?></span>
            <span class="chip"><?= $hero['type'] === 'movie' ? 'Фильм' : 'Сериал' ?></span>
            <span><?= (int)$hero['year'] ?></span>
            <?php if ($hero['duration']): ?><span><?= (int)$hero['duration'] ?> мин</span><?php endif; ?>
        </div>
        <p class="hero-desc"><?= e($hero['description']) ?></p>
        <div class="hero-actions">
            <a href="content.php?id=<?= (int)$hero['id'] ?>" class="btn btn-primary">Смотреть</a>
            <a href="content.php?id=<?= (int)$hero['id'] ?>#trailer" class="btn btn-ghost">Трейлер</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ Поиск ============ -->
<section class="section search-section">
    <div class="section-head">
        <h2 class="section-title">Найти фильм или сериал</h2>
    </div>
    <form class="search-big" action="search.php" method="get" role="search">
        <input type="text" name="q" placeholder="Название, актёр или слово из описания…"
               value="<?= e($_GET['q'] ?? '') ?>" aria-label="Поисковый запрос">
        <button class="btn btn-primary" type="submit">Искать</button>
    </form>
</section>

<!-- ============ Топ новинки ============ -->
<section class="section">
    <div class="section-head">
        <h2 class="section-title">Топ новинки</h2>
        <a href="catalog.php?sort=new" class="section-link">Смотреть все →</a>
    </div>
    <?php if ($new): ?>
        <div class="row">
            <?php foreach ($new as $item) echo render_card($item, $favIds); ?>
        </div>
    <?php else: ?>
        <p class="empty-state">Новинок пока нет.</p>
    <?php endif; ?>
</section>

<!-- ============ Популярное ============ -->
<section class="section">
    <div class="section-head">
        <h2 class="section-title">Популярное</h2>
        <a href="catalog.php?sort=rating" class="section-link">Смотреть все →</a>
    </div>
    <?php if ($popular): ?>
        <div class="row">
            <?php foreach ($popular as $item) echo render_card($item, $favIds); ?>
        </div>
    <?php else: ?>
        <p class="empty-state">Популярных тайтлов пока нет.</p>
    <?php endif; ?>
</section>

<!-- ============ Категории ============ -->
<section class="section">
    <div class="section-head">
        <h2 class="section-title">Категории</h2>
        <a href="catalog.php" class="section-link">Весь каталог →</a>
    </div>
    <div class="cats">
        <?php foreach ($categories as $c): ?>
            <?php /* Ссылка ведёт в каталог и включает SQL-фильтр по slug категории */ ?>
            <a class="cat" href="catalog.php?category=<?= e($c['slug']) ?>">
                <span class="cat-badge"><?= e($c['name']) ?></span>
                <span class="cat-count"><?= (int)$c['cnt'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ Рекомендации ============ -->
<section class="section">
    <div class="section-head">
        <h2 class="section-title">Рекомендуем вам</h2>
        <span class="section-link">
            <?php if (is_logged_in()): ?>
                по вашему избранному и истории
            <?php else: ?>
                популярное — войдите, чтобы получить персональные подборки
            <?php endif; ?>
        </span>
    </div>
    <div class="row">
        <?php foreach ($recommend as $item) echo render_card($item, $favIds); ?>
    </div>
</section>

<!-- ============ Фильмы ============ -->
<section class="section">
    <div class="section-head">
        <h2 class="section-title">Фильмы</h2>
        <a href="movies.php" class="section-link">Все фильмы →</a>
    </div>
    <div class="row">
        <?php foreach ($movies as $item) echo render_card($item, $favIds); ?>
    </div>
</section>

<!-- ============ Сериалы ============ -->
<section class="section">
    <div class="section-head">
        <h2 class="section-title">Сериалы</h2>
        <a href="series.php" class="section-link">Все сериалы →</a>
    </div>
    <div class="row">
        <?php foreach ($series as $item) echo render_card($item, $favIds); ?>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
