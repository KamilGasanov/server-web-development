<?php
declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function dumpToString(mixed $value): string
{
    ob_start();
    var_dump($value);

    return trim((string) ob_get_clean());
}

abstract class HumanAbstract
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function getGreetings(): string;

    abstract public function getMyNameIs(): string;

    public function introduceYourself(): string
    {
        return $this->getGreetings() . '! ' . $this->getMyNameIs() . ' ' . $this->getName() . '.';
    }
}

class RussianHuman extends HumanAbstract
{
    public function getGreetings(): string
    {
        return 'Привет';
    }

    public function getMyNameIs(): string
    {
        return 'Меня зовут';
    }
}

class EnglishHuman extends HumanAbstract
{
    public function getGreetings(): string
    {
        return 'Hello';
    }

    public function getMyNameIs(): string
    {
        return 'My name is';
    }
}

class Lesson
{
    protected string $title;
    protected string $text;
    protected string $homework;

    public function __construct(string $title, string $text, string $homework)
    {
        $this->title = $title;
        $this->text = $text;
        $this->homework = $homework;
    }
}

class PaidLesson extends Lesson
{
    private float $price;

    public function __construct(string $title, string $text, string $homework, float $price)
    {
        parent::__construct($title, $text, $homework);
        $this->price = $price;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}

interface CalculateSquare
{
    public function calculateSquare(): float;
}

class Rectangle implements CalculateSquare
{
    public function __construct(
        private float $width,
        private float $height
    ) {
    }

    public function calculateSquare(): float
    {
        return $this->width * $this->height;
    }
}

class Circle implements CalculateSquare
{
    public function __construct(private float $radius)
    {
    }

    public function calculateSquare(): float
    {
        return pi() * $this->radius * $this->radius;
    }
}

class Cat
{
    private string $name;
    private string $color;

    public function __construct(string $name, string $color)
    {
        $this->name = $name;
        $this->color = $color;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function sayHello(): string
    {
        return 'Мяу! Меня зовут ' . $this->getName() . '. Мой цвет: ' . $this->getColor() . '.';
    }
}

function describeSquareAbility(object $object): string
{
    $className = get_class($object);

    if (!$object instanceof CalculateSquare) {
        return 'Объект класса ' . $className . ' не реализует интерфейс CalculateSquare.';
    }

    return 'Объект класса ' . $className . ' имеет площадь ' . number_format($object->calculateSquare(), 2, '.', '') . '.';
}

$russianHuman = new RussianHuman('Иван');
$englishHuman = new EnglishHuman('John');

$paidLesson = new PaidLesson(
    'Урок о наследовании в PHP',
    'Лол, кек, чебурек',
    'Ложитесь спать, утро вечера мудренее',
    99.90
);

$objectsForSquare = [
    new Rectangle(6, 4),
    new Circle(5),
    new Lesson('Обычный урок', 'Текст урока', 'Сделать упражнение'),
];

$cat = new Cat('Мурка', 'рыжий');

$squareDescriptions = [];
foreach ($objectsForSquare as $object) {
    $squareDescriptions[] = describeSquareAbility($object);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 1.1 OOP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="page">
    <h1>Lab 1.1 OOP</h1>

    <section class="card">
        <h2>1. Абстрактные классы</h2>
        <p><?= h($russianHuman->introduceYourself()) ?></p>
        <p><?= h($englishHuman->introduceYourself()) ?></p>
    </section>

    <section class="card">
        <h2>2. Наследование</h2>
        <pre><?= h(dumpToString($paidLesson)) ?></pre>
    </section>

    <section class="card">
        <h2>3. Интерфейсы</h2>
        <ul>
            <?php foreach ($squareDescriptions as $description): ?>
                <li><?= h($description) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="card">
        <h2>4. Инкапсуляция</h2>
        <p><?= h($cat->sayHello()) ?></p>
        <p><?= h('Цвет через геттер: ' . $cat->getColor()) ?></p>
    </section>
</main>
</body>
</html>
