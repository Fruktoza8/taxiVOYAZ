<?php
ini_set('session.use_only_cookies', true);
ini_set('session.cookie_httponly', true);
ini_set('session.gc_maxlifetime', 3600); // 1 час
ini_set('session.cookie_lifetime', 3600); // 1 час
session_start();
session_regenerate_id(true);

// Подключение к базе данных
require_once '../config/db.php';

$name = '';
$lastname = '';
$email = '';
$role = 'client'; // Установка роли по умолчанию
$error_message = ''; // Инициализация сообщения об ошибке

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из формы
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; // Добавьте это
// Проверка на содержание только русских букв в name и lastname
    if (!preg_match('/^[а-яё]+$/iu', $name) || !preg_match('/^[а-яё]+$/iu', $lastname)) {
        $error_message = "Имя и фамилия должны содержать только русские буквы";
    } else {
        if ($password !== $confirm_password) {
            $error_message = "Пароли не совпадают";
        } else {
            // Проверка сложности пароля
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
                $error_message = "Пароль должен содержать как минимум одну букву верхнего регистра, одну букву нижнего регистра и одну цифру, и иметь длину не менее 8 символов";
            } else {
                // Проверка уникальности email
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->rowCount() > 0) {
                    $error_message = "Этот email уже зарегистрирован";
                } else {
                    // Хэширование пароля
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Защита от SQL-инъекций
                    $stmt = $db->prepare('INSERT INTO users (name, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$name, $lastname, $email, $hashed_password, $role]); // Добавьте значение роли

                    // Сохранение пользователя в сессии
                    $_SESSION['name'] = $name;
                    $_SESSION['lastname'] = $lastname;

                    // Перед началом HTML
                    $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
                    $lastname = isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : '';
                    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
                    $role = isset($_POST['role']) ? $_POST['role'] : 'client'; // Установка роли по умолчанию


                    // Перенаправление на главную страницу
                    header('Location: ../index/index.php');
                    exit;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
    <script type="text/javascript" src="reg.js"></script>
</head>
<body>
<div class="form-container">
    <h1>Регистрация</h1>
    <form method="post" id="registration-form">
        <label for="name" class="no-hover">Имя:</label>
        <input type="text" id="name" name="name" required autocomplete="off" value="<?php echo $name; ?>">

        <label for="lastname" class="no-hover">Фамилия:</label>
        <input type="text" id="lastname" name="lastname" required autocomplete="off" value="<?php echo $lastname; ?>">

        <label for="email" class="no-hover">Email:</label>
        <input type="email" id="email" name="email" required value="<?php echo $email; ?>">


        <div class="role-container">
            <button type="button" class="role-button" data-role="client">Клиент</button>
            <button type="button" class="role-button" data-role="driver">Водитель</button>
            <input type="hidden" id="selected_role" name="role" value="<?php echo $role; ?>">
        </div>

        <label for="password" class="no-hover">Пароль:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password" class="no-hover">Подтверждение пароля:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <div id="error-message" class="error-message"></div>

        <button type="submit">Зарегистрироваться</button>
    </form>
</div>
</body>
</html>
