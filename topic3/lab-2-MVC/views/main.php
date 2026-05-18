<?php
/** @var string $pageTitle */
$resolvedTitle = $pageTitle ?? 'Мой блог';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($resolvedTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page">
    <header class="header">Мой блог</header>

    <div class="layout">
        <main class="content">
            <h2>Статья 1</h2>
            <p>Всем привет, это текст первой статьи.</p>
            <hr>

            <h2>Статья 2</h2>
            <p>Всем привет, это текст второй статьи.</p>
        </main>

        <aside class="sidebar">
            <div class="sidebar-title">Меню</div>
            <ul>
                <li><a href="/">Главная страница</a></li>
                <li><a href="/about-me">Обо мне</a></li>
                <li><a href="/hello/Камиль">/hello/Камиль</a></li>
                <li><a href="/bye/Камиль">/bye/Камиль</a></li>
            </ul>
        </aside>
    </div>

    <footer class="footer">Все права защищены (c) Мой блог</footer>
</div>
</body>
</html>
