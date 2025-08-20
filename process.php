<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/mail_error.log');

require 'vendor/autoload.php';
$config = require 'config.php';

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno] $errstr on line $errline in file $errfile");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Internal Server Error',
        'errors' => ['Техническая ошибка на сервере']
    ]);
    exit;
});

session_start();
header('Content-Type: application/json');

function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function sendFormMail($formData) {
    global $config;
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->SMTPDebug = 0;

        $mail->setFrom($config['smtp']['username'], 'Форма обратной связи');
        $mail->addAddress($formData['email']);
        $mail->addReplyTo($config['smtp']['username'], 'Форма обратной связи');

        $mail->isHTML(true);
        $mail->Subject = 'Новое сообщение с формы обратной связи';
        $mail->Body = "
            <h2>Новое сообщение с формы обратной связи</h2>
            <p><strong>Тема:</strong> " . htmlspecialchars($formData['topic'], ENT_QUOTES, 'UTF-8') . "</p>
            <p><strong>ФИО:</strong> " . htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8') . "</p>
            <p><strong>Телефон:</strong> " . htmlspecialchars($formData['phone'], ENT_QUOTES, 'UTF-8') . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8') . "</p>
            <p><strong>Сообщение:</strong><br>" . nl2br(htmlspecialchars($formData['message'], ENT_QUOTES, 'UTF-8')) . "</p>
        ";

        $mail->AltBody = strip_tags(str_replace('<br>', "\n", $mail->Body));

        if (!$mail->send()) {
            throw new Exception('Ошибка отправки почты: ' . $mail->ErrorInfo);
        }

        return true;

    } catch (Exception $e) {
        error_log("PHPMailer Exception: " . $e->getMessage());
        return false;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'message') {
        $response = ['success' => false, 'message' => '', 'errors' => []];

        $required_fields = ['topic', 'name', 'phone', 'email', 'message', 'captcha', 'privacy'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $response['errors'][] = "Поле {$field} обязательно для заполнения";
            }
        }

        if (!empty($response['errors'])) {
            echo json_encode($response);
            exit;
        }

        $formData = [
            'topic' => validateInput($_POST['topic']),
            'name' => validateInput($_POST['name']),
            'phone' => validateInput($_POST['phone']),
            'email' => validateInput($_POST['email']),
            'message' => validateInput($_POST['message'])
        ];

        if (strlen($formData['name']) > 255) {
            $response['errors'][] = 'ФИО не должно превышать 255 символов';
        }
        if (strlen($formData['email']) > 255) {
            $response['errors'][] = 'Email не должен превышать 255 символов';
        }
        if (strlen($formData['message']) > 4096) {
            $response['errors'][] = 'Сообщение не должно превышать 4096 символов';
        }
        if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $response['errors'][] = 'Неверный формат email';
        }

        $phoneDigits = preg_replace('/\D/', '', $formData['phone']);
        if (strlen($phoneDigits) !== 11) {
            $response['errors'][] = 'Телефон должен содержать 11 цифр';
        } elseif ($phoneDigits[0] !== '7') {
            $response['errors'][] = 'Телефон должен начинаться с 7';
        }

        if (!isset($_SESSION['captcha']) || $_POST['captcha'] !== $_SESSION['captcha']) {
            $response['errors'][] = 'Неверный код капчи';
        }

        if (!empty($response['errors'])) {
            echo json_encode($response);
            exit;
        }

        if (sendFormMail($formData)) {
            $response['success'] = true;
            $response['message'] = 'Сообщение успешно отправлено';
        } else {
            $response['message'] = 'Ошибка при отправке сообщения';
            $response['errors'][] = 'Техническая ошибка при отправке почты';
        }

        echo json_encode($response);
        exit;
    }
} catch (Exception $e) {
    error_log('Ошибка обработки формы: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Произошла ошибка при обработке запроса',
        'errors' => ['Внутренняя ошибка сервера']
    ]);
}
?>