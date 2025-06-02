<?php
session_start();

require_once __DIR__ . '/connect.php';

// Общие функции
function redirect(string $path): void
{
    header("Location: $path");
    die();
}

function backSpam(): void
{
    redirect($_SERVER['HTTP_REFERER'] . '#spam_form');
}

// Функции пользователя
function setError(string $field, string $message): void
{
    $_SESSION['validation'][$field] = $message;
}

function checkError(string $field): bool
{
    return isset($_SESSION['validation'][$field]);
}

function errorFrame(string $field): string
{
    return isset($_SESSION['validation'][$field]) ? 'aria-invalid="true"' : '';
}

function errorMessage(string $field): string
{
    $message = $_SESSION['validation'][$field] ?? '';
    unset($_SESSION['validation'][$field]);
    return $message;
}

function oldValue(string $key, mixed $value): void //если версия php ниже 8.x поменять: mixed => string
{
    $_SESSION['old'][$key] = $value;
}

function old(string $key)
{
    $value = $_SESSION['old'][$key] ?? '';
    unset($_SESSION['old'][$key]);
    return $value;
}

function uploadFile(array $file, string $prefix = ''): ?string
{
    if (empty($file['tmp_name'])) {
        return null;
    }

    $upload = __DIR__ . '/../uploads/avatar';

    if (!is_dir($upload)) {
        mkdir($upload, 0777, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $avatarName = $prefix . '_' . time() . ".$ext";

    if (!move_uploaded_file($file['tmp_name'], "$upload/$avatarName")) {
        die();
    }
    
    return "/uploads/avatar/$avatarName";
}

// Новая функция для загрузки изображений отзыва
function uploadReviewImages(array $files, int $review_id): array
{
    $uploadedPaths = [];

    if (empty($files['tmp_name'])) {
        return $uploadedPaths;
    }

    $uploadDir = __DIR__ . '/../uploads/reviews';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Ограничение на типы файлов (только изображения)
    $allowedTypes = ['image/jpeg', 'image/png'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    foreach ($files['tmp_name'] as $index => $tmpName) {
        if (empty($tmpName)) {
            continue; // Пропускаем пустые файлы
        }

        $fileName = $files['name'][$index];
        $fileType = $files['type'][$index];
        $fileSize = $files['size'][$index];
        $fileError = $files['error'][$index];

        // Проверка ошибок загрузки
        if ($fileError !== UPLOAD_ERR_OK) {
            error_log("uploadReviewImages - File upload error for file $fileName: Error code $fileError");
            continue;
        }

        // Проверка типа файла
        if (!in_array($fileType, $allowedTypes)) {
            error_log("uploadReviewImages - Invalid file type for file $fileName: $fileType");
            continue;
        }

        // Проверка размера файла
        if ($fileSize > $maxFileSize) {
            error_log("uploadReviewImages - File too large for file $fileName: $fileSize bytes");
            continue;
        }

        // Генерация уникального имени файла
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = "review_{$review_id}_" . time() . "_$index.$ext";
        $destination = "$uploadDir/$newFileName";

        if (move_uploaded_file($tmpName, $destination)) {
            $relativePath = "/uploads/reviews/$newFileName";
            $uploadedPaths[] = $relativePath;

            // Сохранение пути в таблицу review_images
            $pdo = getPDO();
            $stmt = $pdo->prepare("INSERT INTO review_images (review_id, image_path) VALUES (:review_id, :image_path)");
            $stmt->execute(['review_id' => $review_id, 'image_path' => $relativePath]);
        } else {
            error_log("uploadReviewImages - Failed to move uploaded file $fileName to $destination");
        }
    }

    return $uploadedPaths;
}

function setAlert(string $key, string $message): void
{
    $_SESSION['message'][$key] = $message;
}

function checkAlert(string $key): bool
{
    return isset($_SESSION['message'][$key]);
}

function getAlert(string $key): string
{
    $message = $_SESSION['message'][$key] ?? '';
    unset($_SESSION['message'][$key]);
    return $message;
}

function findUser(string $login): array|bool
{
    $pdo = getPDO();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = :login");
    $stmt->execute(['login' => $login]);
    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

function findSubscriber(string $email): array|bool
{
    $pdo = getPDO();

    $stmt = $pdo->prepare("SELECT * FROM spam WHERE email = :email");
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

function authorizedUserData(): array|false
{
    $pdo = getPDO();
    
    if (!isset($_SESSION['user'])) {
        return false;
    }

    $userId = $_SESSION['user']['id'] ?? null;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

function logout(): void
{
    unset($_SESSION['user']['id']);
    redirect('/signin-view.php');
}

function denyNoUser(): void
{
    if (!isset($_SESSION['user']['id'])) {
        redirect('/signin-view.php');
    }
}

function denyUser(): void
{
    if (isset($_SESSION['user']['id'])) {
        redirect('/user/profile.php');
    }
}

function denyNoAdmin(): void
{
    if ($_SESSION['user']['status'] !== 'administrator') {
        redirect('/');
    }
}

function denyAdmin(): void
{
    if ($_SESSION['user']['status'] === 'administrator') {
        redirect('/user/profile.php');
    }
}

// Функции каталога
function searchQuery(array &$queryBuilder, array &$queryParams, array $getArray): void {
    if (isset($getArray['search']) && trim($getArray['search']) !== '') {
        $searchValue = '%' . trim($getArray['search']) . '%';
        $queryBuilder[] = "p.name LIKE ?";
        $queryParams[] = $searchValue;
    }
}

function sortQuery(string &$query, array $getArray): void {
    if (isset($getArray['sort'])) {
        switch ($getArray['sort']) {
            case 'price_asc':
                $query .= " ORDER BY price ASC";
                break;
            case 'price_desc':
                $query .= " ORDER BY price DESC";
                break;
            case 'sale':
                $query .= " ORDER BY sale DESC, price ASC";
                break;
        }
    }
}

// Функции корзины
function addToCart(int $product_id): void
{
    $pdo = getPDO();
    $user_id = $_SESSION['user']['id'] ?? null;
    if ($user_id === null) {
        redirect('/signin-view.php');
    }

    $stmt = $pdo->prepare("SELECT * FROM cart WHERE userID = :userID AND productID = :productID");
    $stmt->execute(['userID' => $user_id, 'productID' => $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($item)) {
        $newQuantity = $item['quantity'] + 1;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE userID = :userID AND productID = :productID");
        $stmt->execute(['quantity' => $newQuantity, 'userID' => $user_id, 'productID' => $product_id]);
    }
    else {
        $stmt = $pdo->prepare("INSERT INTO cart (userID, productID, quantity) VALUES (:userID, :productID, 1)");
        $stmt->execute(['userID' => $user_id, 'productID' => $product_id]);
    }
}

function addToWishlist(int $product_id): void
{
    $pdo = getPDO();
    $user_id = $_SESSION['user']['id'] ?? null;
    if ($user_id === null) {
        redirect('/signin-view.php');
    }

    $stmt = $pdo->prepare("SELECT * FROM wishlist WHERE userID = :userID AND productID = :productID");
    $stmt->execute(['userID' => $user_id, 'productID' => $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($item)) {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE userID = :userID AND productID = :productID");
        $stmt->execute(['userID' => $user_id, 'productID' => $product_id]);
    }
    else {
        $stmt = $pdo->prepare("INSERT INTO wishlist (userID, productID) VALUES (:userID, :productID)");
        $stmt->execute(['userID' => $user_id, 'productID' => $product_id]);
    }
}

function checkWishlist(int $product_id): bool
{
    $pdo = getPDO();
    $user_id = $_SESSION['user']['id'] ?? null;
    if ($user_id === null) {
        return false;
    }

    $stmt = $pdo->prepare("SELECT * FROM wishlist WHERE userID = :userID AND productID = :productID");
    $stmt->execute(['userID' => $user_id, 'productID' => $product_id]);
    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}

// Функция для добавления отзыва
function addReview(int $product_id, ?int $rating, ?string $comment, array $user, array $images = []): array
{
    $response = ['success' => false, 'message' => ''];

    // Валидация product_id
    if ($product_id <= 0) {
        $response['message'] = 'Неверный ID товара';
        return $response;
    }

    // Валидация rating
    if ($rating === null || $rating < 1 || $rating > 5) {
        $response['message'] = 'Рейтинг должен быть от 1 до 5';
        return $response;
    }

    // Валидация comment
    if (empty($comment)) {
        $response['message'] = 'Заполните поле с комментарием';
        return $response;
    }

    if (strlen($comment) > 250) {
        $response['message'] = 'Максимальная длина комментария 250 символов';
        return $response;
    }

    // Проверка существования товара
    $pdo = getPDO();
    $productStmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $productStmt->execute([$product_id]);
    if ($productStmt->rowCount() === 0) {
        $response['message'] = 'Товар не найден';
        return $response;
    }

    // Проверка, есть ли уже отзыв от этого пользователя для данного товара
    $userId = $user['id'];
    $checkReviewStmt = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
    $checkReviewStmt->execute([$product_id, $userId]);
    if ($checkReviewStmt->rowCount() > 0) {
        $response['message'] = 'Вы уже оставили отзыв для этого товара';
        return $response;
    }

    // Добавление отзыва
    $query = "INSERT INTO reviews (product_id, user_id, user_name, rating, comment) VALUES (:product_id, :user_id, :user_name, :rating, :comment)";
    $params = [
        'product_id' => $product_id,
        'user_id' => $userId,
        'user_name' => $user['name'],
        'rating' => $rating,
        'comment' => $comment
    ];

    $stmt = $pdo->prepare($query);
    try {
        if ($stmt->execute($params)) {
            $review_id = $pdo->lastInsertId();

            // Загрузка изображений
            if (!empty($images['tmp_name'])) {
                $uploadedImages = uploadReviewImages($images, $review_id);
                if (empty($uploadedImages) && count(array_filter($images['tmp_name'])) > 0) {
                    $response['message'] = 'Отзыв добавлен, но не удалось загрузить изображения';
                } else {
                    $response['success'] = true;
                    $response['message'] = 'Отзыв успешно добавлен';
                }
            } else {
                $response['success'] = true;
                $response['message'] = 'Отзыв успешно добавлен';
            }
        } else {
            $response['message'] = 'Не удалось добавить отзыв';
        }
    } catch (\PDOException $e) {
        error_log("Failed to add review for product_id $product_id: " . $e->getMessage());
        $response['message'] = 'Ошибка сервера при добавлении отзыва';
    }

    return $response;
}

// Функция для редактирования отзыва
function updateReview(int $review_id, int $rating, string $comment, array $user, array $images = []): array
{
    $response = ['success' => false, 'message' => ''];

    // Валидация review_id
    if ($review_id <= 0) {
        $response['message'] = 'Неверный ID отзыва';
        return $response;
    }

    // Валидация rating
    if ($rating < 1 || $rating > 5) {
        $response['message'] = 'Рейтинг должен быть от 1 до 5';
        return $response;
    }

    // Валидация comment
    if (empty($comment)) {
        $response['message'] = 'Заполните поле с комментарием';
        return $response;
    }

    if (strlen($comment) > 250) {
        $response['message'] = 'Максимальная длина комментария 250 символов';
        return $response;
    }

    // Проверка существования отзыва и принадлежности пользователю
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT user_id, product_id FROM reviews WHERE id = ?");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        $response['message'] = 'Отзыв не найден';
        return $response;
    }

    if ($review['user_id'] != $user['id']) {
        $response['message'] = 'Вы не можете редактировать этот отзыв';
        return $response;
    }

    // Обновление отзыва
    $query = "UPDATE reviews SET rating = :rating, comment = :comment WHERE id = :review_id";
    $params = [
        'rating' => $rating,
        'comment' => $comment,
        'review_id' => $review_id
    ];

    $stmt = $pdo->prepare($query);
try {
    if ($stmt->execute($params)) {
        if (!empty($images['tmp_name']) && is_array($images['tmp_name']) && count(array_filter($images['tmp_name'])) > 0) {
            // 1. Получаем старые изображения
            $stmtImg = $pdo->prepare("SELECT image_path FROM review_images WHERE review_id = ?");
            $stmtImg->execute([$review_id]);
            $oldImages = $stmtImg->fetchAll(PDO::FETCH_COLUMN);

            foreach ($oldImages as $imgPath) {
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imgPath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $pdo->prepare("DELETE FROM review_images WHERE review_id = ?")->execute([$review_id]);

            $uploadedImages = uploadReviewImages($images, $review_id);
            if (empty($uploadedImages) && count(array_filter($images['tmp_name'])) > 0) {
                $response['message'] = 'Отзыв обновлён, но не удалось загрузить новые изображения';
            } else {
                $response['success'] = true;
                $response['message'] = 'Отзыв успешно обновлён';
            }
        } else {
            // Если новых изображений нет — старые остаются
            $response['success'] = true;
            $response['message'] = 'Отзыв успешно обновлён';
        }
    } else {
        $response['message'] = 'Не удалось обновить отзыв';
    }
} catch (\PDOException $e) {
    error_log("Failed to update review for review_id $review_id: " . $e->getMessage());
    $response['message'] = 'Ошибка сервера при обновлении отзыва';
}

    return $response;
}

// Функция для удаления отзыва
function deleteReview(int $review_id, array $user): array
{
    $response = ['success' => false, 'message' => ''];

    // Валидация review_id
    if ($review_id <= 0) {
        $response['message'] = 'Неверный ID отзыва';
        return $response;
    }

    // Проверка существования отзыва и принадлежности пользователю
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT user_id, product_id FROM reviews WHERE id = ?");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        $response['message'] = 'Отзыв не найден';
        return $response;
    }

    if ($review['user_id'] != $user['id']) {
        $response['message'] = 'Вы не можете удалить этот отзыв';
        return $response;
    }

    // Удаляем изображения с диска (если есть)
    $imgStmt = $pdo->prepare("SELECT image_path FROM review_images WHERE review_id = ?");
    $imgStmt->execute([$review_id]);
    $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($images as $imgPath) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imgPath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    // Удаление отзыва (изображения из БД удалятся через ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    try {
        if ($stmt->execute([$review_id])) {
            $response['success'] = true;
            $response['message'] = 'Отзыв успешно удалён';
        } else {
            $response['message'] = 'Не удалось удалить отзыв';
        }
    } catch (\PDOException $e) {
        error_log("Failed to delete review for review_id $review_id: " . $e->getMessage());
        $response['message'] = 'Ошибка сервера при удалении отзыва';
    }

    return $response;
}

// Функция для получения URL страницы товара
function getProductUrl(int $product_id): string
{
    return "/product-view.php?id=" . $product_id;
}

// Функция для преобразования числового рейтинга в ранг
function getRankFromRating(float $rating): string
{
    $roundedRating = round($rating); // Округляем средний рейтинг
    $rankMap = [5 => 'S', 4 => 'A', 3 => 'B', 2 => 'C', 1 => 'D'];
    return $rankMap[$roundedRating] ?? 'D';
}
?>