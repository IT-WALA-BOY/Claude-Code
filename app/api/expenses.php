<?php
declare(strict_types=1);

function api_expenses_save(): array
{
    $amount = in_num('amount');
    if ($amount === null || $amount <= 0) {
        fail('Enter an amount above 0.');
    }
    $kind = in_enum('kind', array_keys(EXPENSE_KINDS), 'daily');
    $data = [
        'title' => in_str('title', 160, true),
        'kind' => $kind,
        'amount' => $amount,
        'currency' => in_enum('currency', ['USD', 'PKR'], 'PKR'),
        'rate' => rate(),
        'spent_on' => in_date('spent_on') ?? today(),
        'quantity' => $kind === 'connects' ? in_int('quantity') : null,
        'paid_with' => in_str('paid_with', 40),
        'renews_on' => in_date('renews_on'),
        'note' => in_str('note'),
    ];
    $id = in_int('id');
    if ($id) {
        unset($data['rate']); // keep the rate the expense was logged with
        update('expenses', $id, $data);
        return ['id' => $id];
    }
    if ($data['quantity']) {
        save_setting('connects_left', (string) ((int) setting('connects_left', '0') + $data['quantity']));
    }
    return ['id' => insert('expenses', $data)];
}

function api_expenses_delete(): array
{
    delete_row('expenses', (int) in_int('id'));
    return [];
}

function api_expenses_connects(): array
{
    save_setting('connects_left', (string) max(0, (int) in_int('connects_left', 0)));
    return [];
}

/** One tap: log a Connects pack as an expense and add it to the balance. */
function api_expenses_buy_connects(): array
{
    [$qty, $usd] = CONNECTS_PACK;
    $id = insert('expenses', [
        'title' => "$qty Upwork Connects", 'kind' => 'connects', 'amount' => $usd, 'currency' => 'USD', 'rate' => rate(),
        'spent_on' => today(), 'quantity' => $qty, 'paid_with' => 'Upwork balance', 'note' => '',
    ]);
    save_setting('connects_left', (string) ((int) setting('connects_left', '0') + $qty));
    return ['id' => $id];
}
