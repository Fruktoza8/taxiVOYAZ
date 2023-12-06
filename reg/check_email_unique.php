<?php
require_once '../config/db.php'; // Подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    echo $stmt->rowCount() > 0 ? 'exists' : 'not_exists';
}
