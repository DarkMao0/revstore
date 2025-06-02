<?php
require_once __DIR__ . '/functions.php';

$spam_email = $_POST['email'] ?? null;
$subscriber = findSubscriber($spam_email);

if (empty($spam_email)) {
    setError('email', 'Заполните поле');
    backSpam();
    die();
}
elseif (!filter_var($spam_email, FILTER_VALIDATE_EMAIL)) {
    setError('email', 'Неверный формат почты');
    backSpam();
    die();
}

if (!empty($subscriber)) {
    setError('email', 'Вы уже подписаны');
    backSpam();
}

$pdo = getPDO();

$query = "INSERT INTO spam (email) VALUES (:email)";

$params = [
    'email' => $spam_email
];

$stmt = $pdo->prepare($query);
try {
    $stmt->execute($params);
    setError('email', 'Спасибо!');
}
catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require('../errors/500.php');
    die();
}

backSpam();