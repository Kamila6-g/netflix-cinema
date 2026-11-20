<?php
/**
 * payment.php — страница тарифов и оформления подписки.
 *
 * Тарифы берутся из таблицы plans (а не из массива в коде):
 * название, цена, срок и список возможностей хранятся в БД.
 *
 * Сценарий оплаты:
 *   1. пользователь выбирает тариф и нажимает «Оформить»;
 *   2. создаётся запись payments со статусом pending;
 *   3. пользователь попадает на тестовый платёжный шлюз gateway.php;
 *   4. после «оплаты» статус становится paid, а роль — subscriber.
 *
 * Реальные ключи платёжной системы не используются: включён тестовый режим.
 */

require 'config/config.php';
require_login();

$plans = plans_all($pdo);
$user = current_user($pdo);

// Ошибки и выбранный тариф приходят из POST.
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    csrf_verify();

    $planId = (int)$_POST['plan_id'];
    // Цену и срок берём только из БД — данные из формы не доверяем.
    $plan = plan_by_id($pdo, $planId);

    if (!$plan) {
        $error = 'Тариф не найден. Обновите страницу.';
    } else {
        // Уникальный номер платежа для возврата со шлюза.
        $ref = 'PAY-' . date('Ymd') . '-' . bin2hex(random_bytes(6));

        $ins = $pdo->prepare("INSERT INTO payments (user_id, plan_id, amount, status, payment_ref, method)
                              VALUES (?, ?, ?, 'pending', ?, 'test_gateway')");
        $ins->execute([$_SESSION['user_id'], $plan['id'], $plan['price'], $ref]);

        // Передаём на шлюз только ссылку на платёж; сумму он берёт из БД.
        header('Location: gateway.php?ref=' . urlencode($ref));
        exit;
    }
}

$pageTitle = 'Подписка и тарифы · NETFLIX';
require 'includes/header.php';
?>
<section class="section" style="padding-top:36px">
    <div class="section-head">
        <h2 class="section-title">Выберите подписку</h2>
        <span class="section-link">Оплата в тестовом режиме</span>
    </div>

    <?php if ($user): ?>
        <p class="subscription-note">
            <?= e(subscription_label($user)) ?>
            <?php if ($user['role'] !== 'admin' && subscription_active($user)): ?>
                — новый тариф добавит дни к текущему сроку.
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="flash flash-error" style="margin-bottom:16px"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- Перечень тарифов из БД -->
    <div class="plans">
        <?php $i = 0; foreach ($plans as $plan): $i++; ?>
            <div class="plan <?= $plan['is_featured'] ? 'featured' : '' ?>">
                <?php if ($plan['is_featured']): ?><span class="tag">Популярный</span><?php endif; ?>

                <h3><?= e($plan['name']) ?></h3>
                <div class="price">
                    <?= number_format((float)$plan['price'], 0, ',', ' ') ?> ₽
                    <small>/ <?= (int)$plan['duration_days'] === 365 ? 'год' : (int)$plan['duration_days'] . ' дней' ?></small>
                </div>

                <ul>
                    <?php foreach ($plan['features_list'] as $feature): ?>
                        <li><?= e($feature) ?></li>
                    <?php endforeach; ?>
                </ul>

                <form method="post" action="payment.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                    <button class="btn btn-primary form-submit" type="submit">
                        <?= $plan['is_featured'] ? 'Выбрать «' . e($plan['name']) . '»' : 'Оформить' ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Пояснение про тестовый режим -->
    <div class="test-banner">
        <b>Тестовый режим оплаты.</b>
        Реальный платёжный шлюз (ЮKassa, CloudPayments и т. п.) не подключён: ключей магазина нет.
        Страница <code>gateway.php</code> имитирует платёжную форму ЮKassa в песочнице.
        Деньги не списываются, а тестовая карта — <code>4111 1111 1111 1111</code>, срок <code>12/30</code>, CVC <code>123</code>.
        После подтверждения роль «подписчик» выдаётся так же, как при реальной оплате.
    </div>

    <?php if (!is_subscriber() && !is_admin()): ?>
        <p class="empty-state" style="margin-top:22px">
            Подписка открывает полный каталог, историю просмотров и скачивание.
            Оформить можно в любой момент — доступ появится сразу после оплаты.
        </p>
    <?php endif; ?>
</section>
<?php require 'includes/footer.php'; ?>
