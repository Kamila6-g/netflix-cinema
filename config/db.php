<?php
/**
 * config/db.php — подключение к базе данных MySQL через PDO.
 *
 * Здесь собраны только настройки соединения и создание объекта $pdo.
 * Все запросы в проекте выполняются через подготовленные выражения (prepared statements),
 * поэтому в DSN включён режим эмуляции выключенным: PDO::ATTR_EMULATE_PREPARES => false.
 *
 * Параметры можно переопределить переменными окружения (например, на другом ПК):
 *   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS.
 * Значения по умолчанию рассчитаны на OpenServer Panel с MySQL на порту 3308.
 */

declare(strict_types=1);

/** Хост MySQL. На OpenServer обычно 127.0.0.1. */
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');

/** Порт MySQL. В OpenServer по умолчанию 3306, в этом проекте — 3308. */
define('DB_PORT', getenv('DB_PORT') ?: '3308');

/** Имя базы данных проекта. */
define('DB_NAME', getenv('DB_NAME') ?: 'cinema_db');

/** Пользователь MySQL. */
define('DB_USER', getenv('DB_USER') ?: 'root');

/** Пароль MySQL (по умолчанию у OpenServer пустой). */
define('DB_PASS', getenv('DB_PASS') !== false ? (string)getenv('DB_PASS') : '');

/** Кодировка соединения: utf8mb4 нужна для кириллицы и эмодзи. */
define('DB_CHARSET', 'utf8mb4');

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    DB_HOST,
    DB_PORT,
    DB_NAME,
    DB_CHARSET
);

try {
    // Единое подключение на весь запрос: дальше по коду используется только $pdo.
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        // Ошибки SQL должны выбрасывать исключения, а не молча возвращать false.
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Результаты по умолчанию — ассоциативные массивы.
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Настоящие подготовленные запросы на стороне MySQL (защита от SQL-инъекций).
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Технические детали пишем в лог, пользователю показываем понятное сообщение
    // без логина, пароля и прочих данных подключения.
    error_log('[DB] ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8">'
        . '<title>Нет связи с базой данных</title>'
        . '<style>body{font-family:system-ui,sans-serif;background:#0b0b12;color:#e7e7ef;'
        . 'display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0}'
        . '.box{max-width:560px;padding:32px;background:#15151f;border:1px solid #2a2a3a;'
        . 'border-radius:16px}h1{margin-top:0;font-size:22px}code{color:#ff4d59}'
        . 'a{color:#ff4d59}</style></head><body><div class="box">'
        . '<h1>Не удалось подключиться к базе данных</h1>'
        . '<p>Проверьте, что MySQL запущен в OpenServer, база <code>' . htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8')
        . '</code> создана и в неё импортирован <code>db.sql</code>.</p>'
        . '<p>Параметры подключения: <code>config/db.php</code>.</p>'
        . '<p><a href="index.php">Повторить попытку</a></p>'
        . '</div></body></html>';
    exit;
}
