<?php
declare(strict_types=1);

/** Sidebar counts in one round trip. */
function nav_counts(): array
{
    $row = row(
        "SELECT
            (SELECT COUNT(*) FROM tasks WHERE status <> 'done') AS tasks,
            (SELECT COUNT(*) FROM goals WHERE parent_id IS NULL AND status = 'active') AS goals,
            (SELECT COUNT(*) FROM figma_projects WHERE status IN ('active', 'review')) AS figma,
            (SELECT COUNT(*) FROM buy_items WHERE bought_at IS NULL) AS buy,
            (SELECT COUNT(*) FROM li_prospects WHERE next_followup <= ? AND stage NOT IN ('won', 'lost')) AS li_due",
        [today()]
    );
    return array_map('intval', $row ?? []);
}

/**
 * Things that are late or about to be, for the bell menu and the Overview.
 * Each item: icon, tone, title, meta, url, action.
 */
function attention_items(): array
{
    static $items = null;
    if ($items !== null) {
        return $items;
    }
    $items = [];

    foreach (rows(
        "SELECT id, name, company, next_followup FROM li_prospects
         WHERE next_followup < ? AND stage NOT IN ('won', 'lost') ORDER BY next_followup LIMIT 3",
        [today()]
    ) as $p) {
        $late = days_between($p['next_followup'], today());
        $items[] = [
            'icon' => 'circle-alert', 'tone' => 'negative',
            'title' => 'Follow up: ' . $p['name'],
            'meta' => 'Overdue by ' . plural($late, 'day') . ' · LinkedIn',
            'url' => url('linkedin'), 'action' => 'Follow up',
        ];
    }

    foreach (rows(
        "SELECT t.id, t.title, t.due_on, t.due_time, c.name AS cat FROM tasks t LEFT JOIN categories c ON c.id = t.category_id
         WHERE t.status <> 'done' AND t.priority = 'urgent' AND t.due_on = ? ORDER BY t.due_time IS NULL, t.due_time LIMIT 2",
        [today()]
    ) as $t) {
        $meta = 'Due today';
        if ($t['due_time']) {
            $mins = (int) ((strtotime($t['due_on'] . ' ' . $t['due_time']) - time()) / 60);
            $meta = $mins > 0 ? 'Due in ' . ($mins >= 60 ? plural(intdiv($mins, 60), 'hour') : plural($mins, 'minute')) : 'Due now';
        }
        $items[] = [
            'icon' => 'clock', 'tone' => 'warning',
            'title' => $t['title'],
            'meta' => $meta . ($t['cat'] ? ' · ' . $t['cat'] : ''),
            'url' => url('tasks'), 'action' => 'Open',
        ];
    }

    $connects = (int) setting('connects_left', '0');
    if ($connects > 0 && $connects < 20) {
        $items[] = [
            'icon' => 'zap', 'tone' => 'muted',
            'title' => 'Upwork Connects running low',
            'meta' => $connects . ' left · about ' . plural(max(1, intdiv($connects, 6)), 'proposal'),
            'url' => url('expenses'), 'action' => 'Buy',
        ];
    }

    foreach (rows(
        "SELECT id, client, amount, currency, rate, source, received_on FROM income
         WHERE status = 'pending' AND due_on < ? ORDER BY due_on LIMIT 2",
        [today()]
    ) as $inv) {
        $items[] = [
            'icon' => 'clock', 'tone' => 'warning',
            'title' => 'Invoice unpaid: ' . $inv['client'],
            'meta' => usd(to_usd($inv['amount'], $inv['currency'], $inv['rate'])) . ' · sent ' . plural(days_between($inv['received_on'], today()), 'day') . ' ago · ' . ucfirst($inv['source']),
            'url' => url('income'), 'action' => 'Remind',
        ];
    }
    return $items;
}
