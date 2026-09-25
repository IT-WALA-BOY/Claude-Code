<?php
declare(strict_types=1);

function api_tasks_save(): array
{
    $categoryId = in_int('category_id');
    $projectId = in_int('project_id');
    $data = [
        'title' => in_str('title', 200, true),
        'notes' => in_str('notes', 5000),
        'category_id' => $categoryId && category($categoryId) ? $categoryId : null,
        'status' => in_enum('status', array_keys(TASK_STATUSES), 'todo'),
        'priority' => in_enum('priority', array_keys(TASK_PRIORITIES), 'moderate'),
        'important' => in_bool('important'),
        'urgent' => in_bool('urgent'),
        'start_on' => in_date('start_on'),
        'due_on' => in_date('due_on'),
        'due_time' => in_time('due_time'),
        'est_minutes' => in_int('est_minutes'),
        'project_id' => $projectId && val('SELECT id FROM figma_projects WHERE id = ?', [$projectId]) ? $projectId : null,
    ];
    if ($data['start_on'] && $data['due_on'] && $data['start_on'] > $data['due_on']) {
        fail('The start date is after the due date.');
    }
    $checklist = [];
    foreach (input()['checklist'] ?? [] as $item) {
        $text = mb_substr(trim((string) ($item['text'] ?? '')), 0, 200);
        if ($text !== '') {
            $checklist[] = ['text' => $text, 'done' => empty($item['done']) ? 0 : 1];
        }
    }
    return ['id' => task_save($data, $checklist, in_int('id'))];
}

function api_tasks_delete(): array
{
    delete_row('tasks', (int) in_int('id'));
    return [];
}

/** Drag on the board: new column plus the full order of that column. */
function api_tasks_move(): array
{
    $status = in_enum('status', array_keys(TASK_STATUSES)) ?? fail('Unknown column.');
    task_move((int) in_int('id'), $status, in_ids('order'));
    return [];
}

function api_tasks_done(): array
{
    task_set_done((int) in_int('id'), (bool) in_bool('done'));
    return [];
}

function api_tasks_check(): array
{
    checklist_toggle((int) in_int('id'), (int) in_int('index'), (bool) in_bool('done'));
    $task = row('SELECT (SELECT COUNT(*) FROM task_checklist WHERE task_id = ?) AS total, (SELECT COUNT(*) FROM task_checklist WHERE task_id = ? AND done = 1) AS done', [in_int('id'), in_int('id')]);
    return ['total' => (int) $task['total'], 'done' => (int) $task['done']];
}

/** Drag on the timeline: new start and due dates. */
function api_tasks_dates(): array
{
    $start = in_date('start_on');
    $due = in_date('due_on');
    if (!$start || !$due || $start > $due) {
        fail('Pick a valid date range.');
    }
    task_set_dates((int) in_int('id'), $start, $due);
    return [];
}
