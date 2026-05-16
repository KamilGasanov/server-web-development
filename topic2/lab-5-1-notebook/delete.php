<?php
declare(strict_types=1);

if (!defined('NOTEBOOK_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Прямой доступ запрещен.');
}

function buildDeleteContactLabel(array $contact): string
{
    $nameInitial = function_exists('mb_substr')
        ? mb_substr((string) $contact['name'], 0, 1)
        : substr((string) $contact['name'], 0, 1);
    $initials = $nameInitial . '.';

    if ((string) $contact['patronymic'] !== '') {
        $patronymicInitial = function_exists('mb_substr')
            ? mb_substr((string) $contact['patronymic'], 0, 1)
            : substr((string) $contact['patronymic'], 0, 1);
        $initials .= ' ' . $patronymicInitial . '.';
    }

    return trim($contact['surname'] . ' ' . $initials);
}

function renderDeletePage(): string
{
    $deleteId = getNotebookSelectedId('delete_id');

    if ($deleteId !== null) {
        try {
            $deletedSurname = deleteNotebookContact($deleteId);
        } catch (Throwable) {
            $deletedSurname = null;
        }

        if ($deletedSurname !== null) {
            header('Location: ' . buildNotebookUrl([
                'action' => 'delete',
                'status' => 'success',
                'surname' => $deletedSurname,
            ]));
            exit;
        }

        header('Location: ' . buildNotebookUrl([
            'action' => 'delete',
            'status' => 'error',
        ]));
        exit;
    }

    $contacts = fetchNotebookContactsForSelection();
    $message = '';
    $messageType = '';

    if (($_GET['status'] ?? '') === 'success') {
        $message = 'Запись с фамилией ' . (string) ($_GET['surname'] ?? '') . ' удалена.';
        $messageType = 'success';
    } elseif (($_GET['status'] ?? '') === 'error') {
        $message = 'Ошибка: запись не удалена.';
        $messageType = 'error';
    }

    ob_start();
    ?>
    <section class="content-card">
        <div class="content-card__header">
            <div>
                <h1>Удаление записи</h1>
                <p class="lead">Нажмите на контакт, который нужно удалить из записной книжки.</p>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="status-message status-message--<?= h($messageType) ?>"><?= h($message) ?></div>
        <?php endif; ?>

        <?php if ($contacts === []): ?>
            <p class="empty-note">В записной книжке пока нет записей для удаления.</p>
        <?php else: ?>
            <div class="selection-list">
                <?php foreach ($contacts as $contact): ?>
                    <a
                        class="selection-list__link"
                        href="<?= h(buildNotebookUrl(['action' => 'delete', 'delete_id' => (int) $contact['id']])) ?>"
                    >
                        <?= h(buildDeleteContactLabel($contact)) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php

    return (string) ob_get_clean();
}
