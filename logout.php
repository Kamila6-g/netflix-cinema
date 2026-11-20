<?php
/**
 * logout.php — корректный выход из аккаунта.
 *
 * Выход выполняется только POST-запросом с CSRF-токеном:
 * случайная ссылка или картинка с другого сайта разлогинить не сможет.
 */

require 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

csrf_verify();

// 1. Очищаем данные сессии.
$_SESSION = [];

// 2. Удаляем cookie сессии из браузера.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// 3. Уничтожаем сессию на сервере.
session_destroy();

header('Location: index.php');
exit;
