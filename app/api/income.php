<?php
declare(strict_types=1);

function api_income_save(): array
{
    $amount = in_num('amount');
    if ($amount === null || $amount <= 0) {
        fail('Enter an amount above 0.');
    }
    $status = in_enum('status', ['paid', 'pending', 'escrow'], 'paid');
    $data = [
        'client' => in_str('client', 120, true),
        'title' => in_str('title', 160),
        'source' => in_enum('source', array_keys(INCOME_SOURCES), 'upwork'),
        'amount' => $amount,
        'currency' => in_enum('currency', ['USD', 'PKR'], 'USD'),
        'rate' => rate(),
        'status' => $status,
        'received_on' => in_date('received_on') ?? today(),
        'due_on' => $status === 'paid' ? null : in_date('due_on'),
    ];
    $id = in_int('id');
    if ($id) {
        update('income', $id, $data);
        return ['id' => $id];
    }
    return ['id' => insert('income', $data)];
}

function api_income_delete(): array
{
    delete_row('income', (int) in_int('id'));
    return [];
}

/** Invoice or escrow arrived: mark paid today. */
function api_income_paid(): array
{
    update('income', (int) in_int('id'), ['status' => 'paid', 'received_on' => today(), 'due_on' => null]);
    return [];
}

/** Late invoice: add a follow-up task due today. */
function api_income_remind(): array
{
    $inv = row('SELECT client, amount, currency, rate FROM income WHERE id = ?', [in_int('id')]) ?? fail('That invoice no longer exists.');
    $id = task_save([
        'title' => 'Chase invoice: ' . $inv['client'] . ', ' . usd(to_usd($inv['amount'], $inv['currency'], $inv['rate'])),
        'notes' => 'Invoice is late. Send a friendly reminder.',
        'category_id' => null, 'status' => 'todo', 'priority' => 'urgent', 'important' => 1, 'urgent' => 1,
        'start_on' => today(), 'due_on' => today(), 'due_time' => null, 'est_minutes' => 15, 'project_id' => null,
    ], [], null);
    return ['task' => $id];
}
