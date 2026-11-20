<?php
/**
 * config/config.php — единая точка инициализации приложения.
 *
 * Подключается первой строкой на каждой странице и выполняет три вещи:
 *   1) настраивает и запускает сессию (авторизация пользователя);
 *   2) подключает базу данных (PDO) из config/db.php;
 *   3) подключает общие функции и защиту от CSRF из config/functions.php.
 */

declare(strict_types=1);

// Сессия должна стартовать до любого вывода в браузер.
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Cookie сессии недоступна JavaScript (HttpOnly) и не отправляется при cross-site запросах.
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $isHttps,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Подключение к MySQL. При ошибке db.php сам покажет понятную страницу и завершит работу.
require_once __DIR__ . '/db.php';

// Общие функции: экранирование, роли, CSRF, карточки контента.
require_once __DIR__ . '/functions.php';
