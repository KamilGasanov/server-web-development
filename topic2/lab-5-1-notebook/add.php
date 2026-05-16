<?php
declare(strict_types=1);

if (!defined('NOTEBOOK_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Прямой доступ запрещен.');
}

function renderNotebookContactForm(array $values, string $submitLabel, string $hiddenHtml = ''): string
{
    ob_start();
    ?>
    <form class="contact-form" method="post">
        <?= $hiddenHtml ?>
        <div class="form-grid">
            <label class="form-field">
                <span>Фамилия</span>
                <input type="text" name="surname" value="<?= h($values['surname']) ?>" required>
            </label>

            <label class="form-field">
                <span>Имя</span>
                <input type="text" name="name" value="<?= h($values['name']) ?>" required>
            </label>

            <label class="form-field">
                <span>Отчество</span>
                <input type="text" name="patronymic" value="<?= h($values['patronymic']) ?>">
            </label>

            <label class="form-field">
                <span>Пол</span>
                <select name="gender">
                    <?php foreach (NOTEBOOK_GENDERS as $gender): ?>
                        <option value="<?= h($gender) ?>" <?= $values['gender'] === $gender ? 'selected' : '' ?>>
                            <?= h($gender) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="form-field">
                <span>Дата рождения</span>
                <input type="date" name="birth_date" value="<?= h($values['birth_date']) ?>">
            </label>

            <label class="form-field">
                <span>Телефон</span>
                <input type="text" name="phone" value="<?= h($values['phone']) ?>">
            </label>

            <label class="form-field">
                <span>Адрес</span>
                <input type="text" name="address" value="<?= h($values['address']) ?>">
            </label>

            <label class="form-field">
                <span>E-mail</span>
                <input type="email" name="email" value="<?= h($values['email']) ?>">
            </label>

            <label class="form-field form-field--wide">
                <span>Комментарий</span>
                <textarea name="comment" rows="4"><?= h($values['comment']) ?></textarea>
            </label>
        </div>

        <button class="form-button" type="submit"><?= h($submitLabel) ?></button>
    </form>
    <?php

    return (string) ob_get_clean();
}

function renderAddPage(): string
{
    $values = getDefaultContactValues();
    $message = '';
    $messageType = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $values = normalizeContactInput($_POST);
        $errors = validateContact($values);

        if ($errors === []) {
            try {
                if (insertNotebookContact($values)) {
                    header('Location: ' . buildNotebookUrl(['action' => 'add', 'status' => 'success']));
                    exit;
                }
            } catch (Throwable) {
                $message = 'Ошибка: запись не добавлена.';
                $messageType = 'error';
            }
        }

        if ($message === '') {
            $message = $errors === []
                ? 'Ошибка: запись не добавлена.'
                : 'Ошибка: запись не добавлена. ' . implode(' ', $errors);
            $messageType = 'error';
        }
    } elseif (($_GET['status'] ?? '') === 'success') {
        $message = 'Запись добавлена.';
        $messageType = 'success';
    }

    ob_start();
    ?>
    <section class="content-card">
        <div class="content-card__header">
            <div>
                <h1>Добавление записи</h1>
                <p class="lead">Заполните форму и сохраните новый контакт в записной книжке.</p>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="status-message status-message--<?= h($messageType) ?>"><?= h($message) ?></div>
        <?php endif; ?>

        <?= renderNotebookContactForm($values, 'Добавить запись') ?>
    </section>
    <?php

    return (string) ob_get_clean();
}
