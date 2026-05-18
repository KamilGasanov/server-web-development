<?php
declare(strict_types=1);

final class MainController
{
    public function index(): void
    {
        $this->render('main');
    }

    public function aboutMe(): void
    {
        $this->render('message', [
            'pageTitle' => 'Обо мне',
            'headline' => 'Обо мне',
            'message' => 'Это страница о владельце блога в учебном MVC-приложении.',
        ]);
    }

    public function sayHello(string $name): void
    {
        $this->render('message', [
            'pageTitle' => 'Страница приветствия',
            'headline' => 'Приветствие',
            'message' => 'Привет, ' . $name,
        ]);
    }

    public function sayBye(string $name): void
    {
        $this->render('message', [
            'pageTitle' => 'Прощание',
            'headline' => 'Прощание',
            'message' => 'Пока, ' . $name,
        ]);
    }

    public function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/views/' . $view . '.php';
    }
}
