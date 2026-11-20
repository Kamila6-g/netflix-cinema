<?php
/**
 * gateway.php — ТЕСТОВЫЙ платёжный шлюз (эмулятор ЮKassa, песочница).
 *
 * ВАЖНО: это учебная имитация. Реальные ключи магазина не используются,
 * запросы никуда не отправляются, деньги не списываются.
 * Логика повторяет реальный сценарий:
 *   pending → (подтверждение) → paid → выдача роли «подписчик».
 *
 * Платёж идентифицируется по payment_ref, который нельзя угадать,
 * и всегда проверяется принадлежность текущему пользователю.
 */

require 'config/config.php';
require_login();

$ref = trim((string)($_GET['ref'] ?? $_POST['ref'] ?? ''));
if ($ref === '') {
    header('Location: payment.php');
    exit;
}

// Платёж + тариф; чужой платёж недоступен.
$stmt = $pdo->prepare("SELECT p.*, pl.name AS plan_name, pl.duration_days, pl.features
                       FROM payments p JOIN plans pl ON pl.id = p.plan_id
                       WHERE p.payment_ref = ? AND p.user_id = ? LIMIT 1");
$stmt->execute([$ref, $_SESSION['user_id']]);
$payment = $stmt->fetch();

if (!$payment) {
    http_response_code(404);
    require 'includes/error-404.php';
    exit;
}

// Уже оплачен — сразу к чеку.
if ($payment['status'] === 'paid') {
    header('Location: payment-success.php?ref=' . urlencode($ref));
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'cancel') {
        // Пользователь отказался — платёж помечается отменённым.
        $upd = $pdo->prepare("UPDATE payments SET status = 'canceled' WHERE id = ? AND status = 'pending'");
        $upd->execute([$payment['id']]);
        flash('Оплата отменена. Тариф не подключён.', 'error');
        header('Location: payment.php');
        exit;
    }

    if ($action === 'pay') {
        // Серверная проверка данных карты (клиентская — в main.js/HTML-атрибутах).
        $card = preg_replace('/\D/', '', (string)($_POST['card'] ?? ''));
        $exp  = trim((string)($_POST['exp'] ?? ''));
        $cvc  = preg_replace('/\D/', '', (string)($_POST['cvc'] ?? ''));

        if (strlen($card) !== 16) {
            $errors['card'] = 'Номер карты должен содержать 16 цифр.';
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $exp)) {
            $errors['exp'] = 'Срок действия в формате ММ/ГГ.';
        }
        if (strlen($cvc) !== 3) {
            $errors['cvc'] = 'CVC — три цифры.';
        }

        if (!$errors) {
            // Имитация успешного ответа платёжной системы.
            // В реальной интеграции здесь был бы проверенный webhook от ЮKassa.
            $upd = $pdo->prepare("UPDATE payments
                                  SET status = 'paid', paid_at = NOW()
                                  WHERE id = ? AND status = 'pending'");
            $upd->execute([$payment['id']]);

            if ($upd->rowCount() === 1) {
                // Выдаём роль subscriber и продлеваем подписку на срок тарифа.
                activate_subscription($pdo, (int)$_SESSION['user_id'], (int)$payment['duration_days']);
                flash('Оплата прошла успешно. Подписка «' . $payment['plan_name'] . '» активна.');
                header('Location: payment-success.php?ref=' . urlencode($ref));
                exit;
            }
            $errors['general'] = 'Платёж уже обработан. Обновите страницу.';
        }
    }
}

$pageTitle = 'Оплата · тестовый шлюз · NETFLIX';
require 'includes/header.php';
?>
<section class="section" style="padding-top:36px">
    <div class="checkout-wrap">
        <div class="checkout-summary">
            <p class="test-label">ТЕСТОВЫЙ РЕЖИМ</p>
            <h1><?= e($payment['plan_name']) ?></h1>
            <div class="price"><?= number_format((float)$payment['amount'], 0, ',', ' ') ?> ₽</div>
            <p class="sub">Срок подписки: <?= (int)$payment['duration_days'] ?> дней</p>
            <p class="sub">Номер платежа: <code><?= e($payment['payment_ref']) ?></code></p>

            <ul style="margin-top:16px">
                <?php foreach ((json_decode((string)$payment['features'], true) ?: []) as $f): ?>
                    <li><?= e($f) ?></li>
                <?php endforeach; ?>
            </ul>

            <p class="sub" style="margin-top:18px">
                Это эмулятор платёжной формы, а не реальная оплата.
                Используйте тестовую карту <b>4111 1111 1111 1111</b>, срок <b>12/30</b>, CVC <b>123</b>.
            </p>
        </div>

        <div class="checkout-form">
            <h2>Оплата картой</h2>
            <p class="sub">Данные карты не сохраняются и никуда не отправляются.</p>

            <?php if (!empty($errors['general'])): ?>
                <div class="flash flash-error"><?= e($errors['general']) ?></div>
            <?php endif; ?>

            <form method="post" action="gateway.php">
                <?= csrf_field() ?>
                <input type="hidden" name="ref" value="<?= e($payment['payment_ref']) ?>">
                <input type="hidden" name="action" value="pay">

                <div class="field <?= isset($errors['card']) ? 'invalid' : '' ?>">
                    <label for="card">Номер карты</label>
                    <input id="card" name="card" type="text" inputmode="numeric" maxlength="19"
                           placeholder="0000 0000 0000 0000" autocomplete="off" required>
                    <div class="error"><?= e($errors['card'] ?? 'Введите 16 цифр') ?></div>
                </div>

                <div class="field-row">
                    <div class="field <?= isset($errors['exp']) ? 'invalid' : '' ?>">
                        <label for="exp">Срок</label>
                        <input id="exp" name="exp" type="text" maxlength="5" placeholder="ММ/ГГ"
                               autocomplete="off" required>
                        <div class="error"><?= e($errors['exp'] ?? 'ММ/ГГ') ?></div>
                    </div>
                    <div class="field <?= isset($errors['cvc']) ? 'invalid' : '' ?>">
                        <label for="cvc">CVC</label>
                        <input id="cvc" name="cvc" type="password" inputmode="numeric" maxlength="3"
                               placeholder="123" autocomplete="off" required>
                        <div class="error"><?= e($errors['cvc'] ?? '3 цифры') ?></div>
                    </div>
                </div>

                <button class="btn btn-primary form-submit" type="submit">Оплатить</button>
            </form>

            <?php /* Отмена возвращает пользователя к тарифам */ ?>
            <form method="post" action="gateway.php" style="margin-top:10px">
                <?= csrf_field() ?>
                <input type="hidden" name="ref" value="<?= e($payment['payment_ref']) ?>">
                <input type="hidden" name="action" value="cancel">
                <button class="btn btn-ghost form-submit" type="submit">Отменить оплату</button>
            </form>
        </div>
    </div>
</section>

<script>
/* Маска карты и срока: только цифры, автоматические пробелы и «/» */
document.getElementById('card').addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 16).replace(/(.{4})/g, '$1 ').trim();
});
document.getElementById('exp').addEventListener('input', (e) => {
    let v = e.target.value.replace(/\D/g, '').slice(0, 4);
    if (v.length > 2) v = v.slice(0, 2) + '/' + v.slice(2);
    e.target.value = v;
});
</script>

<?php require 'includes/footer.php'; ?>
