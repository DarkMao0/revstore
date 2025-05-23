<?php
require_once __DIR__ . '/functions.php';

$login = $_POST['login'] ?? null;
$password = $_POST['password'] ?? null;
$user = findUser($login);

if (empty($login)) {
    setError('login', 'Заполните поле');
    redirect('/signin.php');
}
elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
    oldValue('login', $login);
    setError('login', 'Неверный формат почты');
    redirect('/signin.php');
}

if (empty($password)) {
    oldValue('login', $login);
    setError('password', 'Заполните поле');
    redirect('/signin.php');
}
elseif (empty($user)) {
    setAlert('error', 'Пользователь не существует');
    redirect('/signin.php');
}

if (!password_verify($password, $user['password']) && empty($password)) {
    oldValue('login', $login);
    setError('password', 'Заполните поле');
    redirect('/signin.php');
}
elseif (!password_verify($password, $user['password'])) {
    oldValue('login', $login);
    setAlert('error', 'Неверный пароль');
    redirect('/signin.php');
}

$_SESSION['user']['id'] = $user['id'];
redirect('/user/profile.php');