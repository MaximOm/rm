<?php
header('Content-Type: application/json');

// Функция для логирования сообщений
function logMessage($message) {
    $logFile = 'contact_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "$timestamp - $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Проверка, что запрос отправлен методом POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    $phone = $_POST['phone'] ?? '';

    // Простая валидация
    if (empty($name) || empty($email) || empty($message) || empty($phone)) {
//        echo json_encode(['success' => false, 'message' => 'Все поля должны быть заполнены']);
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'The email address is not valid']);
        exit;
    }

    // Формирование сообщения для отправки
//    $to = 'info@ripheanmarble.com'; // Замените на свой email
    $to = 'maksim-matisanov@ya.ru'; // Замените на свой email
    $subject = 'New message from website';
    $email_message = "Name: $name\n";
    $email_message .= "Phone: $phone\n";
    $email_message .= "Email: $email\n";
    $email_message .= "Message:\n$message";

    // Заголовки письма
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Отправка email
    if (mail($to, $subject, $email_message, $headers)) {
        // Логирование успешной отправки
        logMessage("Message sent to $name ($email)");
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        // Логирование ошибки
        logMessage("Ошибка отправки сообщения от $name ($email)");
        echo json_encode(['success' => false, 'message' => 'Error sending message']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>