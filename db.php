<?php

$conn = mysqli_init();

mysqli_ssl_set($conn, null, null, null, null, null);

$conn->real_connect(
    getenv('DB_HOST'),
    getenv('DB_USER'),
    getenv('DB_PASSWORD'),
    getenv('DB_NAME'),
    (int)getenv('DB_PORT'),
    null,
    MYSQLI_CLIENT_SSL
);

if ($conn->connect_errno) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
