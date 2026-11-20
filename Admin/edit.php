<?php
/**
 * Admin/edit.php — создание и редактирование тайтла.
 *
 * ?id=0 или без параметра — создание новой записи.
 * ?id=N — редактирование существующей.
 *
 * Все поля проверяются на сервере, значения сохраняются подготовленным запросом.
 * При ошибках форма показывается повторно с введёнными значениями.
 */

require '../config/config.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

// Значения по умолчанию для новой записи.
$form = [
    'title'       => '',
    'type'        => 'movie',
    'description' => '',
    'poster'      => '',
    'backdrop'    => '',
    'trailer'     => '',
    'year'        => (int)date('Y'),
    'rating'      => '0.0',
    'duration'    => 0,
    'category_id' => 0,
    'is_new'      => 0,
    'is_popular'  => 0,
];

// --- Загрузка существующей записи -----------------------------------------
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM content WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        exit('Запись не найдена. <a href="index.php">Вернуться в админку</a>');
    }
    $form = array_merge($form, $row);
}

$errors = [];
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

// --- Сохранение ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $form['title']       = trim((string)($_POST['title'] ?? ''));
    $form['type']        = (string)($_POST['type'] ?? 'movie');
    $form['description'] = trim((string)($_POST['description'] ?? ''));
    $form['poster']      = trim((string)($_POST['poster'] ?? ''));
    $form['backdrop']    = trim((string)($_POST['backdrop'] ?? ''));
    $form['trailer']     = trim((string)($_POST['trailer'] ?? ''));
    $form['year']        = (int)($_POST['year'] ?? 0);
    $form['rating']      = (string)($_POST['rating'] ?? '0');
    $form['duration']    = (int)($_POST['duration'] ?? 0);
    $form['category_id'] = (int)($_POST['category_id'] ?? 0);
    $form['is_new']      = isset($_POST['is_new']) ? 1 : 0;
    $form['is_popular']  = isset($_POST['is_popular']) ? 1 : 0;

    // Название: обязательно, до 150 символов.
    if (mb_strlen($form['title']) < 1 || mb_strlen($form['title']) > 150) {
        $errors['title'] = 'Название должно быть от 1 до 150 символов.';
    }

    // Тип: только два допустимых значения.
    if (!in_array($form['type'], ['movie', 'series'], true)) {
        $errors['type'] = 'Выберите тип: фильм или сериал.';
    }

    // Категория должна существовать.
    $catIds = array_map(static fn($c) => (int)$c['id'], $categories);
    if ($form['category_id'] <= 0 || !in_array($form['category_id'], $catIds, true)) {
        $errors['category_id'] = 'Выберите категорию.';
    }

    // Год и рейтинг — разумные диапазоны.
    if ($form['year'] < 1888 || $form['year'] > (int)date('Y') + 2) {
        $errors['year'] = 'Год должен быть между 1888 и ' . ((int)date('Y') + 2) . '.';
    }
    $rating = (float)str_replace(',', '.', $form['rating']);
    if ($rating < 0 || $rating > 10) {
        $errors['rating'] = 'Рейтинг — от 0 до 10.';
    }
    $form['rating'] = number_format($rating, 1, '.', '');

    if ($form['duration'] < 0 || $form['duration'] > 600) {
        $errors['duration'] = 'Длительность — от 0 до 600 минут.';
    }

    // Ссылки: пусто или http(s)-URL.
    foreach (['poster', 'backdrop', 'trailer'] as $field) {
        if ($form[$field] !== '' && !filter_var($form[$field], FILTER_VALIDATE_URL)) {
            $errors[$field] = 'Укажите полный URL, начинающийся с http:// или https://';
        }
    }

    if (mb_strlen($form['description']) > 5000) {
        $errors['description'] = 'Описание — не больше 5000 символов.';
    }

    if (!$errors) {
        $data = [
            $form['title'],
            $form['type'],
            $form['description'],
            $form['poster'],
            $form['backdrop'],
            $form['trailer'],
            $form['year'],
            $form['rating'],
            $form['duration'],
            $form['category_id'],
            $form['is_new'],
            $form['is_popular'],
        ];

        if ($id > 0) {
            $data[] = $id;
            $upd = $pdo->prepare("UPDATE content SET
                title = ?, type = ?, description = ?, poster = ?, backdrop = ?, trailer = ?,
                year = ?, rating = ?, duration = ?, category_id = ?, is_new = ?, is_popular = ?
                WHERE id = ?");
            $upd->execute($data);
        } else {
            $ins = $pdo->prepare("INSERT INTO content
                (title, type, description, poster, backdrop, trailer, year, rating, duration, category_id, is_new, is_popular)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute($data);
            $id = (int)$pdo->lastInsertId();
        }

        flash('Сохранено: ' . $form['title']);
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $id > 0 ? 'Редактирование' : 'Новый тайтл' ?> · NETFLIX</title>
    <!-- Общий внешний CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">

<header class="admin-topbar">
    <a href="index.php" class="logo"><span class="logo-mark"><img src="../assets/img/favicon.svg" alt="NETFLIX"></span>NETFLIX · админка</a>
    <div class="admin-topbar-actions">
        <a href="index.php" class="btn btn-ghost">← К списку</a>
    </div>
</header>

<main class="admin-main">
    <div class="admin-form-wrap">
        <h1 class="section-title"><?= $id > 0 ? 'Редактировать тайтл' : 'Добавить тайтл' ?></h1>

        <?php if ($errors): ?>
            <div class="flash flash-error">Проверьте выделенные поля.</div>
        <?php endif; ?>

        <form method="post" action="edit.php<?= $id > 0 ? '?id=' . $id : '' ?>" class="admin-form">
            <?= csrf_field() ?>

            <div class="field <?= isset($errors['title']) ? 'invalid' : '' ?>">
                <label for="f-title">Название</label>
                <input id="f-title" name="title" type="text" maxlength="150" required value="<?= e($form['title']) ?>">
                <div class="error"><?= e($errors['title'] ?? 'Заполните название') ?></div>
            </div>

            <div class="field-row">
                <div class="field <?= isset($errors['type']) ? 'invalid' : '' ?>">
                    <label for="f-type">Тип</label>
                    <select id="f-type" name="type">
                        <option value="movie"  <?= $form['type'] === 'movie' ? 'selected' : '' ?>>Фильм</option>
                        <option value="series" <?= $form['type'] === 'series' ? 'selected' : '' ?>>Сериал</option>
                    </select>
                </div>

                <div class="field <?= isset($errors['category_id']) ? 'invalid' : '' ?>">
                    <label for="f-cat">Категория</label>
                    <select id="f-cat" name="category_id" required>
                        <option value="">— выберите —</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= (int)$form['category_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error"><?= e($errors['category_id'] ?? 'Выберите категорию') ?></div>
                </div>
            </div>

            <div class="field <?= isset($errors['description']) ? 'invalid' : '' ?>">
                <label for="f-desc">Описание</label>
                <textarea id="f-desc" name="description" rows="4"><?= e($form['description']) ?></textarea>
                <div class="error"><?= e($errors['description'] ?? 'Слишком длинное описание') ?></div>
            </div>

            <div class="field <?= isset($errors['poster']) ? 'invalid' : '' ?>">
                <label for="f-poster">URL постера</label>
                <input id="f-poster" name="poster" type="url" value="<?= e($form['poster']) ?>" placeholder="https://…">
                <div class="error"><?= e($errors['poster'] ?? 'Некорректный URL') ?></div>
            </div>

            <div class="field <?= isset($errors['backdrop']) ? 'invalid' : '' ?>">
                <label for="f-backdrop">URL фона (backdrop)</label>
                <input id="f-backdrop" name="backdrop" type="url" value="<?= e($form['backdrop']) ?>" placeholder="https://…">
                <div class="error"><?= e($errors['backdrop'] ?? 'Некорректный URL') ?></div>
            </div>

            <div class="field <?= isset($errors['trailer']) ? 'invalid' : '' ?>">
                <label for="f-trailer">Трейлер (embed-ссылка)</label>
                <input id="f-trailer" name="trailer" type="url" value="<?= e($form['trailer']) ?>"
                       placeholder="https://www.youtube.com/embed/…">
                <div class="error"><?= e($errors['trailer'] ?? 'Некорректный URL') ?></div>
            </div>

            <div class="field-row field-row-3">
                <div class="field <?= isset($errors['year']) ? 'invalid' : '' ?>">
                    <label for="f-year">Год</label>
                    <input id="f-year" name="year" type="number" min="1888" max="<?= (int)date('Y') + 2 ?>" value="<?= (int)$form['year'] ?>">
                    <div class="error"><?= e($errors['year'] ?? 'Неверный год') ?></div>
                </div>

                <div class="field <?= isset($errors['rating']) ? 'invalid' : '' ?>">
                    <label for="f-rating">Рейтинг (0–10)</label>
                    <input id="f-rating" name="rating" type="number" step="0.1" min="0" max="10" value="<?= e($form['rating']) ?>">
                    <div class="error"><?= e($errors['rating'] ?? 'Неверный рейтинг') ?></div>
                </div>

                <div class="field <?= isset($errors['duration']) ? 'invalid' : '' ?>">
                    <label for="f-duration">Длительность, мин</label>
                    <input id="f-duration" name="duration" type="number" min="0" max="600" value="<?= (int)$form['duration'] ?>">
                    <div class="error"><?= e($errors['duration'] ?? 'Неверная длительность') ?></div>
                </div>
            </div>

            <div class="field field-checks">
                <label><input type="checkbox" name="is_new" <?= $form['is_new'] ? 'checked' : '' ?>> Новинка</label>
                <label><input type="checkbox" name="is_popular" <?= $form['is_popular'] ? 'checked' : '' ?>> Популярное</label>
            </div>

            <button class="btn btn-primary" type="submit">Сохранить</button>
            <a href="index.php" class="btn btn-ghost">Отмена</a>
        </form>
    </div>
</main>
</body>
</html>
