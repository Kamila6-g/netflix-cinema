<?php
/**
 * payment-success.php — чек об успешной оплате.
 *
 * Показывает только платёж текущего пользователя и только со статусом paid.
 * Если платёж не найден или не оплачен — отправляем на страницу тарифов.
 */

require 'config/config.php';
require_login();

$ref = trim((string)($_GET['ref'] ?? ''));

$stmt = $pdo->prepare("SELECT p.*, pl.name AS plan_name, pl.duration_days
                       FROM payments p JOIN plans pl ON pl.id = p.plan_id
                       WHERE p.payment_ref = ? AND p.user_id = ? AND p.status = 'paid' LIMIT 1");
$stmt->execute([$ref, $_SESSION['user_id']]);
$payment = $stmt->fetch();

if (!$payment) {
    flash('Оплаченный платёж не найден.', 'error');
    header('Location: payment.php');
    exit;
}

$user = current_user($pdo);

$pageTitle = 'Подписка оформлена · NETFLIX';
require 'includes/header.php';
?>
<section class="section" style="padding-top:40px">
    <div class="auth-wrap" style="max-width:640px">
        <p class="test-label">ТЕСТОВЫЙ РЕЖИМ</p>
        <h1 style="font-size:26px">Подписка оформлена</h1>
        <p class="sub">Платёж обработан тестовым шлюзом, деньги не списаны.</p>

        <div class="receipt">
            <div><span>Тариф</span><b><?= e($payment['plan_name']) ?></b></div>
            <div><span>Сумма</span><b><?= number_format((float)$payment['amount'], 0, ',', ' ') ?> ₽</b></div>
            <div><span>Номер платежа</span><b><code><?= e($payment['payment_ref']) ?></code></b></div>
            <div><span>Дата оплаты</span><b><?= date('d.m.Y H:i', strtotime((string)$payment['paid_at'])) ?></b></div>
            <div><span>Срок подписки</span><b><?= (int)$payment['duration_days'] ?> дней</b></div>
            <div><span>Действует до</span><b><?= date('d.m.Y H:i', strtotime((string)$user['subscription_until'])) ?></b></div>
            <div><span>Роль</span><b><?= e($user['role'] === 'admin' ? 'Администратор' : 'Подписчик') ?></b></div>
        </div>

        <a href="profile.php" class="btn btn-primary form-submit" style="margin-top:20px">В личный кабинет</a>
        <a href="catalog.php" class="btn btn-ghost form-submit" style="margin-top:8px">Смотреть каталог</a>
    </div>
</section>
<?php require 'includes/footer.php'; ?>
