<?php
declare(strict_types=1);

const LI_STAGES = [
    'shortlisted' => 'Shortlisted',
    'connected' => 'Connected',
    'teardown' => 'Teardown sent',
    'replied' => 'Replied',
    'audit' => 'Paid audit',
    'pitch' => 'Redesign pitch',
    'won' => 'Won',
    'lost' => 'Lost',
];
const LI_STAGE_INFO = [
    'shortlisted' => ['list-todo', 'Looks neglected, no agency'],
    'connected' => ['user-plus', 'Accepted, warm up'],
    'teardown' => ['scan-search', 'Loom and fixes sent'],
    'replied' => ['message-circle', 'Talking'],
    'audit' => ['file-text', 'Paid audit proposal out'],
    'pitch' => ['presentation', 'Redesign offer out'],
    'won' => ['trophy', 'Signed'],
    'lost' => ['circle-x', 'Not now'],
];
/** Counted by hand on the LinkedIn page. Posts are counted from the post planner. */
const LI_METRICS = ['reachouts' => 'New reach-outs', 'followups' => 'Follow-ups sent', 'teardowns' => 'Teardowns sent'];
const LI_METRIC_ICONS = ['reachouts' => 'user-plus', 'followups' => 'send', 'teardowns' => 'scan-search', 'posts' => 'megaphone'];
const LI_MARKETS = ['US', 'UK', 'Other'];
const LI_FOLLOWUP_DAYS = 3;

function li_prospects(): array
{
    return rows('SELECT * FROM li_prospects ORDER BY position, id');
}

function li_counts(): array
{
    $row = row(
        "SELECT SUM(stage NOT IN ('won', 'lost')) AS pipeline,
                SUM(stage NOT IN ('won', 'lost') AND next_followup <= ?) AS due,
                SUM(stage NOT IN ('won', 'lost') AND next_followup < ?) AS overdue,
                SUM(stage NOT IN ('won', 'lost') AND next_followup = ?) AS due_today
         FROM li_prospects",
        [today(), today(), today()]
    );
    return array_map('intval', $row ?? []);
}

function li_targets(): array
{
    $targets = json_decode(setting('li_targets', '{}'), true) ?: [];
    return array_map('intval', $targets + array_fill_keys([...array_keys(LI_METRICS), 'posts'], 0));
}

function li_today(): array
{
    $counts = array_column(rows('SELECT metric, count FROM li_daily WHERE day = ?', [today()]), 'count', 'metric');
    $out = [];
    foreach (LI_METRICS as $key => $_) {
        $out[$key] = (int) ($counts[$key] ?? 0);
    }
    $out['posts'] = (int) val("SELECT COUNT(*) FROM li_posts WHERE status = 'posted' AND post_on = ?", [today()]);
    return $out;
}

function li_posts(): array
{
    return rows("SELECT * FROM li_posts ORDER BY status = 'posted', post_on DESC, id DESC");
}

/** How many leads reached each stage (a lead in Replied also passed Shortlisted, Connected and Teardown). */
function li_funnel(): array
{
    $order = array_keys(array_slice(LI_STAGES, 0, 7, true));
    $counts = array_fill_keys($order, 0);
    foreach (rows("SELECT stage FROM li_prospects WHERE stage <> 'lost'") as $r) {
        $idx = array_search($r['stage'], $order, true);
        foreach (array_slice($order, 0, $idx + 1) as $stage) {
            $counts[$stage]++;
        }
    }
    return $counts;
}

/** Stage number out of 7, e.g. 3 for Teardown sent. */
function li_step(string $stage): int
{
    return (int) array_search($stage, array_keys(LI_STAGES), true) + 1;
}

/** Daily counts for the last $days days: [date => [metric => count]]. */
function li_history(int $days = 14): array
{
    $out = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $out[add_days(today(), -$i)] = array_fill_keys(array_keys(LI_METRICS), 0);
    }
    foreach (rows('SELECT day, metric, count FROM li_daily WHERE day > ?', [add_days(today(), -$days)]) as $r) {
        $out[$r['day']][$r['metric']] = (int) $r['count'];
    }
    return $out;
}

function li_posts_between(string $from, string $to): array
{
    return rows('SELECT * FROM li_posts WHERE post_on BETWEEN ? AND ? ORDER BY post_on, id', [$from, $to]);
}

function li_prospect_json(array $p): string
{
    return json_encode([
        'id' => (int) $p['id'], 'name' => $p['name'], 'company' => $p['company'], 'market' => $p['market'],
        'industry' => $p['industry'], 'why_neglected' => $p['why_neglected'], 'stage' => $p['stage'],
        'value' => (float) $p['value'] ?: '', 'next_followup' => (string) $p['next_followup'], 'profile_url' => $p['profile_url'],
    ], JSON_UNESCAPED_UNICODE);
}
