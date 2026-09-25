<?php
declare(strict_types=1);

/** Top-level goals with category color and pace status. */
function goals_list(bool $activeOnly = true): array
{
    $goals = rows(
        'SELECT g.*, c.color AS cat_color, c.name AS cat_name,
                (SELECT COUNT(*) FROM goals s WHERE s.parent_id = g.id) AS parts
         FROM goals g LEFT JOIN categories c ON c.id = g.category_id
         WHERE g.parent_id IS NULL' . ($activeOnly ? " AND g.status = 'active'" : '') . '
         ORDER BY g.status = \'done\', g.position, g.id'
    );
    return array_map('goal_decorate', $goals);
}

function goal(int $id): ?array
{
    $g = row('SELECT g.*, c.color AS cat_color, c.name AS cat_name, 0 AS parts FROM goals g LEFT JOIN categories c ON c.id = g.category_id WHERE g.id = ?', [$id]);
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
    // Per day needed to finish on time, in the goal's unit.
    $g['per_day'] = $g['days_left'] !== null && $g['days_left'] > 0 ? $g['left'] / $g['days_left'] : null;
    return $g;
}

/** "24 of 64 h", "2 of 5 screens". */
function goal_amount(array $g): string
{
    return num($g['done']) . ' of ' . num($g['target']) . ' ' . $g['unit'];
}
