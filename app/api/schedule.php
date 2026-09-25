<?php
declare(strict_types=1);

/** Start and end datetimes for a block on a work night. An end at or before the start rolls past midnight. */
function block_times(string $night, string $start, string $end): array
{
    [$gridStart, $gridEnd] = grid_window();
    $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
    $s = $toMin($start);
    if ($gridEnd > 1440 && $s < $gridEnd - 1440 && $s < $gridStart) {
        $s += 1440; // after midnight in a window that crosses midnight: still the same night
    }
    $e = $toMin($end);
    while ($e <= $s) {
        $e += 1440;
    }
    return [night_time($night, $s), night_time($night, $e)];
}

function api_schedule_save(): array
{
    $night = in_date('day') ?? fail('Pick a day.');
    $start = in_time('start') ?? fail('Pick a start time.');
    $end = in_time('end') ?? fail('Pick an end time.');
    [$startsAt, $endsAt] = block_times($night, $start, $end);
    if (strtotime($endsAt) - strtotime($startsAt) > 12 * 3600) {
        fail('A block can be 12 hours at most.');
    }
    $categoryId = in_int('category_id');
    $data = [
        'title' => in_str('title', 160, true),
        'note' => in_str('note'),
        'category_id' => $categoryId && category($categoryId) ? $categoryId : null,
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
        'done' => in_bool('done'),
        'suggested' => 0,
    ];
    $id = in_int('id');
    if ($id) {
        update('schedule_blocks', $id, $data);
        return ['id' => $id];
    }
    return ['id' => insert('schedule_blocks', $data)];
}

function api_schedule_delete(): array
{
    delete_row('schedule_blocks', (int) in_int('id'));
    return [];
}

/** Drag in the week grid: new night and minutes from that night's midnight. */
function api_schedule_move(): array
{
    $night = in_date('night') ?? fail('Unknown day.');
    $start = (int) in_int('start');
    $end = (int) in_int('end');
    if ($end <= $start || $end - $start > 720) {
        fail('That block length is not allowed.');
    }
    update('schedule_blocks', (int) in_int('id'), [
        'starts_at' => night_time($night, $start),
        'ends_at' => night_time($night, $end),
        'suggested' => 0,
    ]);
    return [];
}

function api_schedule_make(): array
{
    return ['count' => make_schedule(night_of(now()->format('Y-m-d H:i:s')))];
}

function api_schedule_accept(): array
{
    q('UPDATE schedule_blocks SET suggested = 0 WHERE suggested = 1');
    return [];
}

function api_schedule_dismiss(): array
{
    q('DELETE FROM schedule_blocks WHERE suggested = 1');
    return [];
}
