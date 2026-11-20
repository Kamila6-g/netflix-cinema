<?php
/**
 * includes/error-403.php — страница «Доступ запрещён».
 *
 * Подключается из require_admin(), когда пользователь авторизован,
 * но у него нет роли администратора. Код ответа 403 выставляется вызывающим кодом.
 */
$pageTitle = '403 — Доступ запрещён · NETFLIX';
require __DIR__ . '/header.php';
?>
<section class="section" style="padding-top:60px;text-align:center">
    <h1 class="section-title" style="font-size:56px">403</h1>
    <p style="color:var(--muted);font-size:17px;margin:12px 0 24px">
        Эта страница доступна только администратору.
    </p>
    <a href="index.php" class="btn btn-primary">На главную</a>
</section>
<?php require __DIR__ . '/footer.php'; ?>
