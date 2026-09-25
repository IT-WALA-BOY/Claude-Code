<?php
declare(strict_types=1);

const TASK_STATUSES = ['todo' => 'To do', 'doing' => 'In progress', 'review' => 'Review', 'done' => 'Done', 'later' => 'Later'];
const TASK_PRIORITIES = ['urgent' => 'Urgent', 'moderate' => 'Moderate', 'low' => 'Low'];
const CATEGORY_COLORS = ['blue', 'green', 'purple', 'pink', 'orange', 'yellow'];

function categories(): array
{
    static $all = null;
    return $all ??= rows('SELECT id, name, color, icon FROM categories ORDER BY position, id');
}

function category(?int $id): ?array
{
    foreach (categories() as $c) {
        if ((int) $c['id'] === $id) {
            return $c;
        }
    }
    return null;
}

/** Monday of the current week on the app clock. */
function week_start(): string
{
    return now()->modify('monday this week')->format('Y-m-d');
}

/**
 * Tasks with their category and checklist counts.
 * Filters: status (string|array), category (id), open (bool), done_since (date), due_until (date), project (id).
 */
function tasks_list(array $f = []): array
{
    $where = ['1 = 1'];
    $p = [];
    if (!empty($f['open'])) {
        $where[] = "t.status <> 'done'";
    }
    if (!empty($f['status'])) {
        $statuses = (array) $f['status'];
        $where[] = 't.status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
        array_push($p, ...$statuses);
    }
    if (!empty($f['category'])) {
        $where[] = 't.category_id = ?';
        $p[] = (int) $f['category'];
    }
    if (!empty($f['project'])) {
        $where[] = 't.project_id = ?';
        $p[] = (int) $f['project'];
    }
    if (!empty($f['due_until'])) {
        $where[] = 't.due_on IS NOT NULL AND t.due_on <= ?';
        $p[] = $f['due_until'];
    }
    if (!empty($f['with_done_since'])) {
        $where[] = "(t.status <> 'done' OR t.completed_at >= ?)";
        $p[] = $f['with_done_since'] . ' 00:00:00';
    }
    $order = $f['order'] ?? 't.position, t.id';

    $tasks = rows(
        'SELECT t.*, c.name AS cat_name, c.color AS cat_color, c.icon AS cat_icon,
                (SELECT COUNT(*) FROM task_checklist k WHERE k.task_id = t.id) AS checks_total,
                (SELECT COUNT(*) FROM task_checklist k WHERE k.task_id = t.id AND k.done = 1) AS checks_done
         FROM tasks t LEFT JOIN categories c ON c.id = t.category_id
         WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $order,
        $p
    );
    return array_map('task_decorate', $tasks);
}

/** Adds computed fields used by every view: progress, due label, overdue flag. */
function task_decorate(array $t): array
{
    $total = (int) $t['checks_total'];
    $t['progress'] = $t['status'] === 'done' ? 100 : ($total ? pct((float) $t['checks_done'], $total) : 0);
    [$t['due_text'], $t['due_tone']] = due_label($t['due_on'], $t['due_time'], $t['status'] === 'done');
    $t['overdue'] = $t['status'] !== 'done' && $t['due_on'] && $t['due_on'] < today();
    return $t;
}

function task_checklists(array $taskIds): array
{
    if (!$taskIds) {
        return [];
    }
    $in = implode(',', array_fill(0, count($taskIds), '?'));
    $map = [];
    foreach (rows("SELECT id, task_id, text, done FROM task_checklist WHERE task_id IN ($in) ORDER BY position, id", $taskIds) as $item) {
        $map[(int) $item['task_id']][] = $item;
    }
    return $map;
}

/** Data the task dialog needs to edit a task. Printed into a data attribute. */
function task_json(array $t, array $checklist = []): string
{
    return json_encode([
        'id' => (int) $t['id'],
        'title' => $t['title'],
        'notes' => (string) $t['notes'],
        'category_id' => $t['category_id'] ? (int) $t['category_id'] : '',
        'status' => $t['status'],
        'priority' => $t['priority'],
        'important' => (bool) $t['important'],
        'urgent' => (bool) $t['urgent'],
        'start_on' => (string) $t['start_on'],
        'due_on' => (string) $t['due_on'],
        'due_time' => $t['due_time'] ? substr($t['due_time'], 0, 5) : '',
        'est_minutes' => $t['est_minutes'] ?? '',
        'project_id' => $t['project_id'] ? (int) $t['project_id'] : '',
        'checklist' => array_map(fn ($c) => ['text' => $c['text'], 'done' => (bool) $c['done']], $checklist),
    ], JSON_UNESCAPED_UNICODE);
}

function task_counts(): array
{
    $row = row(
        "SELECT SUM(status <> 'done') AS open,
                SUM(status <> 'done' AND due_on = ?) AS today,
                SUM(status <> 'done' AND due_on < ?) AS overdue,
                SUM(status = 'done' AND completed_at >= ?) AS done_week
         FROM tasks",
        [today(), today(), week_start() . ' 00:00:00']
    );
    return array_map('intval', $row ?? []);
}

/** Change in open tasks over the last 7 days: tasks added minus tasks finished. */
function task_open_change(): int
{
    $since = add_days(today(), -7) . ' 00:00:00';
    $created = (int) val('SELECT COUNT(*) FROM tasks WHERE created_at >= ?', [$since]);
    $completed = (int) val("SELECT COUNT(*) FROM tasks WHERE status = 'done' AND completed_at >= ?", [$since]);
    return $created - $completed;
}

function open_counts_by_category(): array
{
    return array_column(
        rows("SELECT category_id, COUNT(*) AS n FROM tasks WHERE status <> 'done' GROUP BY category_id"),
        'n',
        'category_id'
    );
}

function task_save(array $data, array $checklist, ?int $id): int
{
    if ($id) {
        $before = row('SELECT status FROM tasks WHERE id = ?', [$id]) ?? fail('That task no longer exists.');
        if ($data['status'] === 'done' && $before['status'] !== 'done') {
            $data['completed_at'] = now()->format('Y-m-d H:i:s');
        } elseif ($data['status'] !== 'done') {
            $data['completed_at'] = null;
        }
        update('tasks', $id, $data);
    } else {
        $data['position'] = (int) val('SELECT COALESCE(MIN(position), 0) - 1 FROM tasks WHERE status = ?', [$data['status']]);
        $data['created_at'] = now()->format('Y-m-d H:i:s');
        $data['completed_at'] = $data['status'] === 'done' ? $data['created_at'] : null;
        $id = insert('tasks', $data);
    }
    q('DELETE FROM task_checklist WHERE task_id = ?', [$id]);
    foreach ($checklist as $i => $item) {
        insert('task_checklist', ['task_id' => $id, 'text' => $item['text'], 'done' => $item['done'], 'position' => $i]);
    }
    return $id;
}

/** Moves a task to a column and saves the new order of that column. */
function task_move(int $id, string $status, array $orderedIds): void
{
    $current = val('SELECT status FROM tasks WHERE id = ?', [$id]) ?? fail('That task no longer exists.');
    $data = ['status' => $status];
    if ($status === 'done' && $current !== 'done') {
        $data['completed_at'] = now()->format('Y-m-d H:i:s');
    } elseif ($status !== 'done') {
        $data['completed_at'] = null;
    }
    update('tasks', $id, $data);
    reorder('tasks', $orderedIds);
}

function task_set_done(int $id, bool $done): void
{
    task_move($id, $done ? 'done' : 'todo', []);
}

function task_set_dates(int $id, ?string $start, ?string $due): void
{
    update('tasks', $id, ['start_on' => $start, 'due_on' => $due]);
}

function checklist_toggle(int $taskId, int $index, bool $done): void
{
    $ids = array_column(rows('SELECT id FROM task_checklist WHERE task_id = ? ORDER BY position, id', [$taskId]), 'id');
    if (!isset($ids[$index])) {
        fail('That checklist item no longer exists.');
    }
    update('task_checklist', (int) $ids[$index], ['done' => (int) $done]);
}
