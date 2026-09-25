<?php
declare(strict_types=1);

/**
 * Sample data that matches the Figma screens. Dates are relative to today on the Pacific clock,
 * so the demo always looks current. Run from install.php ("Load sample data").
 */
function seed_demo(PDO $pdo): void
{
    $tz = new DateTimeZone('America/Los_Angeles');
    $today = new DateTimeImmutable('today', $tz);
    $d = fn (int $days): string => $today->modify("$days days")->format('Y-m-d');
    $at = fn (int $days, string $time): string => $today->modify("$days days")->format('Y-m-d') . ' ' . $time;
    $now = (new DateTimeImmutable('now', $tz))->format('Y-m-d H:i:s');
    $ins = function (string $table, array $row) use ($pdo): int {
        $cols = array_keys($row);
        $pdo->prepare(sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, implode(',', $cols), implode(',', array_fill(0, count($cols), '?'))))
            ->execute(array_values($row));
        return (int) $pdo->lastInsertId();
    };
    $rate = 282;

    // Figma projects
    $projects = [];
    foreach ([
        ['Qualitas dashboard', 'Qualitas', 'client', 'upwork', 'B2B SaaS · 12 screens · revisions v3 tonight', 0, 'revisions'],
        ['Fruits Au Travail website', 'Fruits Au Travail', 'client', 'direct', 'B2B landing page · desktop done, mobile next', 3, 'active'],
        ['ScaleX pricing page', 'ScaleX', 'client', 'upwork', 'Claude code converted to Figma · cleanup', 2, 'review'],
        ['Mobile app case study', '', 'portfolio', 'self', 'New service: mobile app design · 5 screens', 6, 'active'],
        ['Portfolio site', '', 'portfolio', 'self', 'Built in Claude Code · must not look generic', 21, 'active'],
    ] as $i => [$name, $client, $kind, $source, $note, $due, $status]) {
        $projects[$name] = $ins('figma_projects', [
            'name' => $name, 'client' => $client, 'kind' => $kind, 'source' => $source, 'note' => $note,
            'figma_url' => 'https://www.figma.com/design/example', 'due_on' => $d($due), 'status' => $status,
            'last_opened_at' => $at(-$i, '21:00:00'), 'position' => $i, 'created_at' => $now,
        ]);
    }

    // Goals, with CXL broken into its 9 courses
    $goalIds = [];
    foreach ([
        ['CXL mini degree', 'h', 64, 24, -30, 30, 90, 3],
        ['Mobile app case study', 'screens', 5, 2, -14, 6, null, 4],
        ['LinkedIn posts this month', 'posts', 12, 5, -24, 5, null, 1],
        ['Portfolio site', 'pages', 8, 3, -20, 21, null, 4],
        ['New Upwork reviews this month', 'reviews', 3, 1, -24, 5, null, 2],
    ] as $i => [$title, $unit, $target, $done, $start, $due, $daily, $cat]) {
        $goalIds[$title] = $ins('goals', [
            'title' => $title, 'unit' => $unit, 'target' => $target, 'done' => $done, 'start_on' => $d($start), 'due_on' => $d($due),
            'daily_minutes' => $daily, 'rest_days' => 1, 'category_id' => $cat, 'status' => 'active', 'position' => $i, 'created_at' => $now,
        ]);
    }
    foreach ([
        ['Conversion research', 8, 8], ['Landing page optimization', 6, 6], ['Copywriting for conversions', 7, 7],
        ['Customer research', 8, 3], ['Product messaging', 6, 0], ['UX research', 8, 0],
        ['A/B testing', 9, 0], ['Google Analytics 4', 7, 0], ['Heuristic analysis', 5, 0],
    ] as $i => [$course, $hours, $done]) {
        $ins('goals', ['parent_id' => $goalIds['CXL mini degree'], 'title' => $course, 'unit' => 'h', 'target' => $hours, 'done' => $done,
            'status' => $done >= $hours ? 'done' : 'active', 'position' => $i, 'category_id' => 3, 'created_at' => $now]);
    }
    for ($i = 29; $i >= 1; $i--) {
        if ($i % 7 !== 0 && $i % 3 !== 1) {
            $ins('goal_logs', ['goal_id' => $goalIds['CXL mini degree'], 'logged_on' => $d(-$i), 'amount' => 1.5, 'note' => '']);
        }
    }

    // Tasks: [title, notes, category, priority, status, due offset, due time, checklist [text, done], project]
    $tasks = [
        ['Follow up: Jake Morrison, Morrison Family Dental Group', 'Second follow-up after the free teardown. Offer the paid audit.', 1, 'urgent', 'todo', -2, null, [['Check teardown views', 0], ['Write follow-up', 0], ['Send and log', 0]], null],
        ['Post: teardown of a neglected medical brand homepage', 'Draft ready. Add 3 screenshots.', 1, 'low', 'doing', 0, null, [['Draft', 1], ['Screenshots', 0]], null],
        ['Teardown Loom for Brightline Physio', 'Weak product cards and no reviews. 5 fixes, 4 minutes.', 1, 'moderate', 'todo', 2, null, [['Audit homepage', 1], ['Script', 0], ['Record Loom', 0], ['Send', 0]], null],
        ['Shortlist 10 B2B SaaS founders in the UK', 'Look for sites with no case studies.', 1, 'low', 'todo', 3, null, [], null],
        ['Reply to Sarah Chen, Northwind Analytics', 'She asked about audit pricing. Send the one-pager.', 1, 'moderate', 'doing', 1, null, [['Pricing one-pager', 1], ['Examples', 1], ['Reply', 1], ['Book call', 0]], null],
        ['Paid audit proposal: Qualia Health', 'Waiting on their CMO. Nudge on Friday if quiet.', 1, 'urgent', 'review', 1, null, [['Scope', 1], ['Price', 1], ['Timeline', 1], ['Send', 0]], null],
        ['Proposal: SaaS onboarding redesign for a FinOps startup', 'Use template A with the Qualitas proof. 12 Connects.', 2, 'moderate', 'doing', 0, '22:00', [['Read brief', 1], ['Write proposal', 0]], null],
        ['Update case study: Multani Threads', 'Add results and the new hero.', 2, 'low', 'todo', 5, null, [], null],
        ['Reply to invite: roofing company landing page', 'Invite from a US client with 9 hires. Reply within the hour.', 2, 'urgent', 'todo', 1, null, [['Read job', 0], ['Reply', 0]], null],
        ['CXL: Conversion research, lesson 4 of 9', 'Heuristic analysis and qualitative research. 1 h 20 m left.', 3, 'moderate', 'doing', 0, null, [['Watch lesson', 1], ['Notes', 1], ['Quiz', 0], ['Apply to a site', 0]], null],
        ['CXL: Copywriting for conversions, intro', 'Mini degree, course 3.', 3, 'low', 'todo', 4, null, [], null],
        ['Mobile app patterns: onboarding flows', 'Collect 10 references.', 3, 'low', 'todo', 2, null, [], null],
        ['Read: Refactoring UI, chapter 3', '', 3, 'low', 'later', 6, null, [], null],
        ['Send Qualitas dashboard revisions v3', 'Client: Qualitas. 6 screens left.', 4, 'urgent', 'doing', 0, '23:00', [['Tables: tighter rows', 1], ['Chart palette', 1], ['Export and send', 0]], 'Qualitas dashboard'],
        ['Fruits Au Travail: mobile breakpoints', 'Hero, pricing and FAQ at 390 and 768.', 4, 'moderate', 'todo', 3, null, [['Hero', 0], ['Pricing', 0], ['FAQ', 0]], 'Fruits Au Travail website'],
        ['Mobile app case study: 2 of 5 screens', 'Portfolio piece.', 4, 'moderate', 'doing', 6, null, [['Screen 1', 1], ['Screen 2', 1], ['Screen 3', 0], ['Screen 4', 0]], 'Mobile app case study'],
        ['Claude code to Figma: ScaleX pricing page', 'Plugin import done. Clean up auto layout and tokens.', 4, 'moderate', 'review', 2, null, [['Import', 1], ['Auto layout', 1], ['Tokens', 1], ['Handoff', 0]], 'ScaleX pricing page'],
        ['Portfolio site: About page', '', 4, 'low', 'later', 8, null, [], 'Portfolio site'],
        // Done this week
        ['Qualitas wireframes approved', 'Client signed off on all 9 wireframes.', 4, 'low', 'done', -3, null, [['Flows', 1], ['Wireframes', 1], ['Review', 1], ['Sign-off', 1]], 'Qualitas dashboard'],
        ['Posted: 3 fixes for a DTC checkout', '1,240 impressions, 2 DMs.', 1, 'low', 'done', -2, null, [['Draft', 1], ['Visuals', 1], ['Post', 1]], null],
        ['CXL: lesson 3 of 9', 'Done in 2 sessions.', 3, 'low', 'done', -1, null, [['Watch', 1], ['Notes', 1], ['Quiz', 1], ['Apply', 1]], null],
        ['Send 5 Upwork proposals', '', 2, 'moderate', 'done', -1, null, [], null],
        ['Invoice Fruits Au Travail', '', 2, 'moderate', 'done', -6, null, [], null],
    ];
    foreach ($tasks as $i => [$title, $notes, $cat, $prio, $status, $due, $time, $checks, $project]) {
        $taskId = $ins('tasks', [
            'title' => $title, 'notes' => $notes, 'category_id' => $cat, 'status' => $status, 'priority' => $prio,
            'important' => (int) ($prio !== 'low'), 'urgent' => (int) ($prio === 'urgent'),
            'start_on' => $d($due - 3), 'due_on' => $d($due), 'due_time' => $time, 'est_minutes' => [30, 60, 90][$i % 3],
            'position' => $i, 'project_id' => $project ? $projects[$project] : null,
            'completed_at' => $status === 'done' ? $at(min(0, $due), '23:00:00') : null, 'created_at' => $at(min(-1, $due - 6), '20:00:00'),
        ]);
        foreach ($checks as $j => [$text, $isDone]) {
            $ins('task_checklist', ['task_id' => $taskId, 'text' => $text, 'done' => $isDone, 'position' => $j]);
        }
    }

    // Finished tasks from earlier weeks, so the weekly trend in Analytics has history.
    $done = [9, 11, 8, 12, 10, 12, 13];
    $titles = ['Upwork proposal', 'LinkedIn follow-ups', 'CXL lesson', 'Client revisions', 'Teardown Loom', 'Case study edits', 'Portfolio section'];
    foreach ($done as $week => $count) {
        for ($i = 0; $i < $count; $i++) {
            $created = -(8 - $week) * 7 - ($i % 5);
            $completed = min(-1, $created + 2 + $i % 4);
            $ins('tasks', [
                'title' => $titles[$i % 7] . ' ' . ($i + 1), 'category_id' => 1 + $i % 4, 'status' => 'done', 'priority' => ['low', 'moderate', 'urgent'][$i % 3],
                'start_on' => $d($created), 'due_on' => $d($completed), 'position' => 100 + $i, 'est_minutes' => 60,
                'completed_at' => $at($completed, '22:00:00'), 'created_at' => $at($created, '19:00:00'),
            ]);
        }
    }

    // Schedule: tonight's blocks plus done focus blocks for the last 5 weeks (heatmap)
    foreach ([
        ['18:00', '19:00', 'LinkedIn outreach block', '12 new, 4 follow-ups', 1],
        ['19:30', '21:00', 'CXL: Conversion research, lesson 4', '1 h 30 m', 3],
        ['21:30', '23:00', 'Qualitas dashboard revisions v3', 'In progress', 4],
        ['23:30', '00:30', 'Write 3 Upwork proposals', 'About 18 Connects', 2],
    ] as [$start, $end, $title, $note, $cat]) {
        $endDay = $end < $start ? 1 : 0;
        $ins('schedule_blocks', ['starts_at' => $at(0, "$start:00"), 'ends_at' => $at($endDay, "$end:00"), 'title' => $title, 'note' => $note, 'category_id' => $cat, 'source' => 'manual']);
    }
    for ($day = 1; $day <= 6; $day++) {
        $ins('schedule_blocks', ['starts_at' => $at($day, '18:00:00'), 'ends_at' => $at($day, '19:00:00'), 'title' => 'LinkedIn outreach block', 'category_id' => 1, 'source' => 'manual']);
        $ins('schedule_blocks', ['starts_at' => $at($day, '19:30:00'), 'ends_at' => $at($day, '21:00:00'), 'title' => 'CXL study block', 'category_id' => 3, 'source' => 'goal', 'goal_id' => $goalIds['CXL mini degree']]);
    }
    $hours = [2.5, 4, 5.5, 3, 6, 4.5, 0, 3.5, 5, 6.5, 4, 5, 3, 0, 4, 6, 7.5, 5.5, 4, 2, 0, 3, 5, 4.5, 6, 3.5, 0, 0, 4, 5];
    foreach ($hours as $i => $h) {
        if ($h > 0) {
            $start = $at(-30 + $i, '18:00:00');
            $end = (new DateTimeImmutable($start))->modify('+' . (int) ($h * 60) . ' minutes')->format('Y-m-d H:i:s');
            $ins('schedule_blocks', ['starts_at' => $start, 'ends_at' => $end, 'title' => 'Focus block', 'category_id' => 1 + $i % 4, 'source' => 'manual', 'done' => 1]);
        }
    }

    // Income: past months as monthly totals, this month by week
    $monthStart = $today->modify('first day of this month');
    foreach ([3200, 4100, 3650, 5020, 4480, 5900, 6300, 7450] as $i => $amount) {
        $month = $monthStart->modify('-' . (8 - $i) . ' months');
        $ins('income', ['received_on' => $month->modify('+14 days')->format('Y-m-d'), 'client' => 'Upwork clients', 'title' => 'Monthly total', 'source' => 'upwork', 'amount' => round($amount * 0.8), 'currency' => 'USD', 'rate' => $rate, 'status' => 'paid']);
        $ins('income', ['received_on' => $month->modify('+20 days')->format('Y-m-d'), 'client' => 'Direct clients', 'title' => 'Monthly total', 'source' => 'direct', 'amount' => $amount - round($amount * 0.8), 'currency' => 'USD', 'rate' => $rate, 'status' => 'paid']);
    }
    $dayOfMonth = (int) $today->format('j');
    foreach ([
        [2, 'Qualitas', 'Milestone 2', 'upwork', 1020], [4, 'Brightline Physio', 'Homepage audit', 'direct', 200],
        [9, 'Qualitas', 'Milestone 3', 'upwork', 1180], [11, 'Multani Threads', 'Landing page', 'direct', 400],
        [15, 'ScaleX', 'Pricing page', 'upwork', 1650], [17, 'Northwind Analytics', 'Paid audit', 'direct', 500],
        [22, 'FinOps startup', 'Onboarding flows', 'upwork', 1000], [23, 'Qualia Health', 'Audit deposit', 'direct', 290],
    ] as [$dom, $client, $title, $source, $amount]) {
        $when = $monthStart->modify('+' . (min($dom, $dayOfMonth) - 1) . ' days')->format('Y-m-d');
        $ins('income', ['received_on' => $when, 'client' => $client, 'title' => $title, 'source' => $source, 'amount' => $amount, 'currency' => 'USD', 'rate' => $rate, 'status' => 'paid']);
    }
    $ins('income', ['received_on' => $d(-6), 'client' => 'Fruits Au Travail', 'title' => 'Website design, part 2', 'source' => 'direct', 'amount' => 480, 'currency' => 'USD', 'rate' => $rate, 'status' => 'pending', 'due_on' => $d(-5)]);
    $ins('income', ['received_on' => $d(-1), 'client' => 'Qualitas', 'title' => 'Milestone 4', 'source' => 'upwork', 'amount' => 520, 'currency' => 'USD', 'rate' => $rate, 'status' => 'escrow', 'due_on' => $d(6)]);

    // Expenses this month: daily costs in PKR, Connects, tools. Plus last months for trends.
    $daily = [['Groceries, weekly', 18500, 'JazzCash', 'Imtiaz Depalpur'], ['Internet', 6500, 'JazzCash', 'Fiber 50 Mbps, monthly'], ['Electricity bill', 21000, 'Bank transfer', 'LESCO'],
        ['Fuel', 12800, 'Cash', 'Motorbike'], ['Food and tea', 9200, 'Cash', ''], ['Phone top-up', 4800, 'JazzCash', '']];
    foreach ($daily as $i => [$title, $pkr, $paidWith, $note]) {
        $ins('expenses', ['spent_on' => $monthStart->modify('+' . min($i * 4, $dayOfMonth - 1) . ' days')->format('Y-m-d'), 'kind' => 'daily', 'title' => $title, 'amount' => $pkr, 'currency' => 'PKR', 'rate' => $rate, 'paid_with' => $paidWith, 'note' => $note]);
    }
    foreach ([[400, 60], [200, 30]] as $i => [$qty, $usd]) {
        $ins('expenses', ['spent_on' => $monthStart->modify('+' . min(3 + $i * 12, $dayOfMonth - 1) . ' days')->format('Y-m-d'), 'kind' => 'connects', 'title' => "$qty Upwork Connects", 'amount' => $usd, 'currency' => 'USD', 'rate' => $rate, 'quantity' => $qty, 'paid_with' => 'Upwork balance']);
    }
    foreach ([['Figma Professional', 16, 3], ['ChatGPT Plus', 20, 8], ['Notion Plus', 10, 12], ['Framer Mini', 18, 26]] as [$title, $usd, $renew]) {
        $ins('expenses', ['spent_on' => $monthStart->format('Y-m-d'), 'kind' => 'tools', 'title' => $title, 'amount' => $usd, 'currency' => 'USD', 'rate' => $rate, 'renews_on' => $d($renew), 'paid_with' => 'Card', 'note' => 'Monthly plan']);
    }
    foreach ([1, 2, 3] as $back) {
        $m = $monthStart->modify("-$back months");
        $ins('expenses', ['spent_on' => $m->modify('+5 days')->format('Y-m-d'), 'kind' => 'daily', 'title' => 'Monthly living costs', 'amount' => 70000 + $back * 1500, 'currency' => 'PKR', 'rate' => $rate]);
        $ins('expenses', ['spent_on' => $m->modify('+6 days')->format('Y-m-d'), 'kind' => 'connects', 'title' => '500 Connects', 'amount' => 75, 'currency' => 'USD', 'rate' => $rate, 'quantity' => 500]);
        $ins('expenses', ['spent_on' => $m->format('Y-m-d'), 'kind' => 'tools', 'title' => 'Tools', 'amount' => 64, 'currency' => 'USD', 'rate' => $rate]);
    }

    // Buy list (Eisenhower)
    foreach ([
        ['Laptop charger 65W', 'Current one overheats', 9500, 1, 1], ['UPS battery', 'Load shedding backup', 14000, 1, 1],
        ['Ergonomic chair', 'Back pain after long nights', 45000, 1, 0], ['27 inch 4K monitor', 'For Figma work', 68000, 1, 0],
        ['Phone screen protector', 'Cracked', 1200, 0, 1], ['Mechanical keyboard', 'Nice to have', 18000, 0, 0],
    ] as $i => [$title, $note, $cost, $imp, $urg]) {
        $ins('buy_items', ['title' => $title, 'note' => $note, 'est_cost' => $cost, 'currency' => 'PKR', 'important' => $imp, 'urgent' => $urg, 'position' => $i]);
    }

    // LinkedIn pipeline
    foreach ([
        ['Jake Morrison', 'Morrison Family Dental Group', 'US', 'Medical', 'Site from 2015, no online booking', 'teardown', 1500, -2],
        ['Sarah Chen', 'Northwind Analytics', 'US', 'SaaS', 'Pricing page hides the plans', 'replied', 2400, 1],
        ['Priya Nair', 'Qualia Health', 'UK', 'Medical', 'Slow homepage, stock photos', 'audit', 900, 1],
        ['Tom Ashford', 'Ashford Roofing', 'US', 'Service', 'No reviews above the fold', 'teardown', 1200, -1],
        ['Emily Hart', 'Brightline Physio', 'UK', 'Medical', 'Weak product cards, no reviews', 'connected', 1500, 0],
        ['Daniel Brooks', 'Ledgerly', 'US', 'SaaS', 'Onboarding drops at step 2', 'pitch', 4800, 3],
        ['Mia Collins', 'Collins Legal', 'UK', 'Service', 'Contact form is 11 fields', 'replied', 1800, 0],
        ['Oliver Grant', 'Stackwise', 'UK', 'B2B', 'No case studies', 'shortlisted', 2000, 2],
        ['Ava Patel', 'Clearpath HR', 'US', 'SaaS', 'Blog-first homepage', 'shortlisted', 2200, 3],
        ['Noah Kim', 'Kim Dental Studio', 'US', 'Medical', 'Not mobile friendly', 'shortlisted', 1200, 4],
        ['Liam Walker', 'Freightline', 'US', 'B2B', 'No clear call to action', 'connected', 2600, 2],
        ['Grace Lee', 'Lumen Clinics', 'US', 'Medical', 'Redesign shipped', 'won', 3200, null],
    ] as $i => [$name, $company, $market, $industry, $why, $stage, $value, $follow]) {
        $ins('li_prospects', ['name' => $name, 'company' => $company, 'market' => $market, 'industry' => $industry, 'why_neglected' => $why,
            'stage' => $stage, 'value' => $value, 'next_followup' => $follow === null ? null : $d($follow), 'position' => $i, 'updated_at' => $at(-$i, '20:00:00')]);
    }
    foreach ([
        ['3 fixes for a DTC checkout', 'posted', -2, 1240, 38], ['Teardown: neglected medical brand homepage', 'draft', 0, 0, 0],
        ['Why your pricing page loses SaaS trials', 'scheduled', 2, 0, 0], ['Before and after: dental clinic hero', 'posted', -5, 860, 24],
        ['The 5 second test for service sites', 'posted', -9, 2100, 61],
    ] as [$topic, $status, $day, $imp, $react]) {
        $ins('li_posts', ['topic' => $topic, 'status' => $status, 'post_on' => $d($day), 'impressions' => $imp, 'reactions' => $react]);
    }
    foreach (['reachouts' => 8, 'followups' => 2, 'teardowns' => 1] as $metric => $count) {
        $ins('li_daily', ['day' => $d(0), 'metric' => $metric, 'count' => $count]);
    }
    for ($back = 1; $back <= 13; $back++) {
        foreach (['reachouts' => 10, 'followups' => 4, 'teardowns' => 1] as $metric => $base) {
            $ins('li_daily', ['day' => $d(-$back), 'metric' => $metric, 'count' => max(0, $base - ($back * 3) % 5)]);
        }
    }

    $pdo->prepare("UPDATE settings SET v = '12' WHERE k = 'connects_left'")->execute();
}
