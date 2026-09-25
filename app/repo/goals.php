<?php
declare(strict_types=1);

/** Top-level goals with category color and pace status. */
function goals_list(bool $activeOnly = true): array
{
    $goals = rows(
        'SELECT g.*, c.color AS cat_color, c.name AS cat_name, c.icon AS cat_icon,
                (SELECT COUNT(*) FROM goals s WHERE s.parent_id = g.id) AS parts
         FROM goals g LEFT JOIN categories c ON c.id = g.category_id
         WHERE g.parent_id IS NULL' . ($activeOnly ? " AND g.status = 'active'" : '') . '
         ORDER BY g.status = \'done\', g.position, g.id'
    );
    return array_map('goal_decorate', $goals);
}

function goal(int $id): ?array
{
    $g = row('SELECT g.*, c.color AS cat_color, c.name AS cat_name, c.icon AS cat_icon, 0 AS parts FROM goals g LEFT JOIN categories c ON c.id = g.category_id WHERE g.id = ?', [$id]);
    return $g ? goal_decorate($g) : null;
}

function goal_parts(int $id): array
{
    return rows('SELECT * FROM goals WHERE parent_id = ? ORDER BY position, id', [$id]);
}

/**
 * Adds percent, expected progress by today, and a status:
 * On pace (at or ahead of a straight line from start to due), Behind (under by up to 15 points), At risk (more).
 */
function goal_decorate(array $g): array
{
    $target = (float) $g['target'];
    $done = (float) $g['done'];
    $g['percent'] = pct($done, $target);
    $g['left'] = max(0, $target - $done);
    $expected = 0;
    if ($g['start_on'] && $g['due_on']) {
        $span = max(1, days_between($g['start_on'], $g['due_on']));
        $expected = pct((float) max(0, min($span, days_between($g['start_on'], today()))), $span);
    }
    $g['expected'] = $expected;
    $g['days_left'] = $g['due_on'] ? days_between(today(), $g['due_on']) : null;
    $gap = $expected - $g['percent'];
    [$g['pace'], $g['pace_tone']] = match (true) {
        $g['status'] === 'done' || $g['percent'] >= 100 => ['Done', 'positive'],
        $gap <= 5 => ['On pace', 'positive'],
        $gap <= 15 => ['Behind', 'warning'],
        default => ['At risk', 'negative'],
    };
    // Per study day needed to finish on time, in the goal's unit.
    $studyDays = $g['due_on'] && $g['due_on'] >= today() ? count(study_days(today(), $g['due_on'], (int) $g['rest_days'])) : 0;
    $g['study_days'] = $studyDays;
    $g['per_day'] = $studyDays ? $g['left'] / $studyDays : null;
    return $g;
}

/** "24 of 64 h", "2 of 5 screens". */
function goal_amount(array $g): string
{
    return num($g['done']) . ' of ' . num($g['target']) . ' ' . $g['unit'];
}

/** Hours or units logged in the last 7 days, for "on pace" notes. */
function goal_logged_week(int $id): float
{
    return (float) val('SELECT COALESCE(SUM(amount), 0) FROM goal_logs WHERE goal_id = ? AND logged_on > ?', [$id, add_days(today(), -7)]);
}

function goal_sessions_week(int $id): int
{
    return (int) val('SELECT COUNT(*) FROM goal_logs WHERE goal_id = ? AND logged_on > ?', [$id, add_days(today(), -7)]);
}

/** Study days between two dates (inclusive), skipping $restDays per week (Sundays first, then Saturdays). */
function study_days(string $from, string $to, int $restDays): array
{
    $off = array_slice([0, 6], 0, max(0, min(2, $restDays)));
    $out = [];
    for ($d = $from; $d <= $to; $d = add_days($d, 1)) {
        if (!in_array((int) day($d)->format('w'), $off, true)) {
            $out[] = $d;
        }
    }
    return $out;
}

/**
 * Expected dates for each part: finished parts show nothing, the rest are laid out one after another
 * from today at the goal's pace. Returns [part id => [start, end]].
 */
function goal_part_dates(array $goal, array $parts): array
{
    $perDay = $goal['per_day'] ?: 0;
    if ($perDay <= 0) {
        return [];
    }
    $days = study_days(today(), add_days(today(), 400), (int) $goal['rest_days']);
    $cursor = 0.0;
    $out = [];
    foreach ($parts as $p) {
        $left = max(0, (float) $p['target'] - (float) $p['done']);
        if ($left <= 0) {
            continue;
        }
        $startIdx = (int) floor($cursor / $perDay);
        $cursor += $left;
        $endIdx = max($startIdx, (int) ceil($cursor / $perDay) - 1);
        $out[(int) $p['id']] = [$days[$startIdx] ?? null, $days[$endIdx] ?? null];
    }
    return $out;
}

/** Adds progress to a goal. With parts, it fills the first unfinished part and spills into the next. */
function goal_log(int $id, float $amount, string $on, string $note): void
{
    insert('goal_logs', ['goal_id' => $id, 'logged_on' => $on, 'amount' => $amount, 'note' => $note]);
    q('UPDATE goals SET done = LEAST(target, done + ?) WHERE id = ?', [$amount, $id]);
    $left = $amount;
    foreach (goal_parts($id) as $p) {
        $room = (float) $p['target'] - (float) $p['done'];
        if ($left <= 0 || $room <= 0) {
            continue;
        }
        $add = min($room, $left);
        $left -= $add;
        update('goals', (int) $p['id'], ['done' => (float) $p['done'] + $add, 'status' => $add >= $room ? 'done' : 'active']);
    }
    if ((float) val('SELECT target - done FROM goals WHERE id = ?', [$id]) <= 0) {
        update('goals', $id, ['status' => 'done']);
    }
}

/** Puts the goal's daily block on the next 7 nights, in the first free slot of the work window. */
function goal_schedule_blocks(array $goal, int $nights = 7): int
{
    $minutes = (int) $goal['daily_minutes'];
    if ($minutes <= 0) {
        fail('Set a daily pace for this goal first.');
    }
    [$workStart, $workEnd] = work_window();
    $first = night_of(now()->format('Y-m-d H:i:s'));
    $byNight = blocks_by_night($first, add_days($first, $nights - 1));
    $added = 0;
    foreach (study_days($first, add_days($first, $nights - 1), (int) $goal['rest_days']) as $night) {
        $list = $byNight[$night] ?? [];
        if (array_filter($list, fn ($b) => (int) $b['goal_id'] === (int) $goal['id'])) {
            continue;
        }
        $busy = array_map(fn ($b) => [minutes_into_night($b['starts_at'], $night), minutes_into_night($b['ends_at'], $night)], $list);
        $slot = find_slot($busy, $workStart, $workEnd, $minutes);
        if ($slot === null) {
            continue;
        }
        insert('schedule_blocks', [
            'starts_at' => night_time($night, $slot), 'ends_at' => night_time($night, $slot + $minutes),
            'title' => $goal['title'], 'category_id' => $goal['category_id'], 'goal_id' => $goal['id'], 'source' => 'goal',
        ]);
        $added++;
    }
    return $added;
}

function goal_json(array $g, array $parts): string
{
    return json_encode([
        'id' => (int) $g['id'],
        'title' => $g['title'],
        'category_id' => $g['category_id'] ? (int) $g['category_id'] : '',
        'unit' => $g['unit'],
        'target' => (float) $g['target'],
        'done' => (float) $g['done'],
        'due_on' => (string) $g['due_on'],
        'rest_days' => (int) $g['rest_days'],
        'daily_minutes' => $g['daily_minutes'] ? (int) $g['daily_minutes'] : '',
        'parts' => array_map(fn ($p) => ['title' => $p['title'], 'target' => (float) $p['target'], 'done' => (float) $p['done']], $parts),
    ], JSON_UNESCAPED_UNICODE);
}
