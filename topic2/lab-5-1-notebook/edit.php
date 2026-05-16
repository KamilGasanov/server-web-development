<?php
declare(strict_types=1);

if (!defined('NOTEBOOK_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Прямой доступ запрещен.');
}

function renderEditPage(): string
{
    $contacts = fetchNotebookContactsForSelection();

    ob_start();
    ?>
    <section class="content-card">
        <div class="content-card__header">
            <div>
                <h1>Редактирование записи</h1>
                <p class="lead">Выберите контакт из списка, измените поля и сохраните изменения.</p>
            </div>
        </div>

        <?php if ($contacts === []): ?>
            <p class="empty-note">В записной книжке пока нет записей для редактирования.</p>
        <?php else: ?>
            <?php
            $selectedId = getNotebookSelectedId() ?? (int) $contacts[0]['id'];
            $selectedContact = fetchNotebookContactById($selectedId) ?? $contacts[0];
            $selectedId = (int) $selectedContact['id'];
            $values = normalizeContactInput($selectedContact);
            $message = '';
            $messageType = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $postedId = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);
                $postedId = $postedId !== false && $postedId !== null ? $postedId : $selectedId;
                $selectedContact = fetchNotebookContactById($postedId) ?? $selectedContact;
                $selectedId = (int) $selectedContact['id'];
                $values = normalizeContactInput($_POST);
                $errors = validateContact($values);

                if ($errors === []) {
                    try {
                        if (updateNotebookContact($selectedId, $values)) {
                            header('Location: ' . buildNotebookUrl([
                                'action' => 'edit',
                                'id' => $selectedId,
                                'status' => 'success',
                            ]));
                            exit;
                        }
                    } catch (Throwable) {
                        $message = 'Ошибка: запись не изменена.';
                        $messageType = 'error';
                    }
                }

                if ($message === '') {
                    $message = $errors === []
                        ? 'Ошибка: запись не изменена.'
                        : 'Ошибка: запись не изменена. ' . implode(' ', $errors);
                    $messageType = 'error';
                }
            } elseif (($_GET['status'] ?? '') === 'success') {
                $message = 'Запись обновлена.';
                $messageType = 'success';
            }
            ?>

            <div class="selection-list">
                <?php foreach ($contacts as $contact): ?>
                    <a
                        class="selection-list__link <?= (int) $contact['id'] === $selectedId ? 'is-current' : '' ?>"
                        href="<?= h(buildNotebookUrl(['action' => 'edit', 'id' => (int) $contact['id']])) ?>"
                    >
                        <?= h($contact['surname'] . ' ' . $contact['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($message !== ''): ?>
                <div class="status-message status-message--<?= h($messageType) ?>"><?= h($message) ?></div>
            <?php endif; ?>

            <?= renderNotebookContactForm(
                $values,
                'Сохранить изменения',
                '<input type="hidden" name="contact_id" value="' . h($selectedId) . '">'
            ) ?>
        <?php endif; ?>
    </section>
    <?php

    return (string) ob_get_clean();
}
