<?php
/**
 * Admin/index.php — главная страница админ-панели.
 *
 * Доступ: только роль admin (require_admin()).
 * Здесь выводятся:
 *   - сводка (контент, пользователи, платежи);
 *   - таблица контента с переходом на редактирование и удалением (POST + CSRF);
 *   - пользователи и их роли;
 *   - последние платежи.
 */

require '../config/config.php';
require_admin();

// --- Удаление контента: только POST и только с CSRF-токеном ---------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $deleteId = (int)($_POST['id'] ?? 0);

    if ($deleteId > 0) {
        // Связанные таблицы (content_people, episodes, favorites, history, reviews)
        // удалятся каскадно благодаря внешним ключам ON DELETE CASCADE.
        $del = $pdo->prepare('DELETE FROM content WHERE id = ?');
        $del->execute([$deleteId]);
        flash('Запись удалена.');
    }
    header('Location: index.php');
    exit;
}

// --- Данные для таблиц -----------------------------------------------------
$items = $pdo->query("SELECT c.*, cat.name AS cat_name
                      FROM content c LEFT JOIN categories cat ON cat.id = c.category_id
                      ORDER BY c.id DESC")->fetchAll();

$users = $pdo->query("SELECT id, username, email, role, subscription_until FROM users ORDER BY id")->fetchAll();

$payments = $pdo->query("SELECT p.*, u.username, pl.name AS plan_name
                         FROM payments p
                         JOIN users u ON u.id = p.user_id
                         JOIN plans pl ON pl.id = p.plan_id
                         ORDER BY p.created_at DESC LIMIT 10")->fetchAll();

$stats = [
    'content'  => (int)$pdo->query('SELECT COUNT(*) FROM content')->fetchColumn(),
    'users'    => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'paid'     => (int)$pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'paid'")->fetchColumn(),
    'revenue'  => (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админ-панель · NETFLIX</title>
    <!-- Общий внешний CSS проекта -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">

<!-- ============ Шапка админки ============ -->
<header class="admin-topbar">
    <a href="index.php" class="logo"><span class="logo-mark"><img src="../assets/img/favicon.svg" alt="NETFLIX"></span>NETFLIX · админка</a>
    <div class="admin-topbar-actions">
        <span class="admin-user"><?= e($_SESSION['username'] ?? '') ?></span>
        <a href="../index.php" class="btn btn-ghost">← На сайт</a>
        <form method="post" action="../logout.php" class="logout-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-ghost">Выйти</button>
        </form>
    </div>
</header>

<main class="admin-main">

    <!-- ============ Сводка ============ -->
    <div class="stat-grid">
        <div class="stat"><span>Тайтлов в каталоге</span><b><?= $stats['content'] ?></b></div>
        <div class="stat"><span>Пользователей</span><b><?= $stats['users'] ?></b></div>
        <div class="stat"><span>Оплаченных подписок</span><b><?= $stats['paid'] ?></b></div>
        <div class="stat"><span>Выручка (тест)</span><b><?= number_format($stats['revenue'], 0, ',', ' ') ?> ₽</b></div>
    </div>

    <!-- ============ Контент ============ -->
    <section class="admin-section">
        <div class="section-head">
            <h2 class="section-title">Контент (<?= count($items) ?>)</h2>
            <a href="edit.php" class="btn btn-primary">+ Добавить тайтл</a>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                <tr><th>ID</th><th>Название</th><th>Тип</th><th>Категория</th><th>Рейтинг</th><th>Метки</th><th>Действия</th></tr>
                </thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= (int)$it['id'] ?></td>
                        <td><?= e($it['title']) ?></td>
                        <td><?= $it['type'] === 'movie' ? 'Фильм' : 'Сериал' ?></td>
                        <td><?= e($it['cat_name'] ?? '—') ?></td>
                        <td><?= e($it['rating']) ?></td>
                        <td>
                            <?php if ($it['is_new']): ?><span class="badge badge-new">Новинка</span><?php endif; ?>
                            <?php if ($it['is_popular']): ?><span class="badge badge-hot">Топ</span><?php endif; ?>
                        </td>
                        <td class="admin-actions">
                            <a href="edit.php?id=<?= (int)$it['id'] ?>">Редактировать</a>

                            <?php /* Удаление — POST-форма с CSRF, а не ссылка */ ?>
                            <form method="post" action="index.php" class="inline-form"
                                  onsubmit="return confirm('Удалить «<?= e($it['title']) ?>»? Действие необратимо.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                                <button type="submit" class="link-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- ============ Пользователи ============ -->
    <section class="admin-section">
        <div class="section-head"><h2 class="section-title">Пользователи (<?= count($users) ?>)</h2></div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                <tr><th>ID</th><th>Имя</th><th>Email</th><th>Роль</th><th>Подписка до</th></tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int)$u['id'] ?></td>
                        <td><?= e($u['username']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td>
                            <?php /* Три роли проекта: администратор, подписчик, пользователь */ ?>
                            <span class="role-pill role-<?= e($u['role']) ?>">
                                <?= e(['user' => 'Пользователь', 'subscriber' => 'Подписчик', 'admin' => 'Администратор'][$u['role']] ?? $u['role']) ?>
                            </span>
                        </td>
                        <td><?= $u['subscription_until'] ? date('d.m.Y H:i', strtotime($u['subscription_until'])) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- ============ Платежи ============ -->
    <section class="admin-section">
        <div class="section-head"><h2 class="section-title">Последние платежи</h2></div>

        <?php if ($payments): ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr><th>Дата</th><th>Пользователь</th><th>Тариф</th><th>Сумма</th><th>Статус</th><th>Номер</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= date('d.m.Y H:i', strtotime($p['created_at'])) ?></td>
                            <td><?= e($p['username']) ?></td>
                            <td><?= e($p['plan_name']) ?></td>
                            <td><?= number_format((float)$p['amount'], 0, ',', ' ') ?> ₽</td>
                            <td>
                                <?php if ($p['status'] === 'paid'): ?>
                                    <span class="status status-paid">Оплачено</span>
                                <?php elseif ($p['status'] === 'pending'): ?>
                                    <span class="status status-pending">Ожидает</span>
                                <?php else: ?>
                                    <span class="status status-canceled">Отменён</span>
                                <?php endif; ?>
                            </td>
                            <td><code><?= e($p['payment_ref']) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty-state">Платежей пока нет.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
