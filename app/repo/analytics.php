<?php
declare(strict_types=1);

const ANALYTICS_PERIODS = ['week' => 'Week', 'month' => 'Month', 'quarter' => 'Quarter', 'year' => 'Year'];
const ON_TIME_PERCENT = 80;

/** First day of the period on the app clock. Ends today. */
function period_start(string $period): string
{
    return match ($period) {
        'week' => week_start(),
        'quarter' => day(month_start())->modify('-2 months')->format('Y-m-d'),
        'year' => now()->format('Y-01-01'),
        default => month_start(),
    };
}

function period_label(string $period): string
{
    return match ($period) {
        'week' => 'This week',
        'quarter' => 'Last 3 months',
        'year' => now()->format('Y') . ' so far',
        default => now()->format('F') . ' so far',
    };
}

/** Planned and done hours per category from schedule blocks. [cat id => [name, color, planned, done]] */
function time_vs_plan(string $from): array
{
    $out = [];
    foreach (rows(
        "SELECT c.id, c.name, c.color,
                SUM(TIMESTAMPDIFF(MINUTE, b.starts_at, b.ends_at)) / 60 AS planned,
                SUM(CASE WHEN b.done = 1 THEN TIMESTAMPDIFF(MINUTE, b.starts_at, b.ends_at) ELSE 0 END) / 60 AS done
         FROM schedule_blocks b JOIN categories c ON c.id = b.category_id
         WHERE b.suggested = 0 AND b.starts_at BETWEEN ? AND ?
         GROUP BY c.id, c.name, c.color ORDER BY c.position",
        [$from . ' 00:00:00', now()->format('Y-m-d H:i:s')]
    ) as $r) {
        $out[] = ['name' => $r['name'], 'color' => $r['color'], 'planned' => (float) $r['planned'], 'done' => (float) $r['done']];
    }
    return $out;
}

/** Tasks per category: done in the period, still open, and overdue. */
function workload(string $from): array
{
    return array_map(fn ($r) => array_map(fn ($v) => is_numeric($v) ? (int) $v : $v, $r), rows(
        "SELECT c.name, c.color,
                SUM(t.status = 'done' AND t.completed_at >= ?) AS completed,
                SUM(t.status <> 'done' AND (t.due_on IS NULL OR t.due_on >= ?)) AS remaining,
                SUM(t.status <> 'done' AND t.due_on < ?) AS overdue
         FROM categories c LEFT JOIN tasks t ON t.category_id = c.id
         GROUP BY c.id, c.name, c.color, c.position ORDER BY c.position",
        [$from . ' 00:00:00', today(), today()]
    ));
}

/** Tasks done and added per week, for the last $weeks weeks (Monday start). */
function tasks_by_week(int $weeks = 8): array
{
    $out = [];
    $start = day(week_start())->modify('-' . ($weeks - 1) . ' weeks');
    for ($i = 0; $i < $weeks; $i++) {
        $from = $start->modify("+$i weeks")->format('Y-m-d');
        $to = add_days($from, 7);
        $out[$from] = [
            'done' => (int) val("SELECT COUNT(*) FROM tasks WHERE status = 'done' AND completed_at >= ? AND completed_at < ?", [$from, $to]),
            'added' => (int) val('SELECT COUNT(*) FROM tasks WHERE created_at >= ? AND created_at < ?', [$from, $to]),
        ];
    }
    return $out;
}

/** Share of tasks due in the period that are done. */
function planned_done_percent(string $from): int
{
    $row = row(
        "SELECT COUNT(*) AS total, SUM(status = 'done') AS done FROM tasks WHERE due_on BETWEEN ? AND ?",
        [$from, today()]
    );
    return pct((float) ($row['done'] ?? 0), (float) ($row['total'] ?? 0));
}

/** Hours planned and done per week from schedule blocks, last $weeks weeks. */
function hours_by_week(int $weeks = 8): array
{
    $out = [];
    $start = day(week_start())->modify('-' . ($weeks - 1) . ' weeks');
    for ($i = 0; $i < $weeks; $i++) {
        $from = $start->modify("+$i weeks")->format('Y-m-d');
        $row = row(
            'SELECT SUM(TIMESTAMPDIFF(MINUTE, starts_at, ends_at)) / 60 AS planned,
                    SUM(CASE WHEN done = 1 THEN TIMESTAMPDIFF(MINUTE, starts_at, ends_at) ELSE 0 END) / 60 AS done
             FROM schedule_blocks WHERE suggested = 0 AND starts_at >= ? AND starts_at < ?',
            [$from . ' 00:00:00', add_days($from, 7) . ' 00:00:00']
        );
        $out[$from] = ['planned' => (float) ($row['planned'] ?? 0), 'done' => (float) ($row['done'] ?? 0)];
    }
    return $out;
}
