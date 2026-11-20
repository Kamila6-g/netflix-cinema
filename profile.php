<?php
/**
 * profile.php — личный кабинет пользователя.
 *
 * Доступен только после авторизации: require_login() перенаправит гостя на вход.
 * На странице показаны:
 *   - данные аккаунта и роль;
 *   - статус подписки и кнопка продления;
 *   - избранное;
 *   - история просмотров (последние 12 уникальных тайтлов);
 *   - платежи пользователя из таблицы payments.
 */

require 'config/config.php';
require_login();

// Данные текущего пользователя (роль и подписка всегда берём из БД, а не из сессии).
$user = current_user($pdo);
if (!$user) {
    // Пользователя удалили, а сессия осталась — выходим.
    header('Location: logout.php');
    exit;
}

// Избранное.
$favStmt = $pdo->prepare("SELECT c.* FROM favorites f
                          JOIN content c ON c.id = f.content_id
                          WHERE f.user_id = ? ORDER BY c.title");
$favStmt->execute([$_SESSION['user_id']]);
$favorites = $favStmt->fetchAll();

// История: одна карточка на тайтл, сортировка по последнему просмотру.
$histStmt = $pdo->prepare("SELECT c.*, MAX(h.watched_at) AS last_watched
                           FROM history h JOIN content c ON c.id = h.content_id
                           WHERE h.user_id = ?
                           GROUP BY c.id, c.title, c.type, c.description, c.poster, c.backdrop,
                                    c.trailer, c.year, c.rating, c.duration, c.category_id,
                                    c.is_new, c.is_popular, c.created_at
                           ORDER BY last_watched DESC LIMIT 12");
$histStmt->execute([$_SESSION['user_id']]);
$history = $histStmt->fetchAll();

// Платежи: тариф берём через JOIN, чтобы показать название, а не ID.
$payStmt = $pdo->prepare("SELECT p.*, pl.name AS plan_name
                          FROM payments p JOIN plans pl ON pl.id = p.plan_id
                          WHERE p.user_id = ? ORDER BY p.created_at DESC");
$payStmt->execute([$_SESSION['user_id']]);
$payments = $payStmt->fetchAll();

$roles = ['user' => 'Пользователь', 'subscriber' => 'Подписчик', 'admin' => 'Администратор'];

$pageTitle = 'Личный кабинет · NETFLIX';
require 'includes/header.php';
?>
<section class="section" style="padding-top:36px">
    <div class="profile-grid">

        <!-- Левая колонка: карточка аккаунта -->
        <aside class="profile-card">
            <div class="profile-avatar"><?= e(mb_strtoupper(mb_substr($user['username'], 0, 1))) ?></div>
            <h2 style="font-family:Montserrat;font-size:22px"><?= e($user['username']) ?></h2>
            <p class="sub"><?= e($user['email']) ?></p>
            <span class="role-tag"><?= e($roles[$user['role']] ?? 'Пользователь') ?></span>

            <p style="margin-top:16px;font-size:13px;color:var(--muted)">
                <?= e(subscription_label($user)) ?>
            </p>

            <?php if ($user['role'] !== 'admin'): ?>
                <div style="margin-top:22px">
                    <a href="payment.php" class="btn btn-primary form-submit">
                        <?= subscription_active($user) ? 'Продлить подписку' : 'Оформить подписку' ?>
                    </a>
                </div>
            <?php endif; ?>

            <p style="margin-top:18px;font-size:12px;color:var(--muted)">
                В кабинете: <?= count($favorites) ?> в избранном,
                <?= count($history) ?> в истории, <?= count($payments) ?> платежей.
            </p>
        </aside>

        <!-- Правая колонка: разделы кабинета -->
        <div>
            <section class="section">
                <div class="section-head">
                    <h2 class="section-title">Избранное (<?= count($favorites) ?>)</h2>
                    <a href="favorites.php" class="section-link">Управлять →</a>
                </div>
                <?php if ($favorites): ?>
                    <div class="row"><?php foreach ($favorites as $f) echo render_card($f, $favIds); ?></div>
                <?php else: ?>
                    <p class="empty-state">Вы пока ничего не добавили в избранное. Откройте любой фильм и нажмите «В избранное».</p>
                <?php endif; ?>
            </section>

            <section class="section">
                <div class="section-head"><h2 class="section-title">История просмотров</h2></div>
                <?php if ($history): ?>
                    <div class="row"><?php foreach ($history as $h) echo render_card($h, $favIds); ?></div>
                <?php else: ?>
                    <p class="empty-state">История пуста — самое время что-нибудь посмотреть!</p>
                <?php endif; ?>
            </section>

            <section class="section">
                <div class="section-head"><h2 class="section-title">Платежи</h2></div>
                <?php if ($payments): ?>
                    <div class="table-wrap">
                        <table class="admin-table">
                            <thead>
                            <tr><th>Дата</th><th>Тариф</th><th>Сумма</th><th>Статус</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><?= date('d.m.Y H:i', strtotime($p['created_at'])) ?></td>
                                    <td><?= e($p['plan_name']) ?></td>
                                    <td><?= number_format((float)$p['amount'], 0, ',', ' ') ?> ₽</td>
                                    <td>
                                        <?php if ($p['status'] === 'paid'): ?>
                                            <span class="status status-paid">Оплачено</span>
                                        <?php elseif ($p['status'] === 'pending'): ?>
                                            <span class="status status-pending">Ожидает оплаты</span>
                                        <?php else: ?>
                                            <span class="status status-canceled">Отменён</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="empty-state">Оплат ещё не было. <a href="payment.php">Выбрать тариф →</a></p>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>
<?php require 'includes/footer.php'; ?>
