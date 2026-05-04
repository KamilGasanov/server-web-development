<?php
$messages = [
    "Hello, World!",
    "Добро пожаловать на страницу!",
    "Это динамический контент на PHP",
    "Серверная веб-разработка"
];

$randomMessage = $messages[array_rand($messages)];
$currentDate = date("d.m.Y");
$currentTime = date("H:i:s");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Домашняя работа: Hello, World!</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="logo">МосПолитех</div>
    <h1>Домашняя работа: Hello, World!</h1>
</header>

<main>
    <section class="card">
        <h2><?php echo $randomMessage; ?></h2>
        <p>Сегодня: <?php echo $currentDate; ?></p>
        <p>Текущее время сервера: <?php echo $currentTime; ?></p>
        <p>
            Эта страница создана с использованием PHP.
            Контент на странице является динамическим, потому что сообщение и время формируются на сервере.
        </p>
    </section>
</main>

<footer>
    <p>задание для самостоятельной работы</p>
</footer>

</body>
</html>