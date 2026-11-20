<?php
/**
 * includes/error-404.php — страница «Страница не найдена».
 *
 * Используется страницами контента, если запись с указанным ID отсутствует в БД.
 * Перед подключением страница выставляет http_response_code(404).
 */
$pageTitle = '404 — Страница не найдена · NETFLIX';
require __DIR__ . '/header.php';
?>
<section class="section" style="padding-top:60px;text-align:center">
    <h1 class="section-title" style="font-size:56px">404</h1>
    <p style="color:var(--muted);font-size:17px;margin:12px 0 24px">
        Такой страницы нет — возможно, фильм удалили или ссылка устарела.
    </p>
    <a href="index.php" class="btn btn-primary">На главную</a>
    <a href="catalog.php" class="btn btn-ghost">В каталог</a>
</section>
<?php require __DIR__ . '/footer.php'; ?>
