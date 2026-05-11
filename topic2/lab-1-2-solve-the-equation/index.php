<?php
declare(strict_types=1);

$equation = '6 / X = 2;';

function solveEquation(string $equation): array
{
    $normalized = strtolower(str_replace([' ', ';'], '', $equation));

    if (substr_count($normalized, '=') !== 1) {
        throw new InvalidArgumentException('Уравнение должно содержать один знак равенства.');
    }

    [$leftSide, $rightSide] = explode('=', $normalized, 2);

    $leftHasX = str_contains($leftSide, 'x');
    $rightHasX = str_contains($rightSide, 'x');

    if ($leftHasX === $rightHasX) {
        throw new InvalidArgumentException('Не удалось однозначно определить сторону с неизвестной переменной.');
    }

    $expressionSide = $leftHasX ? $leftSide : $rightSide;
    $valueSide = $leftHasX ? $rightSide : $leftSide;
    $equationSide = $leftHasX ? 'левая' : 'правая';

    if (!is_numeric($valueSide)) {
        throw new InvalidArgumentException('Сторона без X должна быть числом.');
    }

    if (!preg_match('/^(x|-?\d+(?:\.\d+)?)([+\-*\/])(x|-?\d+(?:\.\d+)?)$/', $expressionSide, $matches)) {
        throw new InvalidArgumentException('Поддерживаются только уравнения вида a op x или x op a.');
    }

    $leftOperand = $matches[1];
    $operator = $matches[2];
    $rightOperand = $matches[3];
    $resultValue = (float) $valueSide;

    if ($leftOperand === 'x' && $rightOperand === 'x') {
        throw new InvalidArgumentException('В выражении должна быть только одна неизвестная переменная.');
    }

    if ($leftOperand === 'x') {
        $known = (float) $rightOperand;
        $position = 'X стоит слева от оператора';

        $x = match ($operator) {
            '+' => $resultValue - $known,
            '-' => $resultValue + $known,
            '*' => $known == 0.0 ? throw new InvalidArgumentException('Деление на ноль невозможно.') : $resultValue / $known,
            '/' => $resultValue * $known,
            default => throw new InvalidArgumentException('Неизвестный оператор.')
        };
    } elseif ($rightOperand === 'x') {
        $known = (float) $leftOperand;
        $position = 'X стоит справа от оператора';

        $x = match ($operator) {
            '+' => $resultValue - $known,
            '-' => $known - $resultValue,
            '*' => $known == 0.0 ? throw new InvalidArgumentException('Деление на ноль невозможно.') : $resultValue / $known,
            '/' => $resultValue == 0.0 ? throw new InvalidArgumentException('Деление на ноль невозможно.') : $known / $resultValue,
            default => throw new InvalidArgumentException('Неизвестный оператор.')
        };
    } else {
        throw new InvalidArgumentException('Не удалось определить положение X.');
    }

    return [
        'equation' => $equation,
        'normalized' => $normalized,
        'operator' => $operator,
        'side' => $equationSide,
        'position' => $position,
        'x' => $x,
    ];
}

try {
    $solution = solveEquation($equation);
    $error = null;
} catch (Throwable $exception) {
    $solution = null;
    $error = $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Домашняя работа: Solve the equation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="logo">МосПолитех</div>
    <h1>Домашняя работа: Solve the equation</h1>
</header>

<main>
    <section class="card">
        <h2>Вариант 3</h2>
        <p><strong>Исходное уравнение:</strong> <?= htmlspecialchars($equation, ENT_QUOTES, 'UTF-8') ?></p>

        <?php if ($error !== null): ?>
            <p class="error"><strong>Ошибка:</strong> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php else: ?>
            <p><strong>Определённый оператор:</strong> <?= htmlspecialchars($solution['operator'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Сторона с неизвестной:</strong> <?= htmlspecialchars($solution['side'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Положение неизвестной:</strong> <?= htmlspecialchars($solution['position'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Решение:</strong> X = <?= rtrim(rtrim(number_format((float) $solution['x'], 2, '.', ''), '0'), '.') ?></p>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Блок-схема</h2>
        <img src="diagram.png" alt="Диаграмма решения уравнения">
    </section>
 </main>

<footer>
    <p>задание для самостоятельной работы</p>
</footer>
</body>
</html>
