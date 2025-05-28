<?php
// Параметры подключения к базе данных
$host = 'db';        // Название сервиса базы данных из docker-compose.yml (контейнер PostgreSQL)
$db   = 'mydb';      // Название базы данных
$user = 'user';      // Имя пользователя для подключения
$pass = 'userpass';  // Пароль пользователя
$charset = 'utf8';   // Кодировка (не используется напрямую в DSN для PostgreSQL)

// Строка подключения (DSN) для PostgreSQL
$dsn = "pgsql:host=$host;dbname=$db";

// Настройки PDO (опции подключения)
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Ошибки будут выбрасываться как исключения
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Результаты будут возвращаться как ассоциативные массивы
];

try {
    // Создаем объект PDO — подключение к базе данных
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Если возникла ошибка при подключении — выводим сообщение и останавливаем выполнение
    echo "Ошибка подключения к БД: " . $e->getMessage();
    exit;
}
?>
