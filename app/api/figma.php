<?php
declare(strict_types=1);

function api_figma_save(): array
{
    $url = in_str('figma_url', 500);
    if ($url !== '' && !preg_match('#^https://([a-z0-9-]+\.)?figma\.com/#i', $url)) {
        fail('Paste a figma.com link, or leave it empty.');
    }
    $data = [
        'name' => in_str('name', 160, true),
        'client' => in_str('client', 120),
        'kind' => in_enum('kind', ['client', 'portfolio'], 'client'),
        'source' => in_enum('source', array_keys(PROJECT_SOURCES), 'upwork'),
        'note' => in_str('note'),
        'figma_url' => $url,
        'status' => in_enum('status', array_keys(PROJECT_STATUSES), 'active'),
        'due_on' => in_date('due_on'),
    ];
    $id = in_int('id');
    if ($id) {
        update('figma_projects', $id, $data);
        return ['id' => $id];
    }
    return ['id' => insert('figma_projects', $data + ['created_at' => now()->format('Y-m-d H:i:s')])];
}

function api_figma_delete(): array
{
    delete_row('figma_projects', (int) in_int('id'));
    return [];
}

function api_figma_opened(): array
{
    update('figma_projects', (int) in_int('id'), ['last_opened_at' => now()->format('Y-m-d H:i:s')]);
    return [];
}
