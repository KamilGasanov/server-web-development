<?php
declare(strict_types=1);

require_once __DIR__ . '/../lab-3-2-trigonometry/trigonometry.php';

function addNumbers(float $left, float $right): float
{
    return $left + $right;
}

function subtractNumbers(float $left, float $right): float
{
    return $left - $right;
}

function multiplyNumbers(float $left, float $right): float
{
    return $left * $right;
}

function divideNumbers(float $left, float $right): float
{
    if (abs($right) < 1e-12) {
        throw new InvalidArgumentException('Деление на ноль невозможно.');
    }

    return $left / $right;
}

function powerNumbers(float $base, float $exponent): float
{
    $result = $base ** $exponent;

    if (!is_finite($result)) {
        throw new InvalidArgumentException('Некорректное возведение в степень.');
    }

    return $result;
}

function sqrtNumber(float $value): float
{
    if ($value < 0) {
        throw new InvalidArgumentException('Нельзя извлечь корень из отрицательного числа.');
    }

    return sqrt($value);
}

function naturalLogNumber(float $value): float
{
    if ($value <= 0) {
        throw new InvalidArgumentException('Ln определён только для положительных чисел.');
    }

    return log($value);
}

function decimalLogNumber(float $value): float
{
    if ($value <= 0) {
        throw new InvalidArgumentException('Log определён только для положительных чисел.');
    }

    return log10($value);
}

function factorialNumber(float $value): float
{
    if ($value < 0) {
        throw new InvalidArgumentException('Факториал определён только для неотрицательных чисел.');
    }

    if (floor($value) !== $value) {
        throw new InvalidArgumentException('Факториал можно вычислить только для целого числа.');
    }

    return factorialRecursive((int) $value);
}

function factorialRecursive(int $value): float
{
    if ($value <= 1) {
        return 1.0;
    }

    return multiplyNumbers((float) $value, factorialRecursive($value - 1));
}

function normalizeExpression(string $expression): string
{
    $expression = mb_strtolower(trim($expression), 'UTF-8');
    $expression = str_replace(' ', '', $expression);
    $expression = str_replace(',', '.', $expression);
    $expression = str_replace('√', 'sqrt', $expression);

    return $expression;
}

function getTaskExpressionPath(): string
{
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Task' . DIRECTORY_SEPARATOR . 'expression.txt';
}

function readTaskExpression(string $path): string
{
    if (!is_file($path)) {
        throw new RuntimeException('Файл Task/expression.txt не найден.');
    }

    $content = file_get_contents($path);

    if ($content === false) {
        throw new RuntimeException('Не удалось прочитать файл Task/expression.txt.');
    }

    $expression = trim($content);

    if ($expression === '') {
        throw new RuntimeException('Файл Task/expression.txt пуст.');
    }

    return $expression;
}

function tokenizeExpression(string $expression): array
{
    $tokens = [];
    $length = strlen($expression);
    $index = 0;

    while ($index < $length) {
        $character = $expression[$index];

        if (ctype_digit($character) || $character === '.') {
            $number = '';
            $dotCount = 0;

            while ($index < $length) {
                $current = $expression[$index];

                if ($current === '.') {
                    $dotCount++;

                    if ($dotCount > 1) {
                        throw new InvalidArgumentException('Некорректный формат числа.');
                    }
                } elseif (!ctype_digit($current)) {
                    break;
                }

                $number .= $current;
                $index++;
            }

            if ($number === '.') {
                throw new InvalidArgumentException('Точка должна быть частью числа.');
            }

            $tokens[] = $number;
            continue;
        }

        if (ctype_alpha($character)) {
            $identifier = '';

            while ($index < $length && ctype_alpha($expression[$index])) {
                $identifier .= $expression[$index];
                $index++;
            }

            if (!in_array($identifier, ['sqrt', 'ln', 'log', 'sin', 'cos', 'tan', 'pi', 'e'], true)) {
                throw new InvalidArgumentException('Обнаружена неизвестная функция или константа.');
            }

            $tokens[] = $identifier;
            continue;
        }

        if (in_array($character, ['+', '-', '*', '/', '^', '!', '(', ')'], true)) {
            $tokens[] = $character;
            $index++;
            continue;
        }

        throw new InvalidArgumentException('Выражение содержит недопустимые символы.');
    }

    return $tokens;
}

function parseExpression(array $tokens, int &$position): float
{
    $value = parseTerm($tokens, $position);

    while ($position < count($tokens)) {
        $token = $tokens[$position];

        if ($token !== '+' && $token !== '-') {
            break;
        }

        $position++;
        $rightValue = parseTerm($tokens, $position);
        $value = $token === '+'
            ? addNumbers($value, $rightValue)
            : subtractNumbers($value, $rightValue);
    }

    return $value;
}

function parseTerm(array $tokens, int &$position): float
{
    $value = parsePower($tokens, $position);

    while ($position < count($tokens)) {
        $token = $tokens[$position];

        if ($token !== '*' && $token !== '/') {
            break;
        }

        $position++;
        $rightValue = parsePower($tokens, $position);
        $value = $token === '*'
            ? multiplyNumbers($value, $rightValue)
            : divideNumbers($value, $rightValue);
    }

    return $value;
}

function parsePower(array $tokens, int &$position): float
{
    $value = parseUnary($tokens, $position);

    if (($tokens[$position] ?? null) === '^') {
        $position++;
        $exponent = parsePower($tokens, $position);
        $value = powerNumbers($value, $exponent);
    }

    return $value;
}

function parseUnary(array $tokens, int &$position): float
{
    if ($position >= count($tokens)) {
        throw new InvalidArgumentException('Выражение обрывается неожиданно.');
    }

    $token = $tokens[$position];

    if ($token === '+') {
        $position++;
        return parseUnary($tokens, $position);
    }

    if ($token === '-') {
        $position++;
        return subtractNumbers(0.0, parseUnary($tokens, $position));
    }

    return parsePostfix($tokens, $position);
}

function parsePostfix(array $tokens, int &$position): float
{
    $value = parsePrimary($tokens, $position);

    while (($tokens[$position] ?? null) === '!') {
        $position++;
        $value = factorialNumber($value);
    }

    return $value;
}

function parsePrimary(array $tokens, int &$position): float
{
    if ($position >= count($tokens)) {
        throw new InvalidArgumentException('Выражение обрывается неожиданно.');
    }

    $token = $tokens[$position];

    if ($token === '(') {
        $position++;
        $value = parseExpression($tokens, $position);

        if (!isset($tokens[$position]) || $tokens[$position] !== ')') {
            throw new InvalidArgumentException('Отсутствует закрывающая скобка.');
        }

        $position++;
        return $value;
    }

    if ($token === 'pi') {
        $position++;
        return pi();
    }

    if ($token === 'e') {
        $position++;
        return exp(1);
    }

    if (in_array($token, ['sqrt', 'ln', 'log', 'sin', 'cos', 'tan'], true)) {
        return parseFunctionCall($tokens, $position);
    }

    if (is_numeric($token)) {
        $position++;
        return (float) $token;
    }

    throw new InvalidArgumentException('Обнаружен неожиданный элемент в выражении.');
}

function parseFunctionCall(array $tokens, int &$position): float
{
    $functionName = $tokens[$position];
    $position++;

    if (($tokens[$position] ?? null) !== '(') {
        throw new InvalidArgumentException('После функции должна идти открывающая скобка.');
    }

    $position++;
    $value = parseExpression($tokens, $position);

    if (($tokens[$position] ?? null) !== ')') {
        throw new InvalidArgumentException('Функция должна заканчиваться закрывающей скобкой.');
    }

    $position++;

    if (in_array($functionName, ['sin', 'cos', 'tan'], true)) {
        return evaluateTrigonometricFunction($functionName, $value);
    }

    return match ($functionName) {
        'sqrt' => sqrtNumber($value),
        'ln' => naturalLogNumber($value),
        'log' => decimalLogNumber($value),
        default => throw new InvalidArgumentException('Неизвестная функция.'),
    };
}

function evaluateExpression(string $expression): float
{
    $normalized = normalizeExpression($expression);

    if ($normalized === '') {
        throw new InvalidArgumentException('Введите выражение перед вычислением.');
    }

    $tokens = tokenizeExpression($normalized);
    $position = 0;
    $result = parseExpression($tokens, $position);

    if ($position !== count($tokens)) {
        throw new InvalidArgumentException('Выражение записано неверно.');
    }

    return $result;
}

function formatNumber(float $value): string
{
    if (!is_finite($value)) {
        throw new InvalidArgumentException('Результат вычисления некорректен.');
    }

    if (abs($value) < 1e-12) {
        $value = 0.0;
    }

    return rtrim(rtrim(number_format($value, 10, '.', ''), '0'), '.');
}

function buildRedirectUrl(array $query): string
{
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/index.php', '?');
    $queryString = http_build_query($query);

    return $queryString === '' ? (string) $path : $path . '?' . $queryString;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedExpression = (string) ($_POST['expression'] ?? '');

    try {
        $result = evaluateExpression($submittedExpression);

        header('Location: ' . buildRedirectUrl([
            'expression' => $submittedExpression,
            'result' => formatNumber($result),
        ]));
    } catch (Throwable $exception) {
        header('Location: ' . buildRedirectUrl([
            'expression' => $submittedExpression,
            'error' => $exception->getMessage(),
        ]));
    }

    exit;
}

$sourceExpression = isset($_GET['expression']) ? (string) $_GET['expression'] : '';
$resultValue = isset($_GET['result']) ? (string) $_GET['result'] : null;
$errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
$displayValue = $resultValue ?? $sourceExpression;
$taskExpression = '';
$taskResultValue = null;
$taskErrorMessage = null;

try {
    $taskExpression = readTaskExpression(getTaskExpressionPath());
    $taskResultValue = formatNumber(evaluateExpression($taskExpression));
} catch (Throwable $exception) {
    $taskErrorMessage = $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Калькулятор</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="page-header">
    <div class="logo">МосПолитех</div>
    <h1>Домашняя работа: Калькулятор</h1>
</header>

<main class="page-content">
    <section class="calculator-card">
        <div class="card-copy">
            <p class="eyebrow">Тема 2</p>
            <h2>Калькулятор</h2>
            <p class="description">
                Доступны обычные арифметические операции, скобки, степень, корень, логарифмы, факториал
                и математические константы. Выражение собирается через JavaScript, отправляется POST-запросом,
                а результат возвращается через GET-параметры.
            </p>
        </div>

        <form class="calculator-form" method="post" id="calculator-form">
            <label class="display-label" for="display">Поле отображения</label>
            <input
                class="display"
                id="display"
                type="text"
                value="<?= htmlspecialchars($displayValue, ENT_QUOTES, 'UTF-8') ?>"
                readonly
            >
            <input
                id="expression"
                name="expression"
                type="hidden"
                value="<?= htmlspecialchars($displayValue, ENT_QUOTES, 'UTF-8') ?>"
            >

            <div class="status-panel">
                <p><strong>Отправленное выражение:</strong> <?= htmlspecialchars($sourceExpression !== '' ? $sourceExpression : 'не задано', ENT_QUOTES, 'UTF-8') ?></p>
                <?php if ($resultValue !== null): ?>
                    <p class="status-ok"><strong>Результат:</strong> <?= htmlspecialchars($resultValue, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($errorMessage !== null): ?>
                    <p class="status-error"><strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <div class="button-grid">
                <button type="button" class="control-button control-clear" data-action="clear">C</button>
                <button type="button" class="control-button" data-action="backspace">&larr;</button>
                <button type="button" class="operator-button" data-value="(">(</button>
                <button type="button" class="operator-button" data-value=")">)</button>
                <button type="button" class="operator-button" data-value="/">/</button>

                <button type="button" data-value="7">7</button>
                <button type="button" data-value="8">8</button>
                <button type="button" data-value="9">9</button>
                <button type="button" class="operator-button" data-value="*">*</button>
                <button type="button" class="operator-button" data-value="^">^</button>

                <button type="button" data-value="4">4</button>
                <button type="button" data-value="5">5</button>
                <button type="button" data-value="6">6</button>
                <button type="button" class="operator-button" data-value="-">-</button>
                <button type="button" class="operator-button" data-value="!">!</button>

                <button type="button" data-value="1">1</button>
                <button type="button" data-value="2">2</button>
                <button type="button" data-value="3">3</button>
                <button type="button" class="operator-button" data-value="+">+</button>
                <button type="button" class="function-button" data-value="sqrt(">&radic;x</button>

                <button type="button" data-value="0">0</button>
                <button type="button" data-value=".">.</button>
                <button type="button" class="function-button" data-value="pi">pi</button>
                <button type="button" class="function-button" data-value="e">e</button>
                <button type="button" class="function-button" data-value="ln(">ln</button>

                <button type="button" class="function-button" data-value="sin(">sin</button>
                <button type="button" class="function-button" data-value="cos(">cos</button>
                <button type="button" class="function-button" data-value="tan(">tan</button>

                <button type="button" class="function-button wide-button" data-value="log(">log</button>
                <button type="submit" class="equals-button wider-button">=</button>
            </div>
        </form>
    </section>

    <section class="info-card">
        <h2>Что умеет калькулятор</h2>
        <ul>
            <li>Проверяет корректность выражения и не допускает недопустимые символы.</li>
            <li>Поддерживает сложение, вычитание, умножение, деление, степень, факториал, корень, ln, log, sin, cos, tan, pi и e.</li>
            <li>Понимает отрицательные числа, отрицательные скобки и дробные значения.</li>
            <li>Рекурсивно вычисляет выражение с отдельными пользовательскими функциями для операций.</li>
        </ul>

        <p class="shortcut-note">
            Клавиатура: используйте цифры и знаки как обычно, а также <strong>p</strong> для <code>pi</code>,
            <strong>e</strong> для <code>e</code>, <strong>r</strong> для <code>sqrt(</code>,
            <strong>n</strong> для <code>ln(</code>, <strong>g</strong> для <code>log(</code>,
            <strong>^</strong> для степени и <strong>!</strong> для факториала.
        </p>

        <p class="shortcut-note">
            Тригонометрия: <strong>s</strong> для <code>sin(</code>, <strong>c</strong> для <code>cos(</code>,
            <strong>t</strong> для <code>tan(</code>. Углы считаются в градусах.
        </p>

        <div class="task-panel">
            <h3>Тригонометрия</h3>
            <?php if ($taskErrorMessage !== null): ?>
                <p class="status-error"><strong>Ошибка:</strong> <?= htmlspecialchars($taskErrorMessage, ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <p><strong>Выражение:</strong> <?= htmlspecialchars($taskExpression, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="status-ok"><strong>Результат:</strong> <?= htmlspecialchars((string) $taskResultValue, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
    </section>
</main>

<script src="script.js"></script>
</body>
</html>
