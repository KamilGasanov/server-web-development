<?php
date_default_timezone_set("Europe/Moscow");

$url = "https://httpbin.org/get";
$headers = get_headers($url);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Feedback Form - Headers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="logo">МосПолитех</div>
    <h1>Результат работы get_headers</h1>
</header>

<main>
    <textarea rows="15" cols="80" readonly>
        <?php
        foreach ($headers as $line) {
            echo $line . "\n";
        }
        ?>
    </textarea>
    <a href="index.php">Вернуться на форму</a>
</main>

<footer>
    <p>задание для самостоятельной работы</p>
</footer>

</body>
</html>
