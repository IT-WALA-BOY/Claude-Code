<?php
declare(strict_types=1);

/** Downloads income or expenses as CSV (opens in Excel or Google Sheets), or a JSON backup of everything. */
function export_csv(string $type): void
{
    if ($type === 'backup') {
        export_backup();
        return;
    }
    [$file, $header, $rows] = match ($type) {
        'income' => ['income', ['Date', 'Client', 'For', 'Source', 'Status', 'Amount', 'Currency', 'Due'],
            rows('SELECT received_on, client, title, source, status, amount, currency, due_on FROM income ORDER BY received_on DESC')],
        'expenses' => ['expenses', ['Date', 'Kind', 'Title', 'Amount', 'Currency', 'Rate', 'Quantity', 'Renews', 'Note'],
            rows('SELECT spent_on, kind, title, amount, currency, rate, quantity, renews_on, note FROM expenses ORDER BY spent_on DESC')],
        default => fail('Unknown export.'),
    };
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $file . '-' . today() . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, $header);
    foreach ($rows as $r) {
        // A leading = + - @ would run as a formula in spreadsheets, so prefix it.
        fputcsv($out, array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, $r));
    }
    fclose($out);
}

/** Every table except the login data. The API key is left out on purpose. */
function export_backup(): void
{
    $tables = ['settings', 'categories', 'tasks', 'task_checklist', 'goals', 'goal_logs', 'schedule_blocks', 'expenses', 'income',
        'buy_items', 'li_prospects', 'li_posts', 'li_daily', 'figma_projects'];
    $data = ['exported_at' => now()->format(DATE_ATOM)];
    foreach ($tables as $table) {
        $data[$table] = rows("SELECT * FROM $table");
    }
    $data['settings'] = array_values(array_filter($data['settings'], fn ($r) => $r['k'] !== 'anthropic_key'));
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="workflow-backup-' . today() . '.json"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
