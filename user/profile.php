<?php
require_once __DIR__ . '/../control/functions.php';
$user = authorizedUserData();
denyNoUser();

$pdo = getPDO();
$user_id = $_SESSION['user']['id'] ?? null;

// Получаем все заказы пользователя
$stmt = $pdo->prepare("SELECT o.id, o.total, o.status, o.created_at FROM orders o WHERE o.user_id = :user_id ORDER BY o.created_at DESC");
$stmt->execute(['user_id' => $user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html  lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>REVSTORE - Профиль</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/user.css">
    <link rel="stylesheet" href="/css/order.css">
    <script defer src="/js/scroll.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php' ?>
<main class="content">
        <div class="con">
            <div class="main_dir">
                <div class="user_profile">
                    <?php if (isset($user['avatar'])): ?>
                        <img class="prof_avatar" src="<?php echo $user['avatar']; ?>">
                    <?php else: ?>
                        <img class="prof_avatar" src="/img/svg/prof.svg">
                    <?php endif; ?>
                    <h2>Здравствуйте,
                        <?php
                            $firstName = explode(' ', $user['name']);
                            echo $firstName[1];
                        ?>!
                    </h2>
                    <div class="user_actions">
                        <form action="/control/logout" method="post">
                            <button class="exit_but">Выйти из профиля</button>
                        </form>
                        <a href="/user/edit.php" class="prof_edit" title="Редактировать профиль">
                            <svg class="edit_sign" x="0px" y="0px" viewBox="0 0 101 111" fill="#00D4FF">
                                <path d="M59.2,21.7c7.8,7,15.4,13.9,23.3,20.9C66.3,60.7,50.2,78.6,34,96.8C26.1,
                                89.9,18.4,83.1,10.5,76 C26.7,58,42.9,39.9,59.2,21.7z"/>
                                <path d="M91.6,32.5c-7.8-7-15.5-13.9-23.2-20.8c2.3-2.7,4.5-5.4,6.8-7.9c0.9-1,
                                2.1-1.7,3.2-2.5c2-1.3,4.1-1.3,6.1,0 c1.6,1,3.1,2.2,4.5,3.5c2.9,2.5,5.7,5,8.5,
                                7.6c1.2,1.2,2.4,2.5,3.1,4c1.2,2.3,0.9,4.8-0.7,6.8C97.3,26.3,94.5,29.3,91.6,32.5z"/>
                                <path d="M8.3,78.2C16.2,85.3,24,92.3,32,99.4c-1.5,0.6-2.8,1.2-4.1,1.7c-7.3,
                                3-14.7,6-22,9c-3.3,1.4-6-0.4-5.5-3.9 c0.3-2.5,1.2-5,1.9-7.5C4.2,92.1,6.1,85.5,
                                8,78.9C8.1,78.7,8.2,78.6,8.3,78.2z"/>
                                <path d="M60.7,20.1c2-2.2,4-4.4,6.1-6.9c7.8,6.9,15.5,13.7,23.4,20.8c-2,2.3-4,
                                4.6-6.1,7C76.3,34,68.5,27.1,60.7,20.1z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <!-- Блок заказов -->
                <div class="user_orders">
                    <h3>Мои заказы</h3>
                    <?php if (empty($orders)): ?>
                        <div class="no_result">
                            <a>У вас пока нет заказов</a>
                        </div>
                    <?php else: ?>
                        <table class="prod">
                            <tr class="titles">
                                <td>ID заказа</td>
                                <td>Дата</td>
                                <td>Статус</td>
                                <td>Сумма</td>
                                <td>Подробнее</td>
                            </tr>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td data-label="ID заказа"><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td data-label="Дата"><?php echo htmlspecialchars($order['created_at']); ?></td>
                                    <td data-label="Статус"><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td data-label="Сумма"><?php echo htmlspecialchars($order['total']); ?> ₽</td>
                                    <td data-label="Подробнее">
                                    <a href="/user/order_confirmation.php?order_id=<?php echo htmlspecialchars($order['id']); ?>" class="product_interact">Открыть</a>
                                    <?php if (in_array($order['status'], ['в_обработке', 'отправлен'])): ?>
                                        <form action="/user/cancel_order" method="post" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                                            <button type="submit" class="product_interact" style="background:#f55;margin-left:8px;" onclick="return confirm('Вы уверены, что хотите отменить заказ?');">Отменить</button>
                                        </form>
                                    <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>
                <!-- /Блок заказов -->
            </div>
        </div>
    </main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>