<?php
declare(strict_types=1);

if (!defined('NOTEBOOK_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Прямой доступ запрещен.');
}

function renderMenu(): string
{
    $action = getNotebookAction();
    $sort = getNotebookSort();

    $menuItems = [
        'view' => 'Просмотр',
        'add' => 'Добавление записи',
        'edit' => 'Редактирование записи',
        'delete' => 'Удаление записи',
    ];

    $sortItems = [
        'created' => 'По добавлению',
        'surname' => 'По фамилии',
        'birth_date' => 'По дате рождения',
    ];

    ob_start();
    ?>
    <header class="topbar">
        <div class="topbar__inner">
            <div class="brand">
                <span class="brand__title">Notebook</span>
                <span class="brand__subtitle">Записная книжка контактов</span>
            </div>

            <nav class="menu menu--primary" aria-label="Основное меню">
                <?php foreach ($menuItems as $key => $label): ?>
                    <a
                        class="menu__link <?= $action === $key ? 'is-active' : '' ?>"
                        href="<?= h(buildNotebookUrl(['action' => $key])) ?>"
                    >
                        <?= h($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>

    <?php if ($action === 'view'): ?>
        <aside class="submenu-wrap">
            <nav class="menu menu--secondary" aria-label="Сортировка">
                <?php foreach ($sortItems as $key => $label): ?>
                    <a
                        class="menu__link <?= $sort === $key ? 'is-active' : '' ?>"
                        href="<?= h(buildNotebookUrl(['action' => 'view', 'sort' => $key, 'page' => 1])) ?>"
                    >
                        <?= h($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>
    <?php endif; ?>
    <?php

    return (string) ob_get_clean();
}
