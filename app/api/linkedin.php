<?php
declare(strict_types=1);

function api_linkedin_save(): array
{
    $url = in_str('profile_url', 500);
    if ($url !== '' && !preg_match('#^https?://#i', $url)) {
        fail('The profile link must start with https://');
    }
    $data = [
        'name' => in_str('name', 120, true),
        'company' => in_str('company', 160),
        'market' => in_enum('market', LI_MARKETS, 'US'),
        'industry' => in_str('industry', 60),
        'why_neglected' => in_str('why_neglected'),
        'stage' => in_enum('stage', array_keys(LI_STAGES), 'shortlisted'),
        'value' => max(0, (float) in_num('value', 0)),
        'next_followup' => in_date('next_followup'),
        'profile_url' => $url,
        'updated_at' => now()->format('Y-m-d H:i:s'),
    ];
    $id = in_int('id');
    if ($id) {
        update('li_prospects', $id, $data);
        return ['id' => $id];
    }
    return ['id' => insert('li_prospects', $data + ['position' => (int) val('SELECT COALESCE(MIN(position), 0) - 1 FROM li_prospects')])];
}

function api_linkedin_delete(): array
{
    delete_row('li_prospects', (int) in_int('id'));
    return [];
}

/** Drag in the pipeline: new stage plus the order of that column. */
function api_linkedin_move(): array
{
    $stage = in_enum('stage', array_keys(LI_STAGES)) ?? fail('Unknown stage.');
    $id = (int) in_int('id');
    $data = ['stage' => $stage, 'updated_at' => now()->format('Y-m-d H:i:s')];
    if (in_array($stage, ['won', 'lost'], true)) {
        $data['next_followup'] = null;
    }
    update('li_prospects', $id, $data);
    reorder('li_prospects', in_ids('order'));
    return [];
}

/** Follow-up sent: count it for today and set the next one. */
function api_linkedin_followed(): array
{
    update('li_prospects', (int) in_int('id'), ['next_followup' => add_days(today(), LI_FOLLOWUP_DAYS), 'updated_at' => now()->format('Y-m-d H:i:s')]);
    li_bump('followups', 1);
    return ['next' => fmt_day(add_days(today(), LI_FOLLOWUP_DAYS))];
}

/** +1 or -1 on one of today's counters. */
function api_linkedin_count(): array
{
    $metric = in_enum('metric', array_keys(LI_METRICS)) ?? fail('Unknown counter.');
    return ['count' => li_bump($metric, in_int('step') === -1 ? -1 : 1)];
}

function li_bump(string $metric, int $step): int
{
    q('INSERT INTO li_daily (day, metric, count) VALUES (?, ?, GREATEST(0, ?)) ON DUPLICATE KEY UPDATE count = GREATEST(0, count + ?)', [today(), $metric, $step, $step]);
    return (int) val('SELECT count FROM li_daily WHERE day = ? AND metric = ?', [today(), $metric]);
}

function api_linkedin_targets(): array
{
    $targets = [];
    foreach ([...array_keys(LI_METRICS), 'posts'] as $m) {
        $targets[$m] = max(0, min(100, (int) in_int($m, 0)));
    }
    save_setting('li_targets', json_encode($targets));
    return [];
}

function api_linkedin_post(): array
{
    $url = in_str('url', 500);
    if ($url !== '' && !preg_match('#^https?://#i', $url)) {
        fail('The post link must start with https://');
    }
    $data = [
        'topic' => in_str('topic', 200, true),
        'status' => in_enum('status', ['draft', 'scheduled', 'posted'], 'draft'),
        'post_on' => in_date('post_on'),
        'url' => $url,
        'impressions' => max(0, (int) in_int('impressions', 0)),
        'reactions' => max(0, (int) in_int('reactions', 0)),
    ];
    $id = in_int('id');
    if ($id) {
        update('li_posts', $id, $data);
        return ['id' => $id];
    }
    return ['id' => insert('li_posts', $data)];
}

function api_linkedin_post_delete(): array
{
    delete_row('li_posts', (int) in_int('id'));
    return [];
}
