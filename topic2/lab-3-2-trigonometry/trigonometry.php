<?php
declare(strict_types=1);

function evaluateTrigonometricFunction(string $functionName, float $value): float
{
    if (!in_array($functionName, ['sin', 'cos', 'tan'], true)) {
        throw new InvalidArgumentException('Неизвестная тригонометрическая функция.');
    }

    $result = $functionName(deg2rad($value));

    if (!is_finite($result)) {
        throw new InvalidArgumentException('Некорректный результат тригонометрической функции.');
    }

    return $result;
}
