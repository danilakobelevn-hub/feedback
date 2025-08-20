<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма обратной связи</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
<div class="container">
    <form id="feedback-form" method="POST" action="process.php">
        <input type="hidden" name="action" value="message">

        <div class="form-group">
            <label for="topic">Тема</label>
            <select name="topic" id="topic" required>
                <option value="">Выберите тему</option>
                <option value="general">Общий вопрос</option>
                <option value="technical">Технический вопрос</option>
                <option value="commercial">Коммерческое предложение</option>
            </select>
        </div>

        <div class="form-group">
            <label for="name">ФИО</label>
            <input type="text" name="name" id="name" required maxlength="255">
        </div>

        <div class="form-group">
            <label for="phone">Телефон</label>
            <input type="tel" name="phone" id="phone" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" name="email" id="email" required maxlength="255">
        </div>

        <div class="form-group">
            <label for="message">Сообщение</label>
            <textarea name="message" id="message" required maxlength="4096"></textarea>
        </div>

        <div class="form-group">
            <label for="captcha">Капча</label>
            <div class="captcha-container">
                <div class="captcha-wrapper">
                    <img src="includes/captcha.php" alt="Капча" id="captcha-image">
                    <button type="button" id="refresh-captcha" class="captcha-refresh">↻</button>
                </div>
                <input type="text" name="captcha" id="captcha" required placeholder="Введите код с картинки">
            </div>
        </div>

        <div class="form-group">
            <label class="checkbox-container">
                <input type="checkbox" name="privacy" required>
                Согласен на обработку персональных данных
            </label>
        </div>

        <button type="submit">Отправить</button>
    </form>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>