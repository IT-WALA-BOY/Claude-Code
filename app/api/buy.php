<?php
declare(strict_types=1);

function api_buy_save(): array
{
    $data = [
        'title' => in_str('title', 160, true),
        'note' => in_str('note'),
        'est_cost' => max(0, (float) in_num('est_cost', 0)),
        'currency' => in_enum('currency', ['USD', 'PKR'], 'PKR'),
        'important' => in_bool('important'),
        'urgent' => in_bool('urgent'),
    ];
    $id = in_int('id');
    if ($id) {
        update('buy_items', $id, $data);
        return ['id' => $id];
    }
    return ['id' => insert('buy_items', $data + ['position' => (int) val('SELECT COALESCE(MAX(position), 0) + 1 FROM buy_items')])];
}

function api_buy_delete(): array
{
    delete_row('buy_items', (int) in_int('id'));
    return [];
}

/** Drag between quadrants: new flags plus the order of that quadrant. */
function api_buy_move(): array
{
    update('buy_items', (int) in_int('id'), ['important' => in_bool('important'), 'urgent' => in_bool('urgent')]);
    reorder('buy_items', in_ids('order'));
    return [];
}

/** Bought: log it as an expense today and take it off the list. */
function api_buy_bought(): array
{
    $item = row('SELECT * FROM buy_items WHERE id = ? AND bought_at IS NULL', [in_int('id')]) ?? fail('That item is already bought or deleted.');
    $expenseId = insert('expenses', [
        'title' => $item['title'], 'kind' => 'other', 'amount' => $item['est_cost'], 'currency' => $item['currency'], 'rate' => rate(),
        'spent_on' => today(), 'note' => 'From the buy list', 'paid_with' => '',
    ]);
    update('buy_items', (int) $item['id'], ['bought_at' => now()->format('Y-m-d H:i:s'), 'expense_id' => $expenseId]);
    return [];
}

/** Undo "Mark bought": remove the expense it created and put the item back. */
function api_buy_unbought(): array
{
    $item = row('SELECT id, expense_id FROM buy_items WHERE id = ?', [in_int('id')]) ?? fail('That item no longer exists.');
    if ($item['expense_id']) {
        delete_row('expenses', (int) $item['expense_id']);
    }
    update('buy_items', (int) $item['id'], ['bought_at' => null, 'expense_id' => null]);
    return [];
}
