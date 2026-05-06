<?php
// index.php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Feedback Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="logo">МосПолитех</div>
    <h1>Лабораторная работа: Feedback Form</h1>
</header>

<main>
    <form action="https://httpbin.org/post" method="post" target="_blank">
        <label>Имя пользователя:<br>
            <input type="text" name="username" required>
        </label><br><br>

        <label>E-mail пользователя:<br>
            <input type="email" name="email" required>
        </label><br><br>

        <label>Тип обращения:<br>
            <select name="type">
                <option value="complaint">Жалоба</option>
                <option value="suggestion">Предложение</option>
                <option value="thanks">Благодарность</option>
            </select>
        </label><br><br>

        <label>Текст обращения:<br>
            <textarea name="message" rows="5"></textarea>
        </label><br><br>

        <label>Вариант ответа:<br>
            <input type="checkbox" name="reply[]" value="sms"> SMS
            <input type="checkbox" name="reply[]" value="email"> E-mail
        </label><br><br>

        <button type="submit">Отправить</button>
    </form>

    <a href="page2.php">Перейти на страницу с get_headers</a>
</main>

<footer>
    <p>задание для самостоятельной работы</p>
</footer>

</body>
</html>
