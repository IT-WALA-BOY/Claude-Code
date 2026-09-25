<?php
declare(strict_types=1);

/** Command palette: searches tasks, leads, goals and Figma projects. */
function api_search_query(): array
{
    $q = in_str('q', 80);
    if (mb_strlen($q) < 2) {
        return ['results' => []];
    }
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $results = [];
    foreach (rows('SELECT id, title FROM tasks WHERE title LIKE ? OR notes LIKE ? ORDER BY status = \'done\', due_on IS NULL, due_on LIMIT 6', [$like, $like]) as $r) {
        $results[] = ['title' => $r['title'], 'kind' => 'Task', 'icon' => 'square-check-big', 'url' => url('tasks', ['view' => 'list', 'open' => $r['id']])];
    }
    foreach (rows('SELECT id, name, company FROM li_prospects WHERE name LIKE ? OR company LIKE ? LIMIT 4', [$like, $like]) as $r) {
        $results[] = ['title' => $r['name'] . ($r['company'] ? ', ' . $r['company'] : ''), 'kind' => 'Lead', 'icon' => 'megaphone', 'url' => url('linkedin')];
    }
    foreach (rows('SELECT id, title FROM goals WHERE title LIKE ? LIMIT 3', [$like]) as $r) {
        $results[] = ['title' => $r['title'], 'kind' => 'Goal', 'icon' => 'target', 'url' => url('goals', ['goal' => $r['id']])];
    }
    foreach (rows('SELECT id, name FROM figma_projects WHERE name LIKE ? OR client LIKE ? LIMIT 3', [$like, $like]) as $r) {
        $results[] = ['title' => $r['name'], 'kind' => 'Figma', 'icon' => 'figma', 'url' => url('figma')];
    }
    foreach (PAGES as $slug => $p) {
        if ($slug !== 'not-found' && stripos($p['title'], $q) !== false) {
            $results[] = ['title' => $p['title'], 'kind' => 'Page', 'icon' => 'arrow-up-right', 'url' => url($slug === 'overview' ? '' : $slug)];
        }
    }
    return ['results' => $results];
}
