<?php
/**
 * includes/header.php — общая шапка всех страниц сайта.
 *
 * Подключается после config/config.php. Здесь выводятся:
 *   - <head> с внешним CSS и шрифтами;
 *   - логотип, навигация, выпадающий список категорий;
 *   - форма поиска (GET-запрос на search.php);
 *   - блок пользователя (вход/регистрация или меню авторизованного);
 *   - flash-сообщение после редиректа.
 *
 * Переменные, которые страница может задать до подключения:
 *   $pageTitle — заголовок вкладки браузера;
 *   $pageDescription — meta description (необязательно).
 */

// Список избранного нужен карточкам и шапке.
$favIds = get_fav_ids($pdo);

// Сообщение, оставленное предыдущей страницей (например, «Подписка оформлена»).
$flash = get_flash();

// Категории для выпадающего меню: один запрос на страницу.
$navCategories = $pdo->query('SELECT name, slug FROM categories ORDER BY name')->fetchAll();

$pageTitle = $pageTitle ?? 'NETFLIX — онлайн-кинотеатр';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription ?? 'Онлайн-кинотеатр NETFLIX: фильмы и сериалы, подборки, подписка.') ?>">
    <title><?= e($pageTitle) ?></title>

    <!-- Иконка вкладки и внешние шрифты -->
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">

    <!-- Единственный внешний файл стилей проекта (assets/css/style.css) -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Шапка сайта: логотип, навигация, поиск, аккаунт -->
<header class="header" id="header">
    <div class="header-inner">
        <a href="index.php" class="logo" aria-label="NETFLIX — на главную">
            <span class="logo-mark"><img src="assets/img/favicon.svg" alt="NETFLIX"></span>NETFLIX</a>

        <!-- Основная навигация. На мобильных сворачивается в бургер -->
        <nav class="nav" id="nav" aria-label="Основное меню">
            <a href="index.php">Главная</a>
            <a href="movies.php">Фильмы</a>
            <a href="series.php">Сериалы</a>
            <a href="catalog.php">Каталог</a>
            <div class="dropdown">
                <a href="catalog.php" class="dropdown-toggle">Категории ▾</a>
                <div class="dropdown-menu">
                    <?php /* Категории кликабельны и ведут в каталог с фильтром ?category=slug */ ?>
                    <?php foreach ($navCategories as $c): ?>
                        <a href="catalog.php?category=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="payment.php">Подписка</a>
        </nav>

        <!-- Поиск по названию и описанию (GET, обрабатывается в search.php) -->
        <form class="search-form" action="search.php" method="get" role="search">
            <input type="text" name="q" placeholder="Поиск фильмов и сериалов…"
                   aria-label="Поиск по каталогу"
                   value="<?= e($_GET['q'] ?? '') ?>">
            <button type="submit" aria-label="Найти"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg></button>
        </form>

        <!-- Блок аккаунта -->
        <div class="header-actions">
            <?php if (is_logged_in()): ?>
                <a href="favorites.php" class="icon-btn" title="Избранное" aria-label="Избранное"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-6.7-4.3-9.3-8.1C.7 10.3 2 6.6 5.2 5.4 7.2 4.7 9 5.4 12 8c3-2.6 4.8-3.3 6.8-2.6 3.2 1.2 4.5 4.9 2.5 7.5C18.7 16.7 12 21 12 21z"/></svg></a>
                <?php if (is_admin()): ?>
                    <a href="Admin/index.php" class="btn btn-ghost">Админка</a>
                <?php endif; ?>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle avatar-btn" aria-label="Меню пользователя">
                        <span class="avatar"><?= e(mb_substr($_SESSION['username'] ?? 'U', 0, 1)) ?></span>
                    </a>
                    <div class="dropdown-menu right">
                        <a href="profile.php">Личный кабинет</a>
                        <a href="payment.php">Подписка</a>
                        <?php if (is_admin()): ?><a href="Admin/index.php">Админ-панель</a><?php endif; ?>
                        <?php /* Выход — POST-запрос с CSRF-токеном, а не ссылка */ ?>
                        <form method="post" action="logout.php" class="logout-form">
                            <?= csrf_field() ?>
                            <button type="submit">Выйти</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-ghost">Вход</a>
                <a href="register.php" class="btn btn-primary">Регистрация</a>
            <?php endif; ?>
        </div>

        <!-- Кнопка мобильного меню (обрабатывается assets/js/main.js) -->
        <button class="burger" id="burger" aria-label="Открыть меню" aria-expanded="false"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
    </div>
</header>

<?php if ($flash): ?>
    <!-- Одноразовое уведомление (успех/ошибка), текст экранирован -->
    <div class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<main class="main">
