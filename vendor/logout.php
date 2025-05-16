<?php
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
    logout();
}
redirect('/');