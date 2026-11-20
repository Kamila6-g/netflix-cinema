<?php
/**
 * register.php — регистрация нового пользователя.
 *
 * Особенности:
 *   - все поля проверяются на сервере (и дублируются на клиенте в assets/js/main.js);
 *   - форма отправляется через fetch (AJAX) и показывает ошибки без перезагрузки страницы;
 *   - при отключённом JavaScript форма работает обычным POST-запросом;
 *   - пароль хранится только в виде хеша password_hash();
 *   - после успешной регистрации создаётся новая сессия (session_regenerate_id).
 */

require 'config/config.php';

$errors = [];       // ошибки по полям: ['email' => 'текст']
$general = '';      // общая ошибка (например, «email уже занят»)
$old = ['username' => '', 'email' => ''];

// AJAX-запрос приходит с заголовком X-Requested-With (см. assets/js/main.js).
$isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') || ($_POST['ajax'] ?? '') === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF-токен: без него запрос отклоняется.
    $tokenOk = hash_equals((string)($_SESSION['csrf_token'] ?? ''), (string)($_POST['csrf_token'] ?? ''));

    $username  = trim((string)($_POST['username'] ?? ''));
    $email     = trim((string)($_POST['email'] ?? ''));
    $password  = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password2'] ?? '');
    $old = ['username' => $username, 'email' => $email];

    if (!$tokenOk) {
        $general = 'Сессия истекла. Обновите страницу и попробуйте снова.';
    }

    // Имя: 3–30 символов, буквы (русские/латинские), цифры, подчёркивание и дефис.
    if (!preg_match('/^[A-Za-zА-Яа-яЁё0-9_-]{3,30}$/u', $username)) {
        $errors['username'] = 'Имя: 3–30 символов, буквы, цифры, «_» или «-».';
    }
    // Email: проверка формата и длины.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        $errors['email'] = 'Введите корректный email, например name@mail.ru.';
    }
    // Пароль: не короче 8 символов, буква и цифра.
    if (mb_strlen($password) < 8) {
        $errors['password'] = 'Пароль — минимум 8 символов.';
    } elseif (!preg_match('/[A-Za-zА-Яа-яЁё]/u', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Пароль должен содержать буквы и хотя бы одну цифру.';
    }
    if ($password !== $password2) {
        $errors['password2'] = 'Пароли не совпадают.';
    }

    // Проверка уникальности email и имени (подготовленный запрос).
    if (!$errors) {
        $chk = $pdo->prepare('SELECT username, email FROM users WHERE email = ? OR username = ? LIMIT 1');
        $chk->execute([$email, $username]);
        if ($existing = $chk->fetch()) {
            if (mb_strtolower($existing['email']) === mb_strtolower($email)) {
                $errors['email'] = 'Этот email уже зарегистрирован.';
            }
            if (mb_strtolower($existing['username']) === mb_strtolower($username)) {
                $errors['username'] = 'Такое имя уже занято.';
            }
        }
    }

    if (!$errors && $general === '') {
        // Хеширование пароля: в БД попадает только необратимый хеш.
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
        $ins->execute([$username, $email, $hash]);

        // Новая сессия после успешной регистрации — защита от фиксации сессии.
        session_regenerate_id(true);
        $_SESSION['user_id']   = (int)$pdo->lastInsertId();
        $_SESSION['username']  = $username;
        $_SESSION['role']      = 'user';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        flash('Добро пожаловать в NETFLIX, ' . $username . '!');

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => true, 'redirect' => 'profile.php'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        header('Location: profile.php');
        exit;
    }

    // Ответ для AJAX: ошибки по полям + общее сообщение.
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'      => false,
            'errors'  => $errors,
            'general' => $general,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$pageTitle = 'Регистрация · NETFLIX';
require 'includes/header.php';
?>
<div class="auth-wrap">
    <h1>Регистрация</h1>
    <p class="sub">Создайте аккаунт и смотрите кино без границ</p>

    <?php /* Общая ошибка (CSRF, неожиданная ситуация) */ ?>
    <div class="flash flash-error <?= $general === '' ? 'hidden' : '' ?>" id="formGeneral" style="margin-bottom:14px">
        <?= e($general) ?>
    </div>

    <?php /* Серверные ошибки при обычной отправке формы (без JS) */ ?>
    <?php foreach ($errors as $err): ?>
        <div class="flash flash-error" style="margin-bottom:10px"><?= e($err) ?></div>
    <?php endforeach; ?>

    <!-- data-register включает AJAX-обработку в assets/js/main.js -->
    <form method="post" action="register.php" data-register novalidate>
        <?= csrf_field() ?>

        <div class="field">
            <label for="reg-username">Имя пользователя</label>
            <input id="reg-username" type="text" name="username" required minlength="3" maxlength="30"
                   autocomplete="username" value="<?= e($old['username']) ?>">
            <div class="error" data-error-for="username">Введите имя пользователя</div>
        </div>

        <div class="field">
            <label for="reg-email">Email</label>
            <input id="reg-email" type="email" name="email" required maxlength="100"
                   autocomplete="email" value="<?= e($old['email']) ?>">
            <div class="error" data-error-for="email">Введите корректный email</div>
        </div>

        <div class="field">
            <label for="reg-password">Пароль</label>
            <input id="reg-password" type="password" name="password" required minlength="8"
                   autocomplete="new-password">
            <div class="error" data-error-for="password">Минимум 8 символов, буквы и цифры</div>
        </div>

        <div class="field">
            <label for="reg-password2">Повторите пароль</label>
            <input id="reg-password2" type="password" name="password2" required minlength="8"
                   autocomplete="new-password">
            <div class="error" data-error-for="password2">Пароли не совпадают</div>
        </div>

        <button class="btn btn-primary form-submit" type="submit">Создать аккаунт</button>
    </form>
    <p class="auth-switch">Уже есть аккаунт? <a href="login.php">Войти</a></p>
</div>
<?php require 'includes/footer.php'; ?>
