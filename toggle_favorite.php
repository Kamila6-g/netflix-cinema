<?php
/**
 * toggle_favorite.php — добавление/удаление тайтла в избранном.
 *
 * Принимает только POST с CSRF-токеном и ID контента.
 * Если запись уже есть — удаляет, если нет — добавляет.
 * После действия возвращает пользователя на исходную страницу.
 */

require 'config/config.php';
require_login();

// Защита от подделки запроса с чужого сайта.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: favorites.php');
    exit;
}
csrf_verify();

$contentId = (int)($_POST['content_id'] ?? 0);

// Проверяем, что такой тайтл существует (защита от мусорных ID).
$exists = $pdo->prepare('SELECT 1 FROM content WHERE id = ?');
$exists->execute([$contentId]);

if ($contentId > 0 && $exists->fetch()) {
    $chk = $pdo->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND content_id = ?');
    $chk->execute([$_SESSION['user_id'], $contentId]);

    if ($chk->fetch()) {
        $del = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND content_id = ?');
        $del->execute([$_SESSION['user_id'], $contentId]);
        flash('Удалено из избранного.');
    } else {
        $ins = $pdo->prepare('INSERT INTO favorites (user_id, content_id) VALUES (?, ?)');
        $ins->execute([$_SESSION['user_id'], $contentId]);
        flash('Добавлено в избранное.');
    }
}

// Возврат только на свою страницу: внешний URL из формы игнорируем.
$back = (string)($_POST['back'] ?? '');
if ($back === '' || !preg_match('/^[a-z0-9_\-]+\.php(\?[^#]*)?$/i', $back)) {
    $back = 'favorites.php';
}
header('Location: ' . $back);
exit;
