<?php
require_once __DIR__ . '/functions.php';

$user = authorizedUserData();

$password = $_POST['password'] ?? null;

if (empty($password)) {
    setError('password', 'Заполните поле');
}
elseif (!password_verify($password, $user['password'])) {
    setAlert('error', 'Неверный пароль');
}

if (empty($_SESSION['validation']) && password_verify($password, $user['password'])) {
    $pdo = getPDO();

    $params = ['id' => $user['id']];

    $query = "DELETE FROM users WHERE id = :id";

    $stmt = $pdo->prepare($query);
    try {
        $stmt->execute($params);
        $_SESSION = array();
        session_destroy();
        redirect('/');
    }
    catch (\PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        require('../errors/500.php');
        die();
    }
}
else {
    redirect('/user/deleteprofile.php');
}