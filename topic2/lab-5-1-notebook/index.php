<?php
declare(strict_types=1);

define('NOTEBOOK_BOOTSTRAPPED', true);

const NOTEBOOK_PAGE_SIZE = 10;
const NOTEBOOK_ACTIONS = ['view', 'add', 'edit', 'delete'];
const NOTEBOOK_SORTS = ['created', 'surname', 'birth_date'];
const NOTEBOOK_GENDERS = ['Мужской', 'Женский'];

function h(null|string|int|float $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function buildNotebookUrl(array $query = []): string
{
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/index.php', '?');
    $path = $path === false ? '/index.php' : $path;
    $queryString = http_build_query($query);

    return $queryString === '' ? $path : $path . '?' . $queryString;
}

function ensureNotebookDataDirectory(): string
{
    $directory = __DIR__ . DIRECTORY_SEPARATOR . 'data';

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    return $directory;
}

function getNotebookPdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $databasePath = ensureNotebookDataDirectory() . DIRECTORY_SEPARATOR . 'notebook.sqlite';
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    initializeNotebookDatabase($pdo);

    return $pdo;
}

function initializeNotebookDatabase(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS contacts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            surname TEXT NOT NULL,
            name TEXT NOT NULL,
            patronymic TEXT NOT NULL DEFAULT "",
            gender TEXT NOT NULL DEFAULT "Мужской",
            birth_date TEXT NOT NULL DEFAULT "",
            phone TEXT NOT NULL DEFAULT "",
            address TEXT NOT NULL DEFAULT "",
            email TEXT NOT NULL DEFAULT "",
            comment TEXT NOT NULL DEFAULT "",
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
}

function getNotebookAction(): string
{
    $action = (string) ($_GET['action'] ?? 'view');

    return in_array($action, NOTEBOOK_ACTIONS, true) ? $action : 'view';
}

function getNotebookSort(): string
{
    $sort = (string) ($_GET['sort'] ?? 'created');

    return in_array($sort, NOTEBOOK_SORTS, true) ? $sort : 'created';
}

function getNotebookPage(): int
{
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

    return $page !== false && $page !== null && $page > 0 ? $page : 1;
}

function getNotebookSelectedId(string $key = 'id'): ?int
{
    $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);

    return $value !== false && $value !== null && $value > 0 ? $value : null;
}

function getDefaultContactValues(): array
{
    return [
        'surname' => '',
        'name' => '',
        'patronymic' => '',
        'gender' => NOTEBOOK_GENDERS[0],
        'birth_date' => '',
        'phone' => '',
        'address' => '',
        'email' => '',
        'comment' => '',
    ];
}

function normalizeContactInput(array $source): array
{
    $contact = getDefaultContactValues();

    foreach ($contact as $field => $defaultValue) {
        $contact[$field] = trim((string) ($source[$field] ?? $defaultValue));
    }

    if (!in_array($contact['gender'], NOTEBOOK_GENDERS, true)) {
        $contact['gender'] = NOTEBOOK_GENDERS[0];
    }

    return $contact;
}

function isValidBirthDate(string $value): bool
{
    if ($value === '') {
        return true;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);

    return $date instanceof DateTime && $date->format('Y-m-d') === $value;
}

function validateContact(array $contact): array
{
    $errors = [];

    if ($contact['surname'] === '') {
        $errors[] = 'Введите фамилию.';
    }

    if ($contact['name'] === '') {
        $errors[] = 'Введите имя.';
    }

    if (!isValidBirthDate($contact['birth_date'])) {
        $errors[] = 'Дата рождения указана неверно.';
    }

    if ($contact['email'] !== '' && filter_var($contact['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'Е-mail указан неверно.';
    }

    return $errors;
}

function getNotebookOrderBy(string $sort): string
{
    return match ($sort) {
        'surname' => 'surname COLLATE NOCASE ASC, name COLLATE NOCASE ASC, patronymic COLLATE NOCASE ASC, id ASC',
        'birth_date' => 'birth_date ASC, surname COLLATE NOCASE ASC, name COLLATE NOCASE ASC, id ASC',
        default => 'id ASC',
    };
}

function countNotebookContacts(): int
{
    $statement = getNotebookPdo()->query('SELECT COUNT(*) FROM contacts');

    return (int) $statement->fetchColumn();
}

function fetchNotebookContacts(string $sort, int $page, int $pageSize = NOTEBOOK_PAGE_SIZE): array
{
    $page = max(1, $page);
    $offset = ($page - 1) * $pageSize;
    $orderBy = getNotebookOrderBy($sort);

    $statement = getNotebookPdo()->prepare(
        "SELECT * FROM contacts ORDER BY {$orderBy} LIMIT :limit OFFSET :offset"
    );
    $statement->bindValue(':limit', $pageSize, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}

function fetchNotebookContactsForSelection(): array
{
    $statement = getNotebookPdo()->query(
        'SELECT * FROM contacts ORDER BY surname COLLATE NOCASE ASC, name COLLATE NOCASE ASC, patronymic COLLATE NOCASE ASC, id ASC'
    );

    return $statement->fetchAll();
}

function fetchNotebookContactById(int $id): ?array
{
    $statement = getNotebookPdo()->prepare('SELECT * FROM contacts WHERE id = :id');
    $statement->execute([':id' => $id]);
    $contact = $statement->fetch();

    return $contact === false ? null : $contact;
}

function insertNotebookContact(array $contact): bool
{
    $statement = getNotebookPdo()->prepare(
        'INSERT INTO contacts (surname, name, patronymic, gender, birth_date, phone, address, email, comment)
         VALUES (:surname, :name, :patronymic, :gender, :birth_date, :phone, :address, :email, :comment)'
    );

    return $statement->execute([
        ':surname' => $contact['surname'],
        ':name' => $contact['name'],
        ':patronymic' => $contact['patronymic'],
        ':gender' => $contact['gender'],
        ':birth_date' => $contact['birth_date'],
        ':phone' => $contact['phone'],
        ':address' => $contact['address'],
        ':email' => $contact['email'],
        ':comment' => $contact['comment'],
    ]);
}

function updateNotebookContact(int $id, array $contact): bool
{
    $statement = getNotebookPdo()->prepare(
        'UPDATE contacts
         SET surname = :surname,
             name = :name,
             patronymic = :patronymic,
             gender = :gender,
             birth_date = :birth_date,
             phone = :phone,
             address = :address,
             email = :email,
             comment = :comment
         WHERE id = :id'
    );

    return $statement->execute([
        ':id' => $id,
        ':surname' => $contact['surname'],
        ':name' => $contact['name'],
        ':patronymic' => $contact['patronymic'],
        ':gender' => $contact['gender'],
        ':birth_date' => $contact['birth_date'],
        ':phone' => $contact['phone'],
        ':address' => $contact['address'],
        ':email' => $contact['email'],
        ':comment' => $contact['comment'],
    ]);
}

function deleteNotebookContact(int $id): ?string
{
    $contact = fetchNotebookContactById($id);

    if ($contact === null) {
        return null;
    }

    $statement = getNotebookPdo()->prepare('DELETE FROM contacts WHERE id = :id');
    $statement->execute([':id' => $id]);

    return (string) $contact['surname'];
}

require_once __DIR__ . '/menu.php';
require_once __DIR__ . '/viewer.php';
require_once __DIR__ . '/add.php';
require_once __DIR__ . '/edit.php';
require_once __DIR__ . '/delete.php';

$action = getNotebookAction();
$sort = getNotebookSort();
$page = getNotebookPage();

$content = match ($action) {
    'add' => renderAddPage(),
    'edit' => renderEditPage(),
    'delete' => renderDeletePage(),
    default => renderViewer($sort, $page),
};
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notebook</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
    <?= renderMenu() ?>

    <main class="page-content">
        <?= $content ?>
    </main>
</div>
</body>
</html>
