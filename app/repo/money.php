<?php
declare(strict_types=1);

const EXPENSE_KINDS = ['daily' => 'Daily costs', 'connects' => 'Upwork Connects', 'tools' => 'Tools and apps', 'other' => 'Other'];
const EXPENSE_ICONS = ['daily' => 'shopping-bag', 'connects' => 'zap', 'tools' => 'app-window', 'other' => 'receipt'];
const INCOME_SOURCES = ['upwork' => 'Upwork', 'direct' => 'Direct'];
const EXPENSE_COLORS = ['daily' => 'orange', 'connects' => 'green', 'tools' => 'purple', 'other' => 'gray'];
const CONNECTS_PACK = [200, 30.0]; // Connects, USD
const BUY_QUADRANTS = [
    'iu' => [1, 1, 'Important and urgent', 'Buy this week', 'circle-alert', 'pink'],
    'in' => [1, 0, 'Important, not urgent', 'Plan and save for it', 'calendar', 'blue'],
    'nu' => [0, 1, 'Urgent, not important', 'Quick buy, keep it cheap', 'zap', 'orange'],
    'nn' => [0, 0, 'Neither', 'Maybe later', 'pause', 'yellow'],
];

function income_goal(): float
{
    return (float) setting('income_goal', '10000') ?: 10000.0;
}

/** SQL expression that converts a row's amount to USD with its saved rate. */
const USD_SQL = "CASE WHEN currency = 'PKR' THEN amount / rate ELSE amount END";

/** Paid income between two dates, split by source. */
function income_between(string $from, string $to): array
{
    $sums = array_column(rows(
        'SELECT source, SUM(' . USD_SQL . ") AS total FROM income WHERE status = 'paid' AND received_on BETWEEN ? AND ? GROUP BY source",
        [$from, $to]
    ), 'total', 'source');
    $upwork = (float) ($sums['upwork'] ?? 0);
    $direct = (float) ($sums['direct'] ?? 0);
    return ['upwork' => $upwork, 'direct' => $direct, 'total' => $upwork + $direct];
}

/** This month against the goal: earned, percent, needed per day, change against last month at the same day. */
function income_month(): array
{
    $today = today();
    $start = month_start();
    $end = month_end();
    $now = income_between($start, $today);
    [$lastStart, $lastSameDay] = last_month_to_date();
    $last = income_between($lastStart, $lastSameDay);
    $daysLeft = days_between($today, $end) + 1;
    $goal = income_goal();
    return $now + [
        'goal' => $goal,
        'percent' => pct($now['total'], $goal),
        'left' => max(0, $goal - $now['total']),
        'per_day' => $daysLeft > 0 ? max(0, $goal - $now['total']) / $daysLeft : 0,
        'days_left' => $daysLeft,
        'change' => $last['total'] > 0 ? ($now['total'] - $last['total']) / $last['total'] * 100 : null,
        'last_month_label' => day($lastStart)->format('M'),
    ];
}

/** This month in week buckets (1 to 7, 8 to 14, ...), with the weekly pace to hit the goal. */
function income_weeks(): array
{
    $start = day(month_start());
    $daysInMonth = (int) $start->format('t');
    $today = today();
    $pace = income_goal() / ($daysInMonth / 7);
    $weeks = [];
    for ($first = 1; $first <= $daysInMonth; $first += 7) {
        $last = min($first + 6, $daysInMonth);
        $from = $start->modify('+' . ($first - 1) . ' days')->format('Y-m-d');
        $to = $start->modify('+' . ($last - 1) . ' days')->format('Y-m-d');
        $sum = $from <= $today ? income_between($from, $to) : ['upwork' => 0, 'direct' => 0, 'total' => 0];
        $weeks[] = $sum + [
            'label' => $first . ' to ' . $last . ' ' . $start->format('M'),
            'state' => $from > $today ? 'future' : ($to >= $today ? 'current' : 'past'),
            'pace' => $pace * (($last - $first + 1) / 7),
        ];
    }
    return ['weeks' => $weeks, 'pace' => $pace];
}

/** Paid income per month for a year, split by source. */
function income_by_month(int $year): array
{
    $months = array_fill(1, 12, ['upwork' => 0.0, 'direct' => 0.0]);
    foreach (rows(
        'SELECT MONTH(received_on) AS m, source, SUM(' . USD_SQL . ") AS total FROM income
         WHERE status = 'paid' AND YEAR(received_on) = ? GROUP BY m, source",
        [$year]
    ) as $r) {
        $months[(int) $r['m']][$r['source']] = (float) $r['total'];
    }
    return $months;
}

function income_open(): array
{
    return rows("SELECT * FROM income WHERE status <> 'paid' ORDER BY due_on IS NULL, due_on");
}

function income_list(string $from, string $to): array
{
    return rows("SELECT * FROM income WHERE received_on BETWEEN ? AND ? ORDER BY received_on DESC, id DESC", [$from, $to]);
}

/** Spend between two dates in USD, by kind. */
function spend_between(string $from, string $to): array
{
    $sums = array_column(rows(
        'SELECT kind, SUM(' . USD_SQL . ') AS total FROM expenses WHERE spent_on BETWEEN ? AND ? GROUP BY kind',
        [$from, $to]
    ), 'total', 'kind');
    $out = [];
    foreach (array_keys(EXPENSE_KINDS) as $kind) {
        $out[$kind] = (float) ($sums[$kind] ?? 0);
    }
    $out['total'] = array_sum($out);
    return $out;
}

/** This month's spend with the change against last month at the same day. */
function spend_month(): array
{
    $today = today();
    $now = spend_between(month_start(), $today);
    $last = spend_between(...last_month_to_date());
    $now['change'] = $last['total'] > 0 ? ($now['total'] - $last['total']) / $last['total'] * 100 : null;
    $now['daily_pkr'] = (float) val("SELECT SUM(CASE WHEN currency = 'PKR' THEN amount ELSE amount * rate END) FROM expenses WHERE kind = 'daily' AND spent_on BETWEEN ? AND ?", [month_start(), $today]);
    return $now;
}

function expenses_list(string $from, string $to): array
{
    return rows('SELECT * FROM expenses WHERE spent_on BETWEEN ? AND ? ORDER BY spent_on DESC, id DESC', [$from, $to]);
}

/** Tool subscriptions that renew in the next 30 days. */
function renewals(): array
{
    return rows(
        "SELECT title, amount, currency, rate, renews_on FROM expenses
         WHERE renews_on BETWEEN ? AND ? ORDER BY renews_on",
        [today(), add_days(today(), 30)]
    );
}

/** Spend per month for the last $count months, oldest first, by kind. */
function spend_by_month(int $count = 6): array
{
    $out = [];
    for ($i = $count - 1; $i >= 0; $i--) {
        $start = day(month_start())->modify("-$i months");
        $out[$start->format('M')] = spend_between($start->format('Y-m-d'), $start->format('Y-m-t'));
    }
    return $out;
}

function buy_items(): array
{
    return rows('SELECT * FROM buy_items WHERE bought_at IS NULL ORDER BY position, id');
}

function buy_bought_since(string $date): array
{
    return rows('SELECT * FROM buy_items WHERE bought_at >= ? ORDER BY bought_at DESC', [$date . ' 00:00:00']);
}
