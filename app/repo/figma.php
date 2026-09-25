<?php
declare(strict_types=1);

const PROJECT_STATUSES = ['active' => 'In progress', 'revisions' => 'In revisions', 'review' => 'In review', 'paused' => 'Paused', 'done' => 'Done'];
const PROJECT_SOURCES = ['upwork' => ['Upwork', 'green'], 'direct' => ['Direct', 'blue'], 'self' => ['Portfolio', 'purple']];

/** Projects with task progress: tasks linked to each project, done and total. */
function figma_projects(): array
{
    return rows(
        "SELECT p.*,
                (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) AS tasks_total,
                (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.status = 'done') AS tasks_done
         FROM figma_projects p ORDER BY p.status = 'done', p.due_on IS NULL, p.due_on, p.position"
    );
}

/** Projects for the task form. Done ones stay listed (last), so editing a task never drops its project. */
function figma_project_options(): array
{
    return rows("SELECT id, name, status FROM figma_projects ORDER BY status = 'done', name");
}

/** Status label and tone. An open project past its due date reads Behind. */
function project_status(array $p): array
{
    if ($p['status'] !== 'done' && $p['due_on'] && $p['due_on'] < today()) {
        return ['Behind', 'negative'];
    }
    return [PROJECT_STATUSES[$p['status']], match ($p['status']) {
        'revisions' => 'warning', 'review' => 'warning', 'done' => 'positive', default => 'muted',
    }];
}

function ago(?string $datetime): string
{
    if (!$datetime) {
        return 'Not opened yet';
    }
    $minutes = (int) ((time() - strtotime($datetime)) / 60);
    return match (true) {
        $minutes < 1 => 'Opened just now',
        $minutes < 60 => 'Opened ' . plural($minutes, 'min') . ' ago',
        $minutes < 1440 => 'Opened ' . plural(intdiv($minutes, 60), 'hour') . ' ago',
        $minutes < 2880 => 'Opened yesterday',
        default => 'Opened ' . plural(intdiv($minutes, 1440), 'day') . ' ago',
    };
}
