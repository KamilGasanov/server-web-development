<?php
/** @var string $pageTitle */
/** @var string $headline */
/** @var string $message */
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

    <main class="card">
        <h1><?= htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <p><a href="/">Вернуться на главную</a></p>
    </main>
</div>
</body>
</html>
