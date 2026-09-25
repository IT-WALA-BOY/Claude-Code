<?php
declare(strict_types=1);

const MAKER_DEFAULT_MINUTES = 60;
const MAKER_GAP_MINUTES = 15;

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

/** The visible and working window in minutes from midnight. The end can pass midnight (e.g. 18:00 to 26:00). */
function work_window(): array
{
    $toMin = fn (string $hhmm) => (int) substr($hhmm, 0, 2) * 60 + (int) substr($hhmm, 3, 2);
    $start = $toMin(setting('work_start', '18:00'));
    $end = $toMin(setting('work_end', '02:00'));
    if ($end <= $start) {
        $end += 1440;
    }
    return [$start, $end];
}

/** Grid hours: one hour before work starts to the end of work, whole hours. */
function grid_window(): array
{
    [$start, $end] = work_window();
    return [max(0, intdiv($start, 60) - 1) * 60, (int) ceil($end / 60) * 60];
}

/**
 * The work night a moment belongs to. With a window of 17:00 to 26:00, 00:30 on the 25th belongs to the 24th.
 */
function night_of(string $datetime): string
{
    [$gridStart, $gridEnd] = grid_window();
    $minutes = (int) substr($datetime, 11, 2) * 60 + (int) substr($datetime, 14, 2);
    $date = substr($datetime, 0, 10);
    return $gridEnd > 1440 && $minutes < $gridEnd - 1440 && $minutes < $gridStart ? add_days($date, -1) : $date;
}

/** Blocks grouped by work night for a range of nights. */
function blocks_by_night(string $from, string $to): array
{
    $out = [];
    foreach (blocks_between($from, add_days($to, 1)) as $b) {
        $night = night_of($b['starts_at']);
        if ($night >= $from && $night <= $to) {
            $out[$night][] = $b;
        }
    }
    return $out;
}

/** Minutes from the night's midnight: 00:30 the next day is 1470. */
function minutes_into_night(string $datetime, string $night): int
{
    return (int) ((strtotime($datetime) - strtotime($night . ' 00:00:00')) / 60);
}

/** Today's plan: blocks, planned hours, hours against yesterday, what is next. */
function schedule_today(): array
{
    $tonight = night_of(now()->format('Y-m-d H:i:s'));
    $byNight = blocks_by_night(add_days($tonight, -1), $tonight);
    $blocks = $byNight[$tonight] ?? [];
    $now = now()->format('Y-m-d H:i:s');
    $left = array_values(array_filter($blocks, fn ($b) => $b['starts_at'] > $now && !$b['suggested']));
    $planned = array_filter($blocks, fn ($b) => !$b['suggested']);
    $minutes = array_sum(array_map('block_minutes', $planned));
    return [
        'night' => $tonight,
        'blocks' => array_values($planned),
        'hours' => $minutes / 60,
        'change' => ($minutes - array_sum(array_map('block_minutes', $byNight[add_days($tonight, -1)] ?? []))) / 60,
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

function block_json(array $b): string
{
    return json_encode([
        'id' => (int) $b['id'],
        'title' => $b['title'],
        'note' => $b['note'],
        'category_id' => $b['category_id'] ? (int) $b['category_id'] : '',
        'day' => night_of($b['starts_at']),
        'start' => substr($b['starts_at'], 11, 5),
        'end' => substr($b['ends_at'], 11, 5),
        'done' => (bool) $b['done'],
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Schedule maker. Fills free time in the work window for the next $days nights:
 * goal study blocks first, then open tasks by importance and urgency, then due date.
 * Tasks that don't fit spill to the next night. Results are saved as suggestions.
 */
function make_schedule(string $fromNight, int $days = 7): int
{
    q('DELETE FROM schedule_blocks WHERE suggested = 1');
    [$workStart, $workEnd] = work_window();
    $nowMin = null;
    $count = 0;

    $scheduled = array_map('intval', array_column(rows(
        'SELECT DISTINCT task_id FROM schedule_blocks WHERE task_id IS NOT NULL AND starts_at >= ?',
        [$fromNight . ' 00:00:00']
    ), 'task_id'));
    $queue = array_values(array_filter(
        rows(
            "SELECT id, title, category_id, est_minutes FROM tasks WHERE status IN ('todo', 'doing', 'review')
             ORDER BY (important AND urgent) DESC, due_on IS NULL, due_on, important DESC, FIELD(priority, 'urgent', 'moderate', 'low')"
        ),
        fn ($t) => !in_array((int) $t['id'], $scheduled, true)
    ));
    $goals = rows("SELECT id, title, category_id, daily_minutes FROM goals WHERE parent_id IS NULL AND status = 'active' AND daily_minutes > 0");
    $byNight = blocks_by_night($fromNight, add_days($fromNight, $days - 1));

    for ($i = 0; $i < $days; $i++) {
        $night = add_days($fromNight, $i);
        $busy = array_map(fn ($b) => [minutes_into_night($b['starts_at'], $night), minutes_into_night($b['ends_at'], $night)], $byNight[$night] ?? []);
        $start = $workStart;
        if ($night === night_of(now()->format('Y-m-d H:i:s'))) {
            $nowMin ??= minutes_into_night(now()->format('Y-m-d H:i:s'), $night);
            $start = max($start, (int) ceil($nowMin / 15) * 15);
        }

        $want = [];
        foreach ($goals as $g) {
            $hasBlock = array_filter($byNight[$night] ?? [], fn ($b) => (int) $b['goal_id'] === (int) $g['id']);
            if (!$hasBlock) {
                $want[] = ['goal_id' => (int) $g['id'], 'title' => $g['title'], 'category_id' => $g['category_id'], 'minutes' => (int) $g['daily_minutes']];
            }
        }
        foreach ($queue as $t) {
            $want[] = ['task_id' => (int) $t['id'], 'title' => $t['title'], 'category_id' => $t['category_id'], 'minutes' => (int) ($t['est_minutes'] ?: MAKER_DEFAULT_MINUTES)];
        }

        foreach ($want as $item) {
            $slot = find_slot($busy, $start, $workEnd, $item['minutes']);
            if ($slot === null) {
                continue;
            }
            $busy[] = [$slot, $slot + $item['minutes'] + MAKER_GAP_MINUTES];
            insert('schedule_blocks', [
                'starts_at' => night_time($night, $slot),
                'ends_at' => night_time($night, $slot + $item['minutes']),
                'title' => $item['title'],
                'category_id' => $item['category_id'],
                'task_id' => $item['task_id'] ?? null,
                'goal_id' => $item['goal_id'] ?? null,
                'source' => isset($item['goal_id']) ? 'goal' : 'maker',
                'suggested' => 1,
            ]);
            $count++;
            if (isset($item['task_id'])) {
                $queue = array_values(array_filter($queue, fn ($t) => (int) $t['id'] !== $item['task_id']));
            }
        }
    }
    return $count;
}

/** First start minute where $length fits between busy ranges, on a 15 minute grid. */
function find_slot(array $busy, int $from, int $to, int $length): ?int
{
    usort($busy, fn ($a, $b) => $a[0] <=> $b[0]);
    $at = $from;
    foreach ($busy as [$bs, $be]) {
        if ($be <= $at) {
            continue;
        }
        if ($bs - $at >= $length) {
            return $at;
        }
        $at = max($at, (int) ceil($be / 15) * 15);
    }
    return $to - $at >= $length ? $at : null;
}

function night_time(string $night, int $minutes): string
{
    return date('Y-m-d H:i:s', strtotime($night . ' 00:00:00') + $minutes * 60);
}
