<?php
/** @var array $t  @var array $checklist  @var bool $extra */
$id = (int) $t['id'];
$showChecks = $checklist && trim((string) $t['notes']) === '';
?>
<article class="kcard prio-<?= e($t['priority']) ?><?= $extra ? ' is-extra' : '' ?><?= $t['status'] === 'done' ? ' is-done' : '' ?>" data-id="<?= $id ?>" data-record="<?= e(task_json($t, $checklist)) ?>" tabindex="0" aria-label="<?= e($t['title']) ?>">
  <div class="kcard-band"><?= icon('flag', 14) ?><?= e(TASK_PRIORITIES[$t['priority']]) ?> priority</div>
  <div class="kcard-body">
    <div class="row-between">
      <?= category_pill($t['cat_name'], $t['cat_color']) ?>
      <div class="menu-wrap">
        <?= menu_button('task-menu-' . $id, 'Task actions') ?>
        <div class="menu" id="task-menu-<?= $id ?>" role="menu" hidden>
          <button class="menu-item" type="button" data-edit="task-dialog"><?= icon('pencil', 16) ?>Edit</button>
          <p class="menu-title">Move to</p>
          <?php foreach (TASK_STATUSES as $key => $label): ?>
            <?php if ($key !== $t['status']): ?>
              <button class="menu-item" type="button" data-move-to="<?= e($key) ?>"><?= icon(STATUS_ICONS[$key], 16) ?><?= e($label) ?></button>
            <?php endif ?>
          <?php endforeach ?>
        </div>
      </div>
    </div>
    <h3 class="kcard-title"><?= e($t['title']) ?></h3>
    <?php if ($showChecks): ?>
      <ul class="kcard-checks">
        <?php foreach (array_slice($checklist, 0, 4) as $i => $c): ?>
          <li><label><input class="check" type="checkbox" data-check="<?= $i ?>"<?= $c['done'] ? ' checked' : '' ?>><span><?= e($c['text']) ?></span></label></li>
        <?php endforeach ?>
      </ul>
    <?php elseif (trim((string) $t['notes']) !== ''): ?>
      <p class="kcard-notes"><?= e($t['notes']) ?></p>
    <?php endif ?>
    <div class="kcard-progress"><span>Progress</span><b data-progress><?= $t['progress'] ?>%</b></div>
    <?= seg_bar($t['progress']) ?>
    <footer class="kcard-foot">
      <?= due_html($t['due_text'], $t['due_tone']) ?>
      <span class="kcard-meta">
        <?php if ($t['checks_total']): ?><span title="Checklist"><?= icon('list-checks', 16) ?><b data-checks><?= (int) $t['checks_done'] ?>/<?= (int) $t['checks_total'] ?></b></span><?php endif ?>
        <?php if ($t['est_minutes']): ?><span title="Estimate"><?= icon('timer', 16) ?><?= e(fmt_minutes((int) $t['est_minutes'])) ?></span><?php endif ?>
      </span>
    </footer>
  </div>
</article>
