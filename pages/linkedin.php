<?php
declare(strict_types=1);

const LI_TABS = ['pipeline' => ['Pipeline', 'layout-dashboard'], 'posts' => ['Posts', 'megaphone'], 'targets' => ['Daily targets', 'target']];

$tab = isset(LI_TABS[$_GET['tab'] ?? '']) ? $_GET['tab'] : 'pipeline';
$counts = li_counts();
$targets = li_targets();
$todayCounts = li_today();
$prospects = li_prospects();
$byStage = array_fill_keys(array_keys(LI_STAGES), []);
foreach ($prospects as $p) {
    $byStage[$p['stage']][] = $p;
}
$liBlock = array_values(array_filter(schedule_today()['blocks'], fn ($b) => ($b['cat_name'] ?? '') === 'LinkedIn'));
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome"><?= e(['pipeline' => 'Lead pipeline', 'posts' => 'Post planner', 'targets' => 'Daily targets'][$tab]) ?></h2>
    <p><?= e(plural($counts['pipeline'], 'lead')) ?> · <?= e(plural($counts['due'], 'follow-up')) ?> due · free teardown, then paid audit, then redesign</p>
  </div>
  <div class="actions">
    <nav class="views" aria-label="LinkedIn views">
      <?php foreach (LI_TABS as $key => [$label, $ic]): ?>
        <a class="btn<?= $key === $tab ? ' is-active' : '' ?>" href="<?= e(url('linkedin', $key === 'pipeline' ? [] : ['tab' => $key])) ?>"><?= icon($ic, 16) ?><?= e($label) ?></a>
      <?php endforeach ?>
    </nav>
    <?php if ($tab === 'posts'): ?>
      <button class="btn btn-primary" type="button" data-open="post-dialog"><?= icon('plus', 16) ?>Add post</button>
    <?php else: ?>
      <button class="btn btn-primary" type="button" data-open="lead-dialog"><?= icon('plus', 16) ?>Add lead</button>
    <?php endif ?>
  </div>
</div>

<section class="card outreach">
  <?= card_head('target', "Today's outreach", '<span class="meta">' . ($liBlock ? e(fmt_range($liBlock[0]['starts_at'], $liBlock[0]['ends_at'])) . ' block · ' : '') . 'target 1 to 1.5 h a day</span>') ?>
  <div class="panel outreach-grid">
    <?php foreach ([...array_keys(LI_METRICS), 'posts'] as $metric): ?>
      <?php
      $n = $todayCounts[$metric];
      $target = max(1, $targets[$metric]);
      $segments = min(10, $target);
      $on = (int) min($segments, round($n / $target * $segments));
      $met = $n >= $target;
      ?>
      <div class="counter" data-metric="<?= e($metric) ?>" data-count="<?= $n ?>" data-target="<?= $target ?>">
        <p class="counter-label"><?= icon(LI_METRIC_ICONS[$metric], 16) ?><?= e(LI_METRICS[$metric] ?? 'Posts published') ?><?= $met ? icon('circle-check', 16, 'tone-positive counter-met') : '' ?></p>
        <p class="counter-value"><b data-value><?= $n ?></b> <span>of <?= $target ?></span>
          <?php if ($metric !== 'posts'): ?>
            <span class="counter-btns">
              <button class="icon-btn icon-btn-xs" type="button" data-step="-1" aria-label="One less"><?= icon('minus', 14) ?></button>
              <button class="icon-btn icon-btn-xs" type="button" data-step="1" aria-label="One more"><?= icon('plus', 14) ?></button>
            </span>
          <?php endif ?>
        </p>
        <div class="counter-bar<?= $met ? ' is-met' : '' ?>" style="--n:<?= $segments ?>"><?php for ($i = 0; $i < $segments; $i++): ?><i<?= $i < $on ? ' class="on"' : '' ?>></i><?php endfor ?></div>
      </div>
    <?php endforeach ?>
  </div>
</section>

<?php if ($tab === 'pipeline'): ?>
  <div class="pipeline" data-pipeline>
    <?php foreach (LI_STAGES as $stage => $label): ?>
      <?php [$stageIcon, $stageNote] = LI_STAGE_INFO[$stage]; $list = $byStage[$stage]; ?>
      <section class="pcol<?= in_array($stage, ['won', 'lost'], true) ? ' pcol-end' : '' ?>" data-stage="<?= e($stage) ?>">
        <header class="pcol-head">
          <p><?= icon($stageIcon, 16) ?><b><?= e($label) ?></b><em class="count" data-count><?= count($list) ?></em></p>
          <small><?= e($stageNote) ?><?php if ($list && array_sum(array_column($list, 'value')) > 0): ?> · <?= e(usd(array_sum(array_column($list, 'value')))) ?><?php endif ?></small>
        </header>
        <div class="pcol-list">
          <?php foreach ($list as $p): ?>
            <?php
            $late = $p['next_followup'] && $p['next_followup'] < today() && !in_array($stage, ['won', 'lost'], true);
            $dueToday = $p['next_followup'] === today();
            $followText = match (true) {
                !$p['next_followup'] => 'No follow-up set',
                $late => plural(days_between($p['next_followup'], today()), 'day') . ' late',
                $dueToday => 'Follow up today',
                default => day($p['next_followup'])->format('D j M'),
            };
            ?>
            <article class="lead<?= $late ? ' is-late' : '' ?>" data-id="<?= (int) $p['id'] ?>" data-record="<?= e(li_prospect_json($p)) ?>" tabindex="0">
              <div class="lead-top">
                <span class="lead-name"><b class="truncate"><?= e($p['name']) ?></b><small class="truncate"><?= e($p['company']) ?></small></span>
                <?php if ($late): ?>
                  <?= icon('circle-alert', 16, 'tone-negative') ?>
                <?php elseif ($dueToday): ?>
                  <?= icon('clock', 16, 'tone-warning') ?>
                <?php elseif ($p['profile_url']): ?>
                  <a class="icon-btn icon-btn-xs" href="<?= e($p['profile_url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Open profile"><?= icon('arrow-up-right', 14) ?></a>
                <?php endif ?>
              </div>
              <div class="lead-tags"><span class="tag"><?= e($p['market']) ?></span><?php if ($p['industry']): ?><span class="tag"><?= e($p['industry']) ?></span><?php endif ?></div>
              <footer class="lead-foot">
                <span class="<?= $late ? 'tone-negative' : ($dueToday ? 'tone-warning' : '') ?>"><?= icon('send', 14) ?><?= e($followText) ?></span>
                <?php if (!in_array($stage, ['won', 'lost'], true) && ($late || $dueToday)): ?>
                  <button class="btn btn-xs" type="button" data-followed>Done</button>
                <?php else: ?>
                  <span class="muted"><?= icon('target', 14) ?><?= min(7, li_step($stage)) ?>/7</span>
                <?php endif ?>
              </footer>
            </article>
          <?php endforeach ?>
          <p class="kcol-empty" data-drop-end>Drop a lead here.</p>
        </div>
        <?php if ($stage === 'shortlisted'): ?>
          <button class="kcol-add" type="button" data-open="lead-dialog"><?= icon('plus', 16) ?>Add lead</button>
        <?php endif ?>
      </section>
    <?php endforeach ?>
  </div>
<?php elseif ($tab === 'posts'): ?>
  <?= partial('linkedin-posts') ?>
<?php else: ?>
  <?= partial('linkedin-targets', ['targets' => $targets]) ?>
<?php endif ?>

<?php if ($tab === 'pipeline'): ?>
  <?php
  $weekStart = week_start();
  $posts = li_posts_between($weekStart, add_days($weekStart, 6));
  $monthPosts = (int) val("SELECT COUNT(*) FROM li_posts WHERE status = 'posted' AND post_on BETWEEN ? AND ?", [month_start(), month_end()]);
  $funnel = li_funnel();
  $top = max(1, reset($funnel));
  $replyRate = $funnel['teardown'] ? pct($funnel['replied'], $funnel['teardown']) : 0;
  ?>
  <div class="grid grid-2 section-gap">
    <section class="card">
      <?= card_head('megaphone', 'Posts this week', '<a class="link" href="' . e(url('linkedin', ['tab' => 'posts'])) . '">' . count($posts) . ' planned · ' . $monthPosts . ' posted this month' . icon('chevron-right', 16) . '</a>') ?>
      <div class="panel panel-flush">
        <?php if (!$posts): ?>
          <?= empty_state('megaphone', 'No posts planned this week', 'Plan one teardown post to keep the funnel warm.', '<button class="btn btn-sm" type="button" data-open="post-dialog">Add post</button>', 'empty-sm') ?>
        <?php endif ?>
        <?php foreach ($posts as $post): ?>
          <?= partial('linkedin-post-row', ['post' => $post]) ?>
        <?php endforeach ?>
      </div>
    </section>
    <section class="card">
      <?= card_head('chart-bar', 'Funnel', '<span class="meta">Reply rate ' . $replyRate . '% · ' . $funnel['won'] . ' won</span>') ?>
      <div class="panel funnel">
        <?php foreach ($funnel as $stage => $n): ?>
          <div class="funnel-row">
            <span><?= e(LI_STAGES[$stage]) ?></span>
            <?= progress_bar(pct($n, $top), $stage === 'won' ? 'c-green' : 'c-blue') ?>
            <b><?= $n ?></b>
          </div>
        <?php endforeach ?>
      </div>
    </section>
  </div>
<?php endif ?>
<?= partial('linkedin-dialogs') ?>
