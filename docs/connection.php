<?php
include "credentials.php";

$connection = new mysqli("localhost", $user, $pw, $db);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$connection->set_charset("utf8mb4");
