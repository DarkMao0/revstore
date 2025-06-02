<?php
require_once __DIR__ . '/functions.php';

$user = authorizedUserData();

$name = $_POST['name'] ?? null;
$login = $_POST['login'] ?? null;
$avatar = $_FILES['avatar'] ?? null;
$avatarPath = null;
$deleteAvatar = $_POST['delete_avatar'] ?? null;
$maxAvatarSize = 3.5;

$same_user = findUser($login);

$nameArray = explode(' ', $name);

if (count($nameArray) > 3 || count($nameArray) < 2) {
    setError('name', 'Введите корректное ФИО');
}

if (empty($name)) {
    setError('name', 'Заполните поле');
}
elseif (preg_match("/[0-9$*<>]/", $name)) {
    setError('name', 'Некорректные символы');
}

if (empty($login)) {
    setError('login', 'Заполните поле');
}
elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
    setError('login', 'Неверный формат почты');
}

if ($deleteAvatar === 'delete') {
    if (!empty($user['avatar']) && file_exists(__DIR__ . '/../uploads/avatar/' . $user['avatar'])) {
        unlink(__DIR__ . '/../uploads/avatar/' . $user['avatar']);
    }
    else {
        $avatarPath = null;
    }
}

if ($avatar && $avatar['error'] === UPLOAD_ERR_OK) {
    $types = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif', 'image/webp'];

    if (!in_array($avatar['type'], $types)) {
        setError('avatar', 'Неверный тип изображения');
    }

    if (($avatar['size'] / 1024 / 1024) >= $maxAvatarSize) {
        setError('avatar', 'Максимальный размер изображения ' . $maxAvatarSize . ' МБ');
    }

    if (empty($_SESSION['validation'])) {
        $avatarPath = uploadFile($avatar, 'avatar');
    }
    else {
        $avatarPath = $user['avatar'];
    }
}

if (!empty($same_user) && $same_user['login'] !== $user['login']) {
    setAlert('error', 'E-mail занят');
    redirect('/user/edit.php');
}

if (!empty($_SESSION['validation'])) {
    redirect('/user/edit.php');
}

$pdo = getPDO();

$params = [
    'id' => $user['id'],
    'name' => $name,
    'login' => $login,
];

$query = "UPDATE users SET name = :name, login = :login";

if ($avatarPath !== null) {
    $query .= ", avatar = :avatar";
    $params['avatar'] = $avatarPath;
}
elseif ($deleteAvatar === 'delete') {
    $query .= ", avatar = NULL";
}

$query .= " WHERE id = :id";

$stmt = $pdo->prepare($query);
try {
    $stmt->execute($params);
}
catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require('../errors/500.php');
    die();
}

redirect('/user/profile.php');