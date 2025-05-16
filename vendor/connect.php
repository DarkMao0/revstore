<?php
const DB_HOST = 'localhost';
const DB_PORT = '3306';
const DB_NAME = 'mydb';
const DB_USERNAME = 'root';
const DB_PASSWORD = '';

function getPDO(): PDO
{
    try {
        return new \PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8;dbname=' . DB_NAME, DB_USERNAME, DB_PASSWORD);
    }
    catch (\PDOException $e) {
        error_log($e->getMessage());
        http_response_code(503);
        require('errors/503.php');
        die();
    }
}