<?php
declare(strict_types=1);

function api_settings_availability(): array
{
    save_work_window();
    return [];
}

function save_work_window(): void
{
    $start = in_time('work_start') ?? fail('Pick a start time.');
    $end = in_time('work_end') ?? fail('Pick an end time.');
    if ($start === $end) {
        fail('Start and end can not be the same.');
    }
    save_setting('work_start', $start);
    save_setting('work_end', $end);
}

function api_settings_save(): array
{
    $zones = DateTimeZone::listIdentifiers();
    $tz = in_str('timezone', 64);
    $second = in_str('second_timezone', 64);
    if (!in_array($tz, $zones, true) || !in_array($second, $zones, true)) {
        fail('Pick a time zone from the list.');
    }
    $rate = in_num('pkr_rate');
    $goal = in_num('income_goal');
    if (!$rate || $rate <= 0 || !$goal || $goal <= 0) {
        fail('The exchange rate and income goal must be above 0.');
    }
    save_setting('timezone', $tz);
    save_setting('second_timezone', $second);
    save_setting('pkr_rate', (string) round($rate, 2));
    save_setting('income_goal', (string) round($goal));
    save_setting('greeting_name', in_str('greeting_name', 40));
    save_work_window();

    $targets = [];
    foreach ([...array_keys(LI_METRICS), 'posts'] as $m) {
        $targets[$m] = max(0, min(100, (int) in_int('li_' . $m, 0)));
    }
    save_setting('li_targets', json_encode($targets));

    $key = in_str('anthropic_key', 200);
    if (in_bool('remove_key')) {
        save_setting('anthropic_key', '');
    } elseif ($key !== '') {
        if (!str_starts_with($key, 'sk-ant-')) {
            fail('That does not look like an Anthropic key. It starts with sk-ant-.');
        }
        save_setting('anthropic_key', $key);
    }
    return [];
}

/** Replaces the category list. Rows without an id are new; missing ids are deleted. */
function api_settings_categories(): array
{
    $keep = [];
    foreach (array_values(input()['categories'] ?? []) as $i => $c) {
        $name = mb_substr(trim((string) ($c['name'] ?? '')), 0, 60);
        if ($name === '') {
            continue;
        }
        $data = [
            'name' => $name,
            'color' => in_array($c['color'] ?? '', CATEGORY_COLORS, true) ? $c['color'] : 'blue',
            'icon' => preg_match('/^[a-z0-9-]{1,40}$/', (string) ($c['icon'] ?? '')) ? $c['icon'] : 'tag',
            'position' => $i,
        ];
        $id = (int) ($c['id'] ?? 0);
        if ($id && category($id)) {
            update('categories', $id, $data);
            $keep[] = $id;
        } else {
            $keep[] = insert('categories', $data);
        }
    }
    if (!$keep) {
        fail('Keep at least one category.');
    }
    q('DELETE FROM categories WHERE id NOT IN (' . implode(',', array_fill(0, count($keep), '?')) . ')', $keep);
    return [];
}

function api_settings_password(): array
{
    $hash = (string) val('SELECT password_hash FROM admin WHERE id = 1');
    if (!password_verify((string) (input()['current'] ?? ''), $hash)) {
        fail('The current password is wrong.');
    }
    $new = (string) (input()['new'] ?? '');
    if (strlen($new) < 10) {
        fail('The new password needs at least 10 characters.');
    }
    if ($new !== (string) (input()['repeat'] ?? '')) {
        fail('The two new passwords do not match.');
    }
    update('admin', 1, ['password_hash' => password_hash($new, PASSWORD_DEFAULT)]);
    session_regenerate_id(true);
    return [];
}
