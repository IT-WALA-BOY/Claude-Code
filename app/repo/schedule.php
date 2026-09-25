<?php
declare(strict_types=1);

/** Blocks that start on the given Pacific days (inclusive), with category colors. */
function blocks_between(string $from, string $to): array
{
    return rows(
        'SELECT b.*, c.name AS cat_name, c.color AS cat_color, c.icon AS cat_icon
         FROM schedule_blocks b LEFT JOIN categories c ON c.id = b.category_id
         WHERE b.starts_at >= ? AND b.starts_at < ? ORDER BY b.starts_at',
        [$from . ' 00:00:00', add_days($to, 1) . ' 00:00:00']
    );
}

function block_minutes(array $b): int
{
    return (int) ((strtotime($b['ends_at']) - strtotime($b['starts_at'])) / 60);
}

/** Today's plan: blocks, planned hours, hours against yesterday, what is next. */
function schedule_today(): array
{
    $blocks = blocks_between(today(), today());
    $yesterday = blocks_between(add_days(today(), -1), add_days(today(), -1));
    $now = now()->format('Y-m-d H:i:s');
    $left = array_values(array_filter($blocks, fn ($b) => $b['starts_at'] > $now));
    $minutes = array_sum(array_map('block_minutes', $blocks));
    return [
        'blocks' => $blocks,
        'hours' => $minutes / 60,
        'change' => ($minutes - array_sum(array_map('block_minutes', $yesterday))) / 60,
        'left' => count($left),
        'next' => $left[0] ?? null,
    ];
}

/** Done focus hours per day for the heatmap: [Y-m-d => hours]. */
function focus_hours(string $from, string $to): array
{
    $out = [];
    foreach (rows(
        'SELECT DATE(starts_at) AS d, SUM(TIMESTAMPDIFF(MINUTE, starts_at, ends_at)) AS m
         FROM schedule_blocks WHERE done = 1 AND starts_at BETWEEN ? AND ? GROUP BY d',
        [$from . ' 00:00:00', $to . ' 23:59:59']
    ) as $r) {
        $out[$r['d']] = $r['m'] / 60;
    }
    return $out;
}
