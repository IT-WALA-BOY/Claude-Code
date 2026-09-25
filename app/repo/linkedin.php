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
const LI_METRICS = ['reachouts' => 'Reach-outs', 'followups' => 'Follow-ups', 'comments' => 'Comments', 'teardowns' => 'Teardowns'];

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
    return array_map('intval', $targets + array_fill_keys(array_keys(LI_METRICS), 0));
}

function li_today(): array
{
    $counts = array_column(rows('SELECT metric, count FROM li_daily WHERE day = ?', [today()]), 'count', 'metric');
    $out = [];
    foreach (LI_METRICS as $key => $_) {
        $out[$key] = (int) ($counts[$key] ?? 0);
    }
    return $out;
}

function li_posts(): array
{
    return rows("SELECT * FROM li_posts ORDER BY status = 'posted', post_on DESC, id DESC");
}
