<?php
/**
 * config/functions.php — общие функции проекта.
 *
 * Здесь собрано всё, что нужно на каждой странице:
 *   - безопасный вывод в HTML (e);
 *   - проверка авторизации и ролей;
 *   - CSRF-токен для форм;
 *   - единая карточка контента;
 *   - вспомогательные функции для тарифов и подписки.
 */

declare(strict_types=1);

/**
 * Экранирует строку для вывода в HTML (защита от XSS).
 * Всегда используйте e() при выводе пользовательских данных.
 */
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Пользователь авторизован? */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Текущая роль: user / subscriber / admin ('' для гостя). */
function current_role(): string
{
    return (string)($_SESSION['role'] ?? '');
}

/** Администратор? */
function is_admin(): bool
{
    return is_logged_in() && current_role() === 'admin';
}

/**
 * Подписчик — роль subscriber или admin.
 * Дополнительно проверяем дату окончания подписки, чтобы роль не «залипала» навсегда.
 */
function is_subscriber(): bool
{
    if (!is_logged_in()) {
        return false;
    }
    if (current_role() === 'admin') {
        return true;
    }
    if (current_role() !== 'subscriber') {
        return false;
    }
    $until = $_SESSION['subscription_until'] ?? null;
    if (!$until) {
        return true;
    }
    return strtotime($until) >= time();
}

/**
 * Требует авторизации. Гостя перенаправляет на вход,
 * сохраняя адрес возврата в ?next=.
 */
function require_login(string $redirect = 'login.php'): void
{
    if (is_logged_in()) {
        return;
    }
    $target = $_SERVER['REQUEST_URI'] ?? $redirect;
    if (strpos($target, '://') !== false || strpos($target, "\n") !== false) {
        $target = $redirect;
    }
    header('Location: ' . $redirect . '?next=' . rawurlencode($target));
    exit;
}

/**
 * Требует роль администратора для страниц в папке Admin/.
 * Гостя отправляет на вход, авторизованного без прав — на 403.
 * Страница ошибки самодостаточная (без общей шапки), чтобы не зависеть
 * от относительных путей при вызове из вложенной папки.
 */
function require_admin(): void
{
    require_login();
    if (is_admin()) {
        return;
    }

    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>403 — Доступ запрещён</title>'
        . '<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;'
        . 'background:#0b0b12;color:#e7e7ef;font-family:Inter,system-ui,sans-serif}'
        . '.box{max-width:520px;padding:36px;background:#15151f;border:1px solid #2a2a3a;border-radius:18px;text-align:center}'
        . 'h1{font-size:52px;margin:0 0 8px;color:#e50914}p{color:#9a9aae;line-height:1.5}'
        . 'a{display:inline-block;margin-top:18px;padding:12px 22px;border-radius:10px;background:#e50914;'
        . 'color:#fff;text-decoration:none;font-weight:600}</style></head><body><div class="box">'
        . '<h1>403</h1><p>Эта страница доступна только администратору.</p>'
        . '<a href="../index.php">На главную</a></div></body></html>';
    exit;
}

/** Текущий пользователь из БД (или null). */
function current_user(PDO $pdo): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/** Одноразовое сообщение, которое показывается после редиректа. */
function flash(string $msg, string $type = 'success'): void
{
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

/** Забирает flash-сообщение из сессии (и удаляет его). */
function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

/* ------------------------------------------------------------------
 * CSRF-защита
 * ------------------------------------------------------------------ */

/** Возвращает CSRF-токен текущей сессии, создавая его при необходимости. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Готовое скрытое поле для формы. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Проверяет токен из POST. При несовпадении — 419 и остановка. */
function csrf_verify(): void
{
    $sent = (string)($_POST['csrf_token'] ?? '');
    $real = (string)($_SESSION['csrf_token'] ?? '');
    if ($sent === '' || $real === '' || !hash_equals($real, $sent)) {
        http_response_code(419);
        exit('Страница устарела. Обновите её и повторите действие.');
    }
}

/* ------------------------------------------------------------------
 * Вспомогательные функции представления
 * ------------------------------------------------------------------ */

/** CSS-класс для рейтинга. */
function rating_class(float $r): string
{
    if ($r >= 8.5) return 'top';
    if ($r >= 7.5) return 'good';
    return 'mid';
}

/**
 * Карточка контента для сеток главной, каталога и кабинета.
 * $favIds — массив ID избранного, чтобы показать сердечко.
 */
function render_card(array $item, array $favIds = []): string
{
    $isFav = in_array((int)$item['id'], $favIds, true);
    $badge = '';
    if (!empty($item['is_new'])) {
        $badge = '<span class="badge badge-new">Новинка</span>';
    }
    if (!empty($item['is_popular'])) {
        $badge = '<span class="badge badge-hot">Топ</span>';
    }

    return '
    <a class="card" href="content.php?id=' . (int)$item['id'] . '">
        ' . $badge . '
        <div class="card-poster">
            <img loading="lazy" src="' . e($item['poster']) . '" alt="' . e($item['title']) . '">
            <div class="card-overlay">
                <span class="play-btn">Смотреть</span>
            </div>
        </div>
        <div class="card-body">
            <div class="card-title">' . e($item['title']) . '</div>
            <div class="card-meta">
                <span class="rating ' . rating_class((float)$item['rating']) . '">' . e($item['rating']) . '</span>
                <span>' . (int)$item['year'] . '</span>
                <span class="type">' . ($item['type'] === 'movie' ? 'Фильм' : 'Сериал') . '</span>
            </div>
        </div>
        ' . ($isFav ? '<span class="fav-mark"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-6.7-4.3-9.3-8.1C.7 10.3 2 6.6 5.2 5.4 7.2 4.7 9 5.4 12 8c3-2.6 4.8-3.3 6.8-2.6 3.2 1.2 4.5 4.9 2.5 7.5C18.7 16.7 12 21 12 21z"/></svg></span>' : '') . '
    </a>';
}

/** ID избранного текущего пользователя. */
function get_fav_ids(PDO $pdo): array
{
    if (!is_logged_in()) {
        return [];
    }
    $stmt = $pdo->prepare('SELECT content_id FROM favorites WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/* ------------------------------------------------------------------
 * Тарифы и подписка
 * ------------------------------------------------------------------ */

/**
 * Тарифы хранятся в таблице plans, а не в коде страницы:
 * администратор может менять цену и описание без правки PHP.
 * features — JSON-массив пунктов «что входит».
 */
function plans_all(PDO $pdo): array
{
    $rows = $pdo->query('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order, price')->fetchAll();
    foreach ($rows as &$row) {
        $row['features_list'] = json_decode((string)$row['features'], true) ?: [];
    }
    return $rows;
}

/** Один тариф по ID (null, если не найден). */
function plan_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM plans WHERE id = ? AND is_active = 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    $row['features_list'] = json_decode((string)$row['features'], true) ?: [];
    return $row;
}

/** Активна ли подписка пользователя (по дате из БД). */
function subscription_active(array $user): bool
{
    return !empty($user['subscription_until']) && strtotime((string)$user['subscription_until']) >= time();
}

/** Человекочитаемый статус подписки. */
function subscription_label(array $user): string
{
    if (($user['role'] ?? '') === 'admin') {
        return 'Администратор — доступ ко всему';
    }
    if (subscription_active($user)) {
        return 'Подписка активна до ' . date('d.m.Y H:i', strtotime((string)$user['subscription_until']));
    }
    return 'Подписка не оформлена';
}

/**
 * Продлевает подписку и выдаёт роль subscriber.
 * Если подписка ещё активна — новый срок добавляется к текущей дате окончания.
 */
function activate_subscription(PDO $pdo, int $userId, int $days): string
{
    $stmt = $pdo->prepare('SELECT subscription_until, role FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $base = time();
    if ($user && !empty($user['subscription_until']) && strtotime((string)$user['subscription_until']) > $base) {
        $base = strtotime((string)$user['subscription_until']);
    }
    $until = date('Y-m-d H:i:s', strtotime('+' . $days . ' days', $base));

    // Администратора не понижаем и не трогаем его роль.
    if ($user && $user['role'] === 'admin') {
        $upd = $pdo->prepare('UPDATE users SET subscription_until = ? WHERE id = ?');
        $upd->execute([$until, $userId]);
    } else {
        $upd = $pdo->prepare("UPDATE users SET role = 'subscriber', subscription_until = ? WHERE id = ?");
        $upd->execute([$until, $userId]);
    }

    $_SESSION['role'] = $user['role'] === 'admin' ? 'admin' : 'subscriber';
    $_SESSION['subscription_until'] = $until;

    return $until;
}
