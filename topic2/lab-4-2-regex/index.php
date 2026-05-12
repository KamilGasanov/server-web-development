<?php
declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$task1Input = 'aaab aaaaab baaab aaabx';
$task1Pattern = '/(?<!a)aaa(?=b)/';
$task1Replacement = '!';
$task1Result = preg_replace($task1Pattern, $task1Replacement, $task1Input);

$task2Input = 'aa aba abba abbba abbbba abbbbba';
$task2Pattern = '/ab{4,}a/';
$task2Matches = [];
preg_match_all($task2Pattern, $task2Input, $task2Matches);

$task3Input = 'aba aca aea abba adca abea';
$task3Pattern = '/ab[be]a/';
$task3Matches = [];
preg_match_all($task3Pattern, $task3Input, $task3Matches);

$task4Input = 'aae xxz 33a';
$task4Pattern = '/(.)\\1/u';
$task4Replacement = '!';
$task4Result = preg_replace($task4Pattern, $task4Replacement, $task4Input);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regex на PHP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <h1>Решение задач по regex на PHP</h1>

    <section class="task">
        <h2>1. Заменить `aaab` на `!b`</h2>
        <p><strong>Строка:</strong> <code><?= h($task1Input) ?></code></p>
        <p><strong>Регулярка:</strong> <code><?= h($task1Pattern) ?></code></p>
        <p><strong>Замена:</strong> <code><?= h($task1Replacement) ?></code></p>
        <p><strong>Результат:</strong> <code><?= h((string) $task1Result) ?></code></p>
    </section>

    <section class="task">
        <h2>2. Найти строки вида `ab...a`, где `b` не меньше 4</h2>
        <p><strong>Строка:</strong> <code><?= h($task2Input) ?></code></p>
        <p><strong>Регулярка:</strong> <code><?= h($task2Pattern) ?></code></p>
        <p><strong>Совпадения:</strong> <code><?= h(implode(', ', $task2Matches[0])) ?></code></p>
    </section>

    <section class="task">
        <h2>3. Найти `abba` и `abea`, не захватив `adca`</h2>
        <p><strong>Строка:</strong> <code><?= h($task3Input) ?></code></p>
        <p><strong>Регулярка:</strong> <code><?= h($task3Pattern) ?></code></p>
        <p><strong>Совпадения:</strong> <code><?= h(implode(', ', $task3Matches[0])) ?></code></p>
    </section>

    <section class="task">
        <h2>4. Заменить два одинаковых подряд символа на `!`</h2>
        <p><strong>Строка:</strong> <code><?= h($task4Input) ?></code></p>
        <p><strong>Регулярка:</strong> <code><?= h($task4Pattern) ?></code></p>
        <p><strong>Замена:</strong> <code><?= h($task4Replacement) ?></code></p>
        <p><strong>Результат:</strong> <code><?= h((string) $task4Result) ?></code></p>
    </section>
</main>
</body>
</html>
