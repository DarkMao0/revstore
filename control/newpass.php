<?php
require_once __DIR__ . '/functions.php';

$user = authorizedUserData();

$password = $_POST['password'] ?? null;
$confirm_password = $_POST['confirm_password'] ?? null;
$current_password = $_POST['current_password'] ?? null;
$passLength = 8;

if (empty($current_password)) {
    setError('current_password', 'Заполните поле');
}
elseif (!password_verify($current_password, $user['password'])) {
    setAlert('error', 'Неверный текущий пароль');
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

if (empty($_SESSION['validation']) && password_verify($current_password, $user['password'])) {
    $pdo = getPDO();

    $params = [
        'id' => $user['id'],
        'password' => password_hash($password, PASSWORD_DEFAULT, ['cost' => 11])
    ];
    $query = "UPDATE users SET password = :password WHERE id = :id";

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
}
else {
    redirect('/user/passchange.php');
}