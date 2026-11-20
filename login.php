<?php
/**
 * login.php — вход в личный кабинет.
 *
 * Проверки:
 *   - CSRF-токен формы;
 *   - поиск пользователя по email или имени (подготовленный запрос);
 *   - password_verify() для сравнения с хешем из БД;
 *   - session_regenerate_id(true) после успешного входа.
 */

require 'config/config.php';

$error = '';
$loginValue = '';

// Куда вернуть пользователя после входа (?next=/profile.php или скрытое поле).
$next = (string)($_POST['next'] ?? $_GET['next'] ?? '');
if ($next !== '' && (!str_starts_with($next, '/') || str_starts_with($next, '//'))) {
    $next = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $loginValue = trim((string)($_POST['login'] ?? ''));
    $password   = (string)($_POST['password'] ?? '');

    if ($loginValue === '' || $password === '') {
        $error = 'Заполните email (или имя) и пароль.';
    } else {
        // Один подготовленный запрос: ищем и по email, и по имени пользователя.
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$loginValue, $loginValue]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Защита от фиксации сессии: выдаём новый идентификатор.
            session_regenerate_id(true);

            $_SESSION['user_id']            = (int)$user['id'];
            $_SESSION['username']           = $user['username'];
            $_SESSION['role']               = $user['role'];
            $_SESSION['subscription_until'] = $user['subscription_until'];

            flash('С возвращением, ' . $user['username'] . '!');
            header('Location: ' . ($next !== '' ? $next : 'profile.php'));
            exit;
        }

        // Одинаковый текст для неверного логина и пароля — не подсказываем, что существует.
        $error = 'Неверный логин или пароль.';
    }
}

$pageTitle = 'Вход · NETFLIX';
require 'includes/header.php';
?>
<div class="auth-wrap">
    <h1>Вход</h1>
    <p class="sub">Рады видеть вас снова</p>

    <?php if ($error): ?>
        <div class="flash flash-error" style="margin-bottom:14px"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php" data-validate>
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">

        <div class="field">
            <label for="login-name">Email или имя пользователя</label>
            <input id="login-name" type="text" name="login" required autocomplete="username"
                   value="<?= e($loginValue) ?>">
            <div class="error">Заполните поле</div>
        </div>

        <div class="field">
            <label for="login-password">Пароль</label>
            <input id="login-password" type="password" name="password" required autocomplete="current-password">
            <div class="error">Заполните поле</div>
        </div>

        <button class="btn btn-primary form-submit" type="submit">Войти</button>
    </form>

    <p class="auth-switch">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
</div>
<?php require 'includes/footer.php'; ?>
