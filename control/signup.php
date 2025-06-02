<?php
require_once __DIR__ . '/functions.php';

$name = $_POST['name'] ?? null;
$login = $_POST['login'] ?? null;
$password = $_POST['password'] ?? null;
$confirm_password = $_POST['confirm_password'] ?? null;
$avatar = $_FILES['avatar'] ?? null;
$avatarPath = null;
$passLength = 8;
$maxAvatarSize = 3.5;

$user = findUser($login);

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

if (empty($password)) {
    setError('password', 'Заполните поле'); 
}
elseif (strlen($password) < $passLength) {
    setError('password', 'Минимальная длина пароля ' . $passLength . ' символов');
}

if (empty($confirm_password)) {
    setError('confirm_password', 'Заполните поле'); 
}
elseif (strlen($confirm_password) < $passLength) {
    setError('confirm_password', 'Минимальная длина пароля ' . $passLength . ' символов');
}

if ($password !== $confirm_password) {
    setError('password', 'Пароли не совпадают');
    setError('confirm_password', 'Пароли не совпадают'); 
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
}

if (!empty($user)) {
    oldValue('name', $name);
    setAlert('error', 'E-mail занят');
    redirect('/signup-view.php');
}

if (!empty($_SESSION['validation'])) {
    oldValue('name', $name);
    oldValue('login', $login);
    redirect('/signup-view.php');
}

$pdo = getPDO();

$query = "INSERT INTO users (name, login, password, avatar) VALUES (:name, :login, :password, :avatar)";

$params = [
    'name' => $name,
    'login' => $login,
    'password' => password_hash($password, PASSWORD_DEFAULT, ['cost' => 11]),
    'avatar' => $avatarPath ?? null
];

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

redirect('/signin-view.php');