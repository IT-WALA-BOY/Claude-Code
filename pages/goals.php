<?php
declare(strict_types=1);

$showDone = ($_GET['show'] ?? '') === 'done';
$all = goals_list(false);
$active = array_values(array_filter($all, fn ($g) => $g['status'] !== 'done'));
$done = array_values(array_filter($all, fn ($g) => $g['status'] === 'done'));
$list = $showDone ? $done : $active;
$selectedId = (int) ($_GET['goal'] ?? ($list[0]['id'] ?? 0));
$goal = $selectedId ? goal($selectedId) : null;
$parts = $goal ? goal_parts((int) $goal['id']) : [];
$partDates = $goal ? goal_part_dates($goal, $parts) : [];
$onPace = count(array_filter($active, fn ($g) => $g['pace'] === 'On pace'));
$isHours = $goal && $goal['unit'] === 'h';
$fmtAmount = fn (float $n, string $unit) => $unit === 'h' ? fmt_hours($n) : num($n) . ' ' . $unit;
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome">Long-term goals</h2>
    <p><?= count($active) ?> active · <?= $onPace ?> on pace · <?= count($active) - $onPace ?> behind · big goals broken into daily steps</p>
  </div>
  <button class="btn btn-primary" type="button" data-wizard><?= icon('plus', 16) ?>New goal</button>
</div>

<div class="goals">
  <section class="card">
    <?= card_head($showDone ? 'circle-check-big' : 'target', $showDone ? 'Completed goals' : 'Active goals', '<span class="meta">Sort: deadline</span>', count($list)) ?>
    <div class="panel goal-cards">
      <?php if (!$list): ?>
        <?= empty_state('target', $showDone ? 'Nothing finished yet' : 'No active goals', $showDone ? 'Finished goals show up here.' : 'Break a big goal into daily steps.', $showDone ? '' : '<button class="btn btn-sm" type="button" data-wizard>New goal</button>') ?>
      <?php endif ?>
      <?php foreach ($list as $g): ?>
        <a class="goal-card c-<?= e($g['cat_color'] ?: 'gray') ?><?= (int) $g['id'] === $selectedId ? ' is-selected' : '' ?>" href="<?= e(url('goals', array_filter(['goal' => $g['id'], 'show' => $showDone ? 'done' : null]))) ?>">
          <span class="row">
            <span class="cat-tile"><?= icon($g['cat_icon'] ?: 'target', 16) ?></span>
            <span class="goal-card-text"><b class="truncate"><?= e($g['title']) ?></b><small><?= e(goal_amount($g)) ?><?= $g['due_on'] ? ' · due ' . e(fmt_day($g['due_on'])) : '' ?></small></span>
            <?= status_badge($g['pace'], $g['pace_tone']) ?>
          </span>
          <?= progress_bar($g['percent'], 'c-' . ($g['cat_color'] ?: 'gray'), 'bar-thin') ?>
        </a>
      <?php endforeach ?>
      <a class="goal-switch" href="<?= e(url('goals', $showDone ? [] : ['show' => 'done'])) ?>">
        <?= icon($showDone ? 'target' : 'circle-check-big', 16) ?><span><?= $showDone ? 'Active goals' : 'Completed goals' ?></span>
        <em class="muted"><?= $showDone ? count($active) : count($done) ?></em><?= icon('chevron-right', 16) ?>
      </a>
    </div>
  </section>

  <?php if ($goal): ?>
    <section class="card" data-record="<?= e(goal_json($goal, $parts)) ?>" data-goal="<?= (int) $goal['id'] ?>">
      <div class="card-head goal-head">
        <span class="cat-tile c-<?= e($goal['cat_color'] ?: 'gray') ?>"><?= icon($goal['cat_icon'] ?: 'target', 16) ?></span>
        <span class="goal-head-text">
          <h2><?= e($goal['title']) ?></h2>
          <small class="meta"><?= $parts ? e(plural(count($parts), 'part')) . ' · ' : '' ?><?= e($fmtAmount((float) $goal['target'], $goal['unit'])) ?><?= $goal['start_on'] ? ' · started ' . e(fmt_day($goal['start_on'])) : '' ?></small>
        </span>
        <?= status_badge($goal['pace'], $goal['pace_tone']) ?>
        <div class="card-head-tools">
          <button class="btn btn-sm" type="button" data-wizard-edit><?= icon('sparkles', 16) ?>Edit plan</button>
          <div class="menu-wrap">
            <?= menu_button('goal-menu', 'Goal actions') ?>
            <div class="menu" id="goal-menu" role="menu" hidden>
              <?php if ($goal['status'] === 'done'): ?>
                <button class="menu-item" type="button" data-goal-status="active"><?= icon('rotate-ccw', 16) ?>Reopen goal</button>
              <?php else: ?>
                <button class="menu-item" type="button" data-goal-status="done"><?= icon('circle-check-big', 16) ?>Mark as done</button>
              <?php endif ?>
              <button class="menu-item is-danger" type="button" data-goal-delete><?= icon('trash-2', 16) ?>Delete goal</button>
            </div>
          </div>
        </div>
      </div>
      <div class="panel goal-detail">
        <div class="goal-stats">
          <div><span>Done</span><b><?= e($fmtAmount((float) $goal['done'], $goal['unit'])) ?></b><small><?= e(plural(goal_sessions_week((int) $goal['id']), 'session')) ?> this week</small></div>
          <div><span>Left</span><b><?= e($fmtAmount($goal['left'], $goal['unit'])) ?></b><small><?= $parts ? 'across ' . e(plural(count(array_filter($parts, fn ($p) => $p['done'] < $p['target'])), 'part')) : 'to the target' ?></small></div>
          <div><span>Pace needed</span><b><?= $goal['per_day'] !== null ? e($fmtAmount(round($goal['per_day'], 1), $goal['unit'])) : 'Not set' ?></b><small>a day<?= $goal['rest_days'] ? ', ' . (7 - (int) $goal['rest_days']) . ' days a week' : '' ?></small></div>
          <div><span>Finish by</span><b><?= e(fmt_day($goal['due_on'])) ?></b><small><?= $goal['days_left'] !== null ? ($goal['days_left'] >= 0 ? e(plural($goal['days_left'], 'day')) . ' left' : e(plural(-$goal['days_left'], 'day')) . ' past') : 'No deadline' ?></small></div>
        </div>
        <?= progress_bar($goal['percent'], '', 'bar-thick') ?>
        <?php $week = goal_logged_week((int) $goal['id']); $weekPerDay = $week / 7; ?>
        <div class="row-between meta goal-note">
          <b class="strong"><?= $goal['percent'] ?>% complete</b>
          <?php if ($goal['per_day'] !== null): ?>
            <span><?= e($goal['pace']) ?>: <?= e($fmtAmount(round($weekPerDay, 1), $goal['unit'])) ?> a day logged this week vs <?= e($fmtAmount(round($goal['per_day'], 1), $goal['unit'])) ?> needed</span>
          <?php endif ?>
        </div>

        <div class="row-between goal-parts-head">
          <h3 class="t-section"><?= $parts ? 'Parts, in order' : 'Progress log' ?></h3>
          <?php if ($parts): ?><span class="meta">Sub-goals from <?= e($fmtAmount((float) $goal['target'], $goal['unit'])) ?> over <?= e(plural(count($parts), 'part')) ?></span><?php endif ?>
        </div>
        <?php if (!$parts): ?>
          <p class="muted goal-noparts">No parts yet. Use Edit plan to split this goal into courses, chapters or screens.</p>
        <?php endif ?>
        <ol class="parts">
          <?php $current = null; foreach ($parts as $i => $p): ?>
            <?php
            $isDone = $p['done'] >= $p['target'];
            $isCurrent = !$isDone && $current === null;
            if ($isCurrent) {
                $current = $p['id'];
            }
            [$pStart, $pEnd] = $partDates[(int) $p['id']] ?? [null, null];
            $when = match (true) {
                $isDone => 'Done',
                $isCurrent => $pEnd ? 'Until ' . fmt_day($pEnd) : 'In progress',
                default => $pStart ? 'Starts ' . fmt_day($pStart) : 'Later',
            };
            ?>
            <li class="part<?= $isDone ? ' is-done' : '' ?><?= $isCurrent ? ' is-current' : '' ?>">
              <?= icon($isDone ? 'circle-check' : ($isCurrent ? 'loader' : 'clock'), 18) ?>
              <span class="part-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <span class="part-title truncate"><?= e($p['title']) ?></span>
              <span class="part-when"><?= e($when) ?></span>
              <?= progress_bar(pct((float) $p['done'], (float) $p['target']), $isDone ? 'c-green' : 'c-purple', 'bar-thin') ?>
              <span class="part-amount"><?= e(num($p['done']) . ' of ' . num($p['target']) . ' ' . $goal['unit']) ?></span>
            </li>
          <?php endforeach ?>
        </ol>
        <div class="actions goal-actions">
          <?php if ($isHours && $goal['per_day']): ?>
            <button class="btn" type="button" data-goal-schedule><?= icon('calendar-plus', 16) ?>Add <?= e(fmt_minutes((int) (ceil($goal['per_day'] * 4) * 15))) ?> daily block to Schedule</button>
          <?php endif ?>
          <button class="btn" type="button" data-open="log-dialog" data-fill="<?= e(json_encode(['goal_id' => (int) $goal['id'], 'logged_on' => today()])) ?>"><?= icon('timer', 16) ?>Log <?= $isHours ? 'hours' : 'progress' ?></button>
        </div>
      </div>
    </section>
  <?php endif ?>
</div>
<?= partial('goal-dialogs', ['goal' => $goal]) ?>
