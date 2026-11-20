<?php
/**
 * favorites.php — избранное текущего пользователя.
 *
 * Доступ только после входа. У каждой карточки есть кнопка удаления:
 * она отправляет POST на toggle_favorite.php с CSRF-токеном.
 */

require 'config/config.php';
require_login();

$stmt = $pdo->prepare("SELECT c.* FROM favorites f
                       JOIN content c ON c.id = f.content_id
                       WHERE f.user_id = ? ORDER BY c.title");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

$pageTitle = 'Избранное · NETFLIX';
require 'includes/header.php';
?>
<section class="section" style="padding-top:36px">
    <div class="section-head">
        <h2 class="section-title">Избранное</h2>
        <span class="section-link"><?= count($items) ?> шт.</span>
    </div>

    <?php if ($items): ?>
        <div class="fav-grid">
            <?php foreach ($items as $item): ?>
                <div class="fav-item">
                    <?= render_card($item, $favIds) ?>
                    <?php /* Удаление из избранного — отдельная POST-форма с CSRF */ ?>
                    <form method="post" action="toggle_favorite.php" class="fav-remove">
                        <?= csrf_field() ?>
                        <input type="hidden" name="content_id" value="<?= (int)$item['id'] ?>">
                        <input type="hidden" name="back" value="favorites.php">
                        <button type="submit" class="btn btn-ghost btn-sm">Убрать из избранного</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="empty-state">
            Здесь пусто. Откройте фильм или сериал и нажмите «В избранное» —
            он появится в этом списке.
        </p>
        <a href="catalog.php" class="btn btn-primary" style="margin-top:16px">Перейти в каталог</a>
    <?php endif; ?>
</section>
<?php require 'includes/footer.php'; ?>
