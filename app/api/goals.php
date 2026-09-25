<?php
declare(strict_types=1);

/** Saves a goal and its parts from the wizard. Part progress is kept by position on edits. */
function api_goals_save(): array
{
    $target = in_num('target');
    if ($target === null || $target <= 0) {
        fail('Enter the total, for example 64.');
    }
    $categoryId = in_int('category_id');
    $unit = in_str('unit', 20) ?: 'h';
    $perDay = in_num('per_day');
    $data = [
        'title' => in_str('title', 160, true),
        'unit' => $unit,
        'target' => $target,
        'due_on' => in_date('due_on') ?? fail('Pick a finish date, or a pace per day.'),
        'rest_days' => max(0, min(2, (int) in_int('rest_days', 0))),
        'daily_minutes' => $unit === 'h' && $perDay ? (int) round($perDay * 60) : null,
        'category_id' => $categoryId && category($categoryId) ? $categoryId : null,
    ];
    $parts = [];
    foreach (input()['parts'] ?? [] as $p) {
        $title = mb_substr(trim((string) ($p['title'] ?? '')), 0, 160);
        $size = (float) str_replace(',', '', (string) ($p['target'] ?? '0'));
        if ($title !== '' && $size > 0) {
            $parts[] = ['title' => $title, 'target' => $size, 'done' => min($size, (float) ($p['done'] ?? 0))];
        }
    }

    $id = in_int('id');
    if ($id) {
        $data['done'] = min($target, (float) val('SELECT done FROM goals WHERE id = ?', [$id]));
        update('goals', $id, $data);
        q('DELETE FROM goals WHERE parent_id = ?', [$id]);
    } else {
        $id = insert('goals', $data + ['start_on' => today(), 'done' => 0, 'created_at' => now()->format('Y-m-d H:i:s'), 'position' => (int) val('SELECT COALESCE(MAX(position), 0) + 1 FROM goals')]);
    }
    foreach ($parts as $i => $p) {
        insert('goals', $p + ['parent_id' => $id, 'unit' => $unit, 'category_id' => $data['category_id'], 'position' => $i,
            'status' => $p['done'] >= $p['target'] ? 'done' : 'active', 'created_at' => now()->format('Y-m-d H:i:s')]);
    }
    $blocks = in_bool('add_block') && $data['daily_minutes'] ? goal_schedule_blocks(goal($id)) : 0;
    return ['id' => $id, 'blocks' => $blocks];
}

function api_goals_log(): array
{
    $amount = in_num('amount');
    if ($amount === null || $amount <= 0) {
        fail('Enter an amount above 0.');
    }
    $id = (int) in_int('goal_id');
    goal($id) ?? fail('That goal no longer exists.');
    goal_log($id, $amount, in_date('logged_on') ?? today(), in_str('note'));
    return ['id' => $id];
}

function api_goals_schedule(): array
{
    $goal = goal((int) in_int('id')) ?? fail('That goal no longer exists.');
    return ['count' => goal_schedule_blocks($goal)];
}

function api_goals_status(): array
{
    update('goals', (int) in_int('id'), ['status' => in_enum('status', ['active', 'done', 'paused'], 'active')]);
    return [];
}

function api_goals_delete(): array
{
    delete_row('goals', (int) in_int('id'));
    return [];
}

/** Optional: ask Claude to split a goal into named parts (needs an API key in Settings). */
function api_goals_split(): array
{
    $total = in_num('target');
    if ($total === null || $total <= 0) {
        fail('Enter the total first, for example 64.');
    }
    return ['parts' => ai_split_goal(in_str('title', 160, true), $total, in_str('unit', 20) ?: 'h', max(0, min(40, (int) in_int('part_count', 0))))];
}
