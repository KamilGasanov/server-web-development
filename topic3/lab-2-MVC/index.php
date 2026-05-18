<?php
declare(strict_types=1);

require_once __DIR__ . '/MainController.php';

$controller = new MainController();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = str_replace('\\', '/', dirname($scriptName));

if ($basePath !== '/' && $basePath !== '.') {
    if (str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath)) ?: '/';
    }
}

$path = '/' . ltrim($path, '/');

if ($path === '/' || $path === '/index.php') {
    $controller->index();
    return;
}

if ($path === '/about-me') {
    $controller->aboutMe();
    return;
}

if (preg_match('#^/hello/([^/]+)$#', $path, $matches) === 1) {
    $controller->sayHello(urldecode($matches[1]));
    return;
}

if (preg_match('#^/bye/([^/]+)$#', $path, $matches) === 1) {
    $controller->sayBye(urldecode($matches[1]));
    return;
}

http_response_code(404);
$controller->render('message', [
    'pageTitle' => '404',
    'headline' => 'Страница не найдена',
    'message' => 'Проверьте адрес или вернитесь на главную страницу.',
]);
