<?php
declare(strict_types=1);

const DB_DIR = __DIR__ . '/data';
const DB_PATH = DB_DIR . '/hashtag_sorter.sqlite';

function h(null|string|int|float $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function ensureDataDirectory(): void
{
    if (!is_dir(DB_DIR)) {
        mkdir(DB_DIR, 0777, true);
    }
}

function getPdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    ensureDataDirectory();

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    initializeDatabase($pdo);

    return $pdo;
}

function initializeDatabase(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS channels (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT NOT NULL DEFAULT \'\',
            created_at TEXT NOT NULL,
            is_trusted INTEGER NOT NULL DEFAULT 0
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS hashtags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            created_at TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS fields (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT NOT NULL DEFAULT \'\',
            created_at TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS hashtag_field (
            hashtag_id INTEGER NOT NULL,
            field_id INTEGER NOT NULL,
            PRIMARY KEY (hashtag_id, field_id),
            FOREIGN KEY (hashtag_id) REFERENCES hashtags(id) ON DELETE CASCADE,
            FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS sms (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            hashtag_id INTEGER NOT NULL,
            user_id INTEGER,
            channel_id INTEGER,
            description TEXT NOT NULL DEFAULT \'\',
            created_at TEXT NOT NULL,
            is_hidden INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (hashtag_id) REFERENCES hashtags(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (channel_id) REFERENCES channels(id) ON DELETE SET NULL
        )'
    );

    seedDatabase($pdo);
}

function seedDatabase(PDO $pdo): void
{
    $hashtagsCount = (int) $pdo->query('SELECT COUNT(*) FROM hashtags')->fetchColumn();

    if ($hashtagsCount > 0) {
        return;
    }

    $now = date('Y-m-d H:i:s');

    $pdo->beginTransaction();

    $insertUser = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
    foreach ([
        ['name' => 'Анна', 'email' => 'anna@example.com', 'password' => password_hash('anna123', PASSWORD_DEFAULT)],
        ['name' => 'Павел', 'email' => 'pavel@example.com', 'password' => password_hash('pavel123', PASSWORD_DEFAULT)],
    ] as $user) {
        $insertUser->execute($user);
    }

    $insertChannel = $pdo->prepare(
        'INSERT INTO channels (name, description, created_at, is_trusted) VALUES (:name, :description, :created_at, :is_trusted)'
    );
    foreach ([
        ['name' => 'FoodTalk', 'description' => 'Канал с рецептами и обзорами еды.', 'created_at' => $now, 'is_trusted' => 1],
        ['name' => 'DevPulse', 'description' => 'Новости разработки и технологий.', 'created_at' => $now, 'is_trusted' => 1],
        ['name' => 'TravelBox', 'description' => 'Путешествия и маршруты.', 'created_at' => $now, 'is_trusted' => 0],
    ] as $channel) {
        $insertChannel->execute($channel);
    }

    $insertField = $pdo->prepare(
        'INSERT INTO fields (name, description, created_at) VALUES (:name, :description, :created_at)'
    );
    foreach ([
        ['name' => 'Кулинария', 'description' => 'Рецепты, десерты, напитки и всё, что связано с приготовлением еды.', 'created_at' => $now],
        ['name' => 'Программирование', 'description' => 'Языки программирования, фреймворки, архитектура и базы данных.', 'created_at' => $now],
        ['name' => 'Маркетинг', 'description' => 'Продвижение, SEO, аналитика и digital-стратегии.', 'created_at' => $now],
    ] as $field) {
        $insertField->execute($field);
    }

    $insertHashtag = $pdo->prepare('INSERT INTO hashtags (name, created_at) VALUES (:name, :created_at)');
    foreach (['cake', 'php', 'seo', 'travel', 'design', 'fitness'] as $name) {
        $insertHashtag->execute(['name' => $name, 'created_at' => $now]);
    }

    $fieldIds = fetchIdMap($pdo, 'fields');
    $hashtagIds = fetchIdMap($pdo, 'hashtags');

    $insertLink = $pdo->prepare('INSERT INTO hashtag_field (hashtag_id, field_id) VALUES (:hashtag_id, :field_id)');
    foreach ([
        ['hashtag_id' => $hashtagIds['php'], 'field_id' => $fieldIds['Программирование']],
        ['hashtag_id' => $hashtagIds['seo'], 'field_id' => $fieldIds['Маркетинг']],
    ] as $link) {
        $insertLink->execute($link);
    }

    $insertSms = $pdo->prepare(
        'INSERT INTO sms (hashtag_id, user_id, channel_id, description, created_at, is_hidden)
         VALUES (:hashtag_id, :user_id, :channel_id, :description, :created_at, :is_hidden)'
    );
    foreach ([
        [
            'hashtag_id' => $hashtagIds['cake'],
            'user_id' => 1,
            'channel_id' => 1,
            'description' => 'Новый рецепт чизкейка с вишней #cake',
            'created_at' => $now,
            'is_hidden' => 0,
        ],
        [
            'hashtag_id' => $hashtagIds['php'],
            'user_id' => 2,
            'channel_id' => 2,
            'description' => 'Разбор типизации и PDO в PHP #php',
            'created_at' => $now,
            'is_hidden' => 0,
        ],
        [
            'hashtag_id' => $hashtagIds['travel'],
            'user_id' => 1,
            'channel_id' => 3,
            'description' => 'Список мест для поездки на майские #travel',
            'created_at' => $now,
            'is_hidden' => 1,
        ],
        [
            'hashtag_id' => $hashtagIds['design'],
            'user_id' => 2,
            'channel_id' => 2,
            'description' => 'Подборка приёмов для UI-карточек #design',
            'created_at' => $now,
            'is_hidden' => 0,
        ],
    ] as $sms) {
        $insertSms->execute($sms);
    }

    $pdo->commit();
}

function fetchIdMap(PDO $pdo, string $table): array
{
    $statement = $pdo->query("SELECT id, name FROM {$table}");
    $map = [];

    foreach ($statement as $row) {
        $map[$row['name']] = (int) $row['id'];
    }

    return $map;
}

function fetchFields(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT f.id, f.name, f.description, COUNT(hf.hashtag_id) AS linked_count
         FROM fields f
         LEFT JOIN hashtag_field hf ON hf.field_id = f.id
         GROUP BY f.id, f.name, f.description
         ORDER BY f.name ASC'
    );

    return $statement->fetchAll();
}

function fetchUnlinkedHashtags(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT
            h.id,
            h.name,
            h.created_at,
            COUNT(s.id) AS message_count,
            SUM(CASE WHEN s.is_hidden = 1 THEN 1 ELSE 0 END) AS hidden_count
         FROM hashtags h
         LEFT JOIN hashtag_field hf ON hf.hashtag_id = h.id
         LEFT JOIN sms s ON s.hashtag_id = h.id
         WHERE hf.hashtag_id IS NULL
         GROUP BY h.id, h.name, h.created_at
         ORDER BY h.name ASC'
    );

    return $statement->fetchAll();
}

function fetchLinkedHashtags(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT
            h.id AS hashtag_id,
            f.id AS field_id,
            h.name AS hashtag_name,
            f.name AS field_name
         FROM hashtag_field hf
         INNER JOIN hashtags h ON h.id = hf.hashtag_id
         INNER JOIN fields f ON f.id = hf.field_id
         ORDER BY f.name ASC, h.name ASC'
    );

    return $statement->fetchAll();
}

function bindHashtagToField(PDO $pdo, int $hashtagId, int $fieldId): void
{
    $statement = $pdo->prepare(
        'INSERT OR IGNORE INTO hashtag_field (hashtag_id, field_id) VALUES (:hashtag_id, :field_id)'
    );
    $statement->execute([
        'hashtag_id' => $hashtagId,
        'field_id' => $fieldId,
    ]);
}

function unbindHashtagFromField(PDO $pdo, int $hashtagId, int $fieldId): void
{
    $statement = $pdo->prepare(
        'DELETE FROM hashtag_field WHERE hashtag_id = :hashtag_id AND field_id = :field_id'
    );
    $statement->execute([
        'hashtag_id' => $hashtagId,
        'field_id' => $fieldId,
    ]);
}

function createField(PDO $pdo, string $name, string $description): int
{
    $statement = $pdo->prepare(
        'INSERT INTO fields (name, description, created_at) VALUES (:name, :description, :created_at)'
    );
    $statement->execute([
        'name' => $name,
        'description' => $description,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    return (int) $pdo->lastInsertId();
}

function buildUrl(array $query = []): string
{
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/index.php', '?');
    $queryString = http_build_query($query);

    return $queryString === '' ? (string) $path : $path . '?' . $queryString;
}

$pdo = getPdo();
$flash = ['type' => '', 'text' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $hashtagId = (int) ($_POST['hashtag_id'] ?? 0);

    try {
        if ($action === 'assign-existing') {
            $fieldId = (int) ($_POST['field_id'] ?? 0);

            if ($hashtagId <= 0 || $fieldId <= 0) {
                throw new InvalidArgumentException('Выберите хэштег и область знаний.');
            }

            bindHashtagToField($pdo, $hashtagId, $fieldId);
            header('Location: ' . buildUrl([
                'type' => 'success',
                'message' => 'Хэштег успешно привязан к выбранной области знаний.',
            ]));
            exit;
        }

        if ($action === 'create-and-assign') {
            $fieldName = trim((string) ($_POST['field_name'] ?? ''));
            $fieldDescription = trim((string) ($_POST['field_description'] ?? ''));

            if ($hashtagId <= 0) {
                throw new InvalidArgumentException('Не найден хэштег для привязки.');
            }

            if ($fieldName === '') {
                throw new InvalidArgumentException('Введите название новой области знаний.');
            }

            $statement = $pdo->prepare('SELECT id FROM fields WHERE lower(name) = lower(:name) LIMIT 1');
            $statement->execute(['name' => $fieldName]);
            $existingFieldId = $statement->fetchColumn();

            $fieldId = $existingFieldId !== false
                ? (int) $existingFieldId
                : createField($pdo, $fieldName, $fieldDescription);

            bindHashtagToField($pdo, $hashtagId, $fieldId);

            header('Location: ' . buildUrl([
                'type' => 'success',
                'message' => 'Новая область знаний создана, и хэштег успешно привязан.',
            ]));
            exit;
        }

        if ($action === 'unlink') {
            $fieldId = (int) ($_POST['field_id'] ?? 0);

            if ($hashtagId <= 0 || $fieldId <= 0) {
                throw new InvalidArgumentException('Не удалось определить связь для отвязки.');
            }

            unbindHashtagFromField($pdo, $hashtagId, $fieldId);

            header('Location: ' . buildUrl([
                'type' => 'success',
                'message' => 'Хэштег отвязан от области знаний.',
            ]));
            exit;
        }

        throw new InvalidArgumentException('Неизвестное действие формы.');
    } catch (Throwable $exception) {
        header('Location: ' . buildUrl([
            'type' => 'error',
            'message' => $exception->getMessage(),
        ]));
        exit;
    }
}

$flash['type'] = (string) ($_GET['type'] ?? '');
$flash['text'] = (string) ($_GET['message'] ?? '');

$fields = fetchFields($pdo);
$unlinkedHashtags = fetchUnlinkedHashtags($pdo);
$linkedHashtags = fetchLinkedHashtags($pdo);
$allHashtagsCount = (int) $pdo->query('SELECT COUNT(*) FROM hashtags')->fetchColumn();
$linkedHashtagsCount = $allHashtagsCount - count($unlinkedHashtags);
$unlinkedHashtagsCount = count($unlinkedHashtags);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>#сортер</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
    <header class="hero">
        <div class="hero__copy">
            <p class="eyebrow">Course Work</p>
            <h1>#сортер</h1>
            <p class="hero__lead">
                Интерфейс для разбора хэштегов, которые ещё не привязаны к области знаний.
                Можно выбрать готовую область или создать новую прямо во время привязки.
            </p>
        </div>
        <div class="hero__stats">
            <article class="stat-card">
                <span class="stat-card__label">Всего хэштегов</span>
                <strong class="stat-card__value"><?= h($allHashtagsCount) ?></strong>
            </article>
            <article class="stat-card">
                <span class="stat-card__label">Уже привязаны</span>
                <strong class="stat-card__value"><?= h($linkedHashtagsCount) ?></strong>
            </article>
            <article class="stat-card stat-card--accent">
                <span class="stat-card__label">Требуют разбора</span>
                <strong class="stat-card__value"><?= h($unlinkedHashtagsCount) ?></strong>
            </article>
        </div>
    </header>

    <?php if ($flash['text'] !== ''): ?>
        <div class="flash flash--<?= h($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= h($flash['text']) ?>
        </div>
    <?php endif; ?>

    <main class="layout">
        <section class="panel panel--wide">
            <div class="panel__header">
                <div>
                    <h2>Хэштеги без области знаний</h2>
                    <p>Ниже показаны только те хэштеги, которые пока не привязаны ни к одной области знаний.</p>
                </div>
            </div>

            <?php if ($unlinkedHashtags === []): ?>
                <p class="empty-state">
                    Все хэштеги уже разобраны и привязаны к областям знаний.
                </p>
            <?php else: ?>
                <div class="hashtag-list">
                    <?php foreach ($unlinkedHashtags as $hashtag): ?>
                        <article class="hashtag-card">
                            <div class="hashtag-card__summary">
                                <div>
                                    <p class="hashtag-name">#<?= h($hashtag['name']) ?></p>
                                    <p class="meta">
                                        Сообщений: <?= h((int) $hashtag['message_count']) ?> |
                                        Скрытых: <?= h((int) $hashtag['hidden_count']) ?>
                                    </p>
                                </div>
                                <span class="badge">Не привязан</span>
                            </div>

                            <div class="action-grid">
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="assign-existing">
                                    <input type="hidden" name="hashtag_id" value="<?= h((int) $hashtag['id']) ?>">
                                    <label>
                                        <span>Привязать к существующей области</span>
                                        <select name="field_id" required>
                                            <option value="">Выберите область знаний</option>
                                            <?php foreach ($fields as $field): ?>
                                                <option value="<?= h((int) $field['id']) ?>"><?= h($field['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <button type="submit">Привязать</button>
                                </form>

                                <form method="post" class="inline-form inline-form--soft">
                                    <input type="hidden" name="action" value="create-and-assign">
                                    <input type="hidden" name="hashtag_id" value="<?= h((int) $hashtag['id']) ?>">
                                    <label>
                                        <span>Создать новую область</span>
                                        <input type="text" name="field_name" placeholder="Например: Кулинария" required>
                                    </label>
                                    <label>
                                        <span>Описание области</span>
                                        <textarea name="field_description" rows="3" placeholder="Кратко опишите, какие знания сюда относятся."></textarea>
                                    </label>
                                    <button type="submit">Создать и привязать</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <aside class="side-column">
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <h2>Области знаний</h2>
                        <p>Доступные категории для привязки хэштегов.</p>
                    </div>
                </div>

                <div class="field-list">
                    <?php foreach ($fields as $field): ?>
                        <article class="field-card">
                            <div class="field-card__head">
                                <strong><?= h($field['name']) ?></strong>
                                <span><?= h((int) $field['linked_count']) ?> тег.</span>
                            </div>
                            <p><?= h($field['description']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="panel">
                <div class="panel__header">
                    <div>
                        <h2>Уже привязанные хэштеги</h2>
                        <p>Небольшая сводка по уже разобранным данным.</p>
                    </div>
                </div>

                <?php if ($linkedHashtags === []): ?>
                    <p class="empty-state">Пока нет ни одной привязки.</p>
                <?php else: ?>
                    <ul class="linked-list">
                        <?php foreach ($linkedHashtags as $link): ?>
                            <li>
                                <div class="linked-list__meta">
                                    <span>#<?= h($link['hashtag_name']) ?></span>
                                    <strong><?= h($link['field_name']) ?></strong>
                                </div>
                                <form method="post" class="unlink-form">
                                    <input type="hidden" name="action" value="unlink">
                                    <input type="hidden" name="hashtag_id" value="<?= h((int) $link['hashtag_id']) ?>">
                                    <input type="hidden" name="field_id" value="<?= h((int) $link['field_id']) ?>">
                                    <button type="submit" class="button-link-danger">Отвязать</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </aside>
    </main>
</div>
</body>
</html>
