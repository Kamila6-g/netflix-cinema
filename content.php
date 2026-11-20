<?php
/**
 * content.php — типовая страница фильма или сериала.
 *
 * Одна страница обслуживает оба типа (movie / series), потому что набор данных
 * одинаковый, различаются только блоки:
 *   - фильм: описание, оценки, режиссёры и актёры, трейлер, похожие фильмы;
 *   - сериал: всё то же + сезоны и эпизоды, похожие сериалы.
 *
 * Все данные (описание, люди, эпизоды, отзывы, похожее) выбираются из БД.
 * Отзыв сохраняется только после входа и проверки CSRF-токена.
 */

require 'config/config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: catalog.php');
    exit;
}

// --- Основная запись + категория ------------------------------------------
$stmt = $pdo->prepare("SELECT c.*, cat.name AS cat_name, cat.slug AS cat_slug
                       FROM content c
                       LEFT JOIN categories cat ON cat.id = c.category_id
                       WHERE c.id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    // Нет записи — честный 404 с шапкой сайта.
    http_response_code(404);
    require 'includes/error-404.php';
    exit;
}

$favIds = get_fav_ids($pdo);
$isFav = in_array($id, $favIds, true);

// --- Актёры и режиссёры ----------------------------------------------------
// Роль (актёр/режиссёр) хранится в people.role, связь — в content_people.
$ppl = $pdo->prepare("SELECT p.*
                      FROM people p
                      JOIN content_people cp ON cp.person_id = p.id
                      WHERE cp.content_id = ?
                      ORDER BY p.name");
$ppl->execute([$id]);
$people = $ppl->fetchAll();
$actors = array_values(array_filter($people, static fn($p) => $p['role'] === 'actor'));
$directors = array_values(array_filter($people, static fn($p) => $p['role'] === 'director'));

// --- Сезоны и эпизоды (только для сериалов) --------------------------------
$episodes = [];
if ($item['type'] === 'series') {
    $ep = $pdo->prepare("SELECT * FROM episodes WHERE content_id = ? ORDER BY season, episode");
    $ep->execute([$id]);
    foreach ($ep->fetchAll() as $row) {
        $episodes[(int)$row['season']][] = $row;
    }
}

// --- Похожие тайтлы: та же категория и тот же тип --------------------------
$similar = $pdo->prepare("SELECT * FROM content
                          WHERE category_id = ? AND type = ? AND id <> ?
                          ORDER BY rating DESC LIMIT 6");
$similar->execute([$item['category_id'], $item['type'], $id]);
$similarItems = $similar->fetchAll();

// Если в категории мало записей — добираем любыми того же типа.
if (count($similarItems) < 3) {
    $fallback = $pdo->prepare("SELECT * FROM content
                               WHERE type = ? AND id <> ? AND id NOT IN (" .
                               implode(',', array_fill(0, max(1, count($similarItems)), '?')) . ")
                               ORDER BY rating DESC LIMIT 6");
    $params = array_merge([$item['type'], $id], array_map(static fn($s) => (int)$s['id'], $similarItems) ?: [0]);
    $fallback->execute($params);
    $similarItems = array_merge($similarItems, $fallback->fetchAll());
    $similarItems = array_slice($similarItems, 0, 6);
}

// --- Отзывы и средняя оценка зрителей --------------------------------------
$rev = $pdo->prepare("SELECT r.*, u.username
                      FROM reviews r JOIN users u ON u.id = r.user_id
                      WHERE r.content_id = ?
                      ORDER BY r.created_at DESC");
$rev->execute([$id]);
$reviews = $rev->fetchAll();

$avgStmt = $pdo->prepare("SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE content_id = ?");
$avgStmt->execute([$id]);
$avg = $avgStmt->fetch();
$userAvg = $avg && $avg['cnt'] > 0 ? (float)$avg['avg_rating'] : null;

// --- Обработка нового отзыва ------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    require_login();          // гость уйдёт на вход
    csrf_verify();            // защита от подделки формы

    $rating = (int)($_POST['rating'] ?? 0);
    $text = trim((string)($_POST['text'] ?? ''));

    if ($rating < 1 || $rating > 10) {
        flash('Оценка должна быть от 1 до 10.', 'error');
    } elseif ($text === '' || mb_strlen($text) > 1000) {
        flash('Отзыв должен быть от 1 до 1000 символов.', 'error');
    } else {
        $ins = $pdo->prepare('INSERT INTO reviews (user_id, content_id, rating, text) VALUES (?, ?, ?, ?)');
        $ins->execute([$_SESSION['user_id'], $id, $rating, $text]);
        flash('Спасибо за отзыв!');
    }
    header('Location: content.php?id=' . $id . '#reviews');
    exit;
}

// --- История просмотров: одна запись на пару пользователь+тайтл ------------
// Запись в историю не должна ломать показ страницы: если аккаунт удалили
// между проверкой сессии и этим запросом, просто пропускаем шаг и пишем лог.
if (is_logged_in()) {
    try {
        $h = $pdo->prepare("INSERT INTO history (user_id, content_id, watched_at) VALUES (?, ?, NOW())
                            ON DUPLICATE KEY UPDATE watched_at = NOW()");
        $h->execute([$_SESSION['user_id'], $id]);
    } catch (PDOException $e) {
        error_log('[history] ' . $e->getMessage());
    }
}

$pageTitle = $item['title'] . ' · NETFLIX';
$pageDescription = mb_substr((string)$item['description'], 0, 160);
require 'includes/header.php';
?>

<!-- ============ Шапка тайтла ============ -->
<div class="content-hero" style="--bg-img:url('<?= e($item['backdrop']) ?>')">
    <div class="content-hero-bg" style="position:absolute;inset:-40px -50vw 0;z-index:0;
        background-image:url('<?= e($item['backdrop']) ?>');background-size:cover;
        background-position:center;filter:brightness(.22) blur(2px)"></div>

    <div class="poster-big">
        <img src="<?= e($item['poster']) ?>" alt="Постер «<?= e($item['title']) ?>»">
    </div>

    <div class="content-info">
        <p class="breadcrumbs">
            <a href="index.php">Главная</a> /
            <?php if ($item['cat_name']): ?>
                <a href="catalog.php?category=<?= e($item['cat_slug']) ?>"><?= e($item['cat_name']) ?></a> /
            <?php endif; ?>
            <span><?= e($item['title']) ?></span>
        </p>

        <h1><?= e($item['title']) ?></h1>

        <div class="meta-row">
            <span class="rating <?= rating_class((float)$item['rating']) ?>"><?= e($item['rating']) ?>/10</span>
            <?php if ($userAvg !== null): ?>
                <span class="rating mid">Зрители: <?= e(number_format($userAvg, 1, ',', '')) ?> (<?= (int)$avg['cnt'] ?>)</span>
            <?php endif; ?>
            <span><?= (int)$item['year'] ?></span>
            <span class="chip"><?= $item['type'] === 'movie' ? 'Фильм' : 'Сериал' ?></span>
            <?php if ($item['cat_name']): ?>
                <a href="catalog.php?category=<?= e($item['cat_slug']) ?>" class="chip"><?= e($item['cat_name']) ?></a>
            <?php endif; ?>
            <?php if ($item['duration']): ?><span><?= (int)$item['duration'] ?> мин</span><?php endif; ?>
            <?php if ($item['type'] === 'series' && $episodes): ?>
                <span><?= count($episodes) ?> сезон(а/ов)</span>
            <?php endif; ?>
        </div>

        <p class="content-desc"><?= e($item['description']) ?></p>

        <div class="content-actions">
            <?php if ($item['trailer']): ?>
                <a href="#trailer" class="btn btn-primary">Смотреть трейлер</a>
            <?php endif; ?>

            <?php if (is_logged_in()): ?>
                <?php /* Избранное — POST с CSRF, повторное нажатие убирает из списка */ ?>
                <form action="toggle_favorite.php" method="post" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="content_id" value="<?= $id ?>">
                    <input type="hidden" name="back" value="content.php?id=<?= $id ?>">
                    <button type="submit" class="btn <?= $isFav ? 'btn-primary' : 'btn-ghost' ?>">
                        <?= $isFav ? 'В избранном' : 'В избранное' ?>
                    </button>
                </form>
            <?php else: ?>
                <a href="login.php?next=<?= rawurlencode('/content.php?id=' . $id) ?>" class="btn btn-ghost">В избранное</a>
            <?php endif; ?>

            <?php if (!is_subscriber() && !is_admin()): ?>
                <a href="payment.php" class="btn btn-ghost">Оформить подписку</a>
            <?php endif; ?>
        </div>

        <?php if ($directors): ?>
            <h3 class="people-title">Режиссёр<?= count($directors) > 1 ? 'ы' : '' ?></h3>
            <div class="people-row">
                <?php foreach ($directors as $p): ?>
                    <div class="person">
                        <img src="<?= e($p['photo']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        <div class="person-name"><?= e($p['name']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($actors): ?>
            <h3 class="people-title">В ролях</h3>
            <div class="people-row">
                <?php foreach ($actors as $p): ?>
                    <div class="person">
                        <img src="<?= e($p['photo']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        <div class="person-name"><?= e($p['name']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============ Трейлер ============ -->
<?php if ($item['trailer']): ?>
<section class="section" id="trailer">
    <div class="section-head"><h2 class="section-title">Трейлер</h2></div>
    <?php
    // Трейлеры размещены на RuTube: сервис доступен в РФ без обходных путей.
    // Ссылка «Открыть на RuTube» — запасной вариант, если встраивание заблокировано.
    $trailerWatchUrl = str_replace('/play/embed/', '/video/', (string)$item['trailer']);
    ?>
    <div class="trailer-box">
        <iframe src="<?= e($item['trailer']) ?>" title="Трейлер «<?= e($item['title']) ?>»"
                allow="clipboard-write; autoplay; fullscreen"
                allowfullscreen loading="lazy"></iframe>
    </div>
    <p class="trailer-link">
        <a href="<?= e($trailerWatchUrl) ?>" target="_blank" rel="noopener noreferrer">Открыть трейлер на RuTube →</a>
    </p>
</section>
<?php endif; ?>

<!-- ============ Сезоны и эпизоды (сериал) ============ -->
<?php if ($episodes): ?>
<section class="section">
    <div class="section-head">
        <h2 class="section-title">Сезоны и эпизоды</h2>
        <span class="section-link"><?= count($episodes) ?> сезон(а/ов)</span>
    </div>

    <div class="season-tabs">
        <?php $first = true; foreach (array_keys($episodes) as $season): ?>
            <button class="season-tab <?= $first ? 'active' : '' ?>" data-season="<?= (int)$season ?>" type="button">
                Сезон <?= (int)$season ?>
            </button>
        <?php $first = false; endforeach; ?>
    </div>

    <?php $first = true; foreach ($episodes as $season => $list): ?>
        <div class="season-block" data-season="<?= (int)$season ?>" style="<?= $first ? '' : 'display:none' ?>">
            <?php foreach ($list as $ep): ?>
                <div class="episode">
                    <span class="episode-num">S<?= (int)$season ?>E<?= (int)$ep['episode'] ?></span>
                    <span style="flex:1"><?= e($ep['title']) ?></span>
                    <span class="episode-dur"><?= (int)$ep['duration'] ?> мин</span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php $first = false; endforeach; ?>
</section>
<?php endif; ?>

<!-- ============ Похожее ============ -->
<?php if ($similarItems): ?>
<section class="section">
    <div class="section-head">
        <h2 class="section-title">Похожие <?= $item['type'] === 'movie' ? 'фильмы' : 'сериалы' ?></h2>
    </div>
    <div class="row">
        <?php foreach ($similarItems as $s) echo render_card($s, $favIds); ?>
    </div>
</section>
<?php endif; ?>

<!-- ============ Отзывы и оценки ============ -->
<section class="section" id="reviews">
    <div class="section-head">
        <h2 class="section-title">Отзывы и оценки (<?= count($reviews) ?>)</h2>
        <?php if ($userAvg !== null): ?>
            <span class="section-link">Средняя оценка зрителей: <?= e(number_format($userAvg, 1, ',', '')) ?>/10</span>
        <?php endif; ?>
    </div>

    <div class="reviews">
        <?php if (!$reviews): ?>
            <p class="empty-state">Пока нет отзывов — будь первым!</p>
        <?php endif; ?>

        <?php foreach ($reviews as $r): ?>
            <div class="review">
                <div class="review-head">
                    <b><?= e($r['username']) ?></b>
                    <span class="rating <?= rating_class((float)$r['rating']) ?>"><?= (int)$r['rating'] ?>/10</span>
                </div>
                <p><?= nl2br(e($r['text'])) ?></p>
                <small style="color:var(--muted)"><?= date('d.m.Y', strtotime($r['created_at'])) ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (is_logged_in()): ?>
        <form class="review-form" method="post" action="content.php?id=<?= $id ?>#reviews">
            <?= csrf_field() ?>
            <label for="review-rating">Ваша оценка</label>
            <select id="review-rating" name="rating">
                <?php for ($i = 10; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <label for="review-text">Отзыв</label>
            <textarea id="review-text" name="text" rows="3" maxlength="1000"
                      placeholder="Поделись впечатлением…" required></textarea>
            <button class="btn btn-primary" name="review_submit" value="1" style="justify-self:start">
                Отправить отзыв
            </button>
        </form>
    <?php else: ?>
        <p style="margin-top:16px">
            <a href="login.php?next=<?= rawurlencode('/content.php?id=' . $id) ?>" style="color:var(--primary)">Войдите</a>,
            чтобы оставить отзыв.
        </p>
    <?php endif; ?>
</section>

<?php require 'includes/footer.php'; ?>
