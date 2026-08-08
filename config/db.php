<?php

$host = "localhost";
$user = "root";
$password = "root";   // If your MAMP password is empty, change this to ""
$database = "liger";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set("Asia/Riyadh");

?>