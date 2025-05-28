<?php

// Функция отправки письма через SMTP вручную (без сторонних библиотек)
function send_mail($to, $subject, $message): void {
    $smtp_server = "smtp.mail.ru";         // Адрес SMTP-сервера
    $smtp_port = 465;                      // Порт (465 используется для SSL)
    $smtp_user = "perveeva2025@mail.ru";   // Логин почты (отправитель)
    $smtp_pass = getenv("smtp_pass");      // Пароль берется из переменной окружения (безопаснее, чем в коде)

    // Заголовки письма
    $headers = "From: $smtp_user\r\n";
    $headers .= "Reply-To: $smtp_user\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Открываем SSL-соединение с SMTP-сервером
    $socket = fsockopen("ssl://$smtp_server", $smtp_port, $errno, $errstr, 30);
    if (!$socket) {
        echo "Ошибка подключения к SMTP: $errstr ($errno)";
        return;
    }

    // Отправка команд SMTP-серверу
    fputs($socket, "EHLO $smtp_server\r\n"); fgets($socket, 512);
    fputs($socket, "AUTH LOGIN\r\n"); fgets($socket, 512);
    fputs($socket, base64_encode($smtp_user) . "\r\n"); fgets($socket, 512);
    fputs($socket, base64_encode($smtp_pass) . "\r\n"); fgets($socket, 512);
    fputs($socket, "MAIL FROM: <$smtp_user>\r\n"); fgets($socket, 512);
    fputs($socket, "RCPT TO: <$to>\r\n"); fgets($socket, 512);
    fputs($socket, "DATA\r\n"); fgets($socket, 512);

    // Отправка темы, заголовков и тела письма
    fputs($socket, "Subject: $subject\r\n");
    fputs($socket, "$headers\r\n");
    fputs($socket, "$message\r\n");

    // Завершаем тело письма и закрываем соединение
    fputs($socket, ".\r\n"); fgets($socket, 512);
    fputs($socket, "QUIT\r\n"); fclose($socket);
}

// Запускаем сессию
session_start();

// Подключаем файл с настройками базы данных
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Получение и очистка данных из формы
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    $valid = true;

    // Проверка на пустые поля
    if (empty($name) || empty($email) || empty($phone)) {
        $valid = false;
    }

    // Валидация имени (только русские буквы, пробелы и дефисы)
    if (!preg_match('/^[А-Яа-яЁё\s\-]+$/u', $name)) $valid = false;

    // Проверка email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $valid = false;

    // Проверка телефона (должно быть 11 цифр и начинаться с 7)
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) !== 11 || $digits[0] !== '7') $valid = false;

    // Если валидация не прошла — прекращаем выполнение
    if (!$valid) {
        echo "Ошибка: данные не прошли проверку.";
        exit;
    }

    // === Ограничение частоты отправки формы: 1 раз в час ===
    $stmt = $pdo->prepare("SELECT created_at FROM feedback_requests WHERE email = :email ORDER BY created_at DESC LIMIT 1");
    $stmt->execute(['email' => $email]);
    $lastRequest = $stmt->fetchColumn();

    if ($lastRequest) {
        $lastTime = new DateTime($lastRequest);
        $now = new DateTime('now', new DateTimeZone('Europe/Moscow'));

        $interval = $now->getTimestamp() - $lastTime->getTimestamp();

        // Проверяем, прошло ли меньше часа
        if ($interval < 3600) {
            $remaining = 3600 - $interval;
            $minutes = floor($remaining / 60) - 180; // ??? -180 выглядит как ошибка
            $seconds = $remaining % 60;

            echo <<<HTML
<p style="color:red;"><strong>Вы уже отправляли заявку.</strong></p>
<p>Повторно можно оставить заявку через: <strong>{$minutes} мин {$seconds} сек</strong></p>
HTML;
            exit;
        }
    }

    // Разделяем ФИО на части: имя, фамилия, отчество
    $nameParts = explode(" ", $name);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';
    $middleName = $nameParts[2] ?? '';

    // Вычисляем будущее время контакта (через 4ч 30м)
    $next = new DateTime();
    $next->modify('+4 hour 30 minutes');
    $contactTime = $next->format('H:i:s d.m.Y');

    // Сохраняем текущее время (плюс 3 часа к серверному времени)
    $now = new DateTime();
    $now->modify('+3 hour');
    $NowTime = $now->format('Y-m-d H:i:s');

    // === Сохраняем данные в базу данных ===
    $stmt = $pdo->prepare("INSERT INTO feedback_requests 
        (first_name, last_name, middle_name, email, phone, comment, created_at) 
        VALUES (:first_name, :last_name, :middle_name, :email, :phone, :comment, :created_at)");

    try {
        $stmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => $middleName,
            'email' => $email,
            'phone' => $phone,
            'comment' => $comment,
            'created_at' => $NowTime
        ]);
    } catch (PDOException $e) {
        echo "Ошибка при сохранении в базу данных: " . $e->getMessage();
        exit;
    }

    // === Подготовка HTML-сообщения для письма ===
    $htmlMessage = "
        <h3>Оставлена новая заявка:</h3>
        <p><strong>Имя:</strong> $firstName</p>
        <p><strong>Фамилия:</strong> $lastName</p>
        <p><strong>Отчество:</strong> $middleName</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Телефон:</strong> $phone</p>
        <p><strong>Комментарий:</strong> $comment</p>
        <p><strong>Текущее время:</strong> $NowTime</p>
        <p>Свяжитесь с пользователем после: <strong>$contactTime</strong></p>
    ";

    // Отправка письма на указанный email
    send_mail("ed573808@gmail.com", "Новая заявка с формы", $htmlMessage);

    // === Вывод пользователю подтверждения ===
    echo <<<HTML
        <p>Оставлено сообщение из формы обратной связи.</p>
        <p>Имя: {$firstName}</p>
        <p>Фамилия: {$lastName}</p>
        <p>Отчество: {$middleName}</p>
        <p>E-mail: {$email}</p>
        <p>Телефон: {$phone}</p>
        <p>С Вами свяжутся после <strong>{$contactTime}</strong></p>
    HTML;
}
?>
