<?php
declare(strict_types=1);

if (!defined('NOTEBOOK_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Прямой доступ запрещен.');
}

function renderViewer(string $sort, int $page): string
{
    $totalContacts = countNotebookContacts();
    $totalPages = max(1, (int) ceil($totalContacts / NOTEBOOK_PAGE_SIZE));
    $page = min(max(1, $page), $totalPages);
    $contacts = fetchNotebookContacts($sort, $page);

    ob_start();
    ?>
    <section class="content-card">
        <div class="content-card__header">
            <div>
                <h1>Просмотр записной книжки</h1>
                <p class="lead">Список контактов с сортировкой и пагинацией по 10 записей.</p>
            </div>
        </div>

        <?php if ($contacts === []): ?>
            <p class="empty-note">Записей пока нет. Добавьте первый контакт через пункт меню «Добавление записи».</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Фамилия</th>
                        <th>Имя</th>
                        <th>Отчество</th>
                        <th>Пол</th>
                        <th>Дата рождения</th>
                        <th>Телефон</th>
                        <th>Адрес</th>
                        <th>E-mail</th>
                        <th>Комментарий</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td><?= h($contact['surname']) ?></td>
                            <td><?= h($contact['name']) ?></td>
                            <td><?= h($contact['patronymic']) ?></td>
                            <td><?= h($contact['gender']) ?></td>
                            <td><?= h($contact['birth_date'] !== '' ? $contact['birth_date'] : '—') ?></td>
                            <td><?= h($contact['phone']) ?></td>
                            <td><?= h($contact['address']) ?></td>
                            <td><?= h($contact['email']) ?></td>
                            <td><?= h($contact['comment']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalContacts > NOTEBOOK_PAGE_SIZE): ?>
                <nav class="pager" aria-label="Пагинация">
                    <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                        <a
                            class="pager__link <?= $pageNumber === $page ? 'is-active' : '' ?>"
                            href="<?= h(buildNotebookUrl(['action' => 'view', 'sort' => $sort, 'page' => $pageNumber])) ?>"
                        >
                            <?= h($pageNumber) ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php

    return (string) ob_get_clean();
}
