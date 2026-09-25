<?php
/** Board view: kanban columns, drag a card to change status or order. */
const BOARD_VISIBLE = 4;
$columns = ['todo' => 'To do', 'doing' => 'In progress', 'review' => 'Review', 'done' => 'Done this week', 'later' => 'Later'];
$byStatus = array_fill_keys(array_keys($columns), []);
foreach ($tasks as $t) {
    $byStatus[$t['status']][] = $t;
}
?>
<div class="board" data-board>
  <?php foreach ($columns as $status => $label): ?>
    <?php $list = $byStatus[$status]; $isRail = $status === 'later'; ?>
    <section class="kcol<?= $isRail ? ' kcol-rail' : '' ?>" data-status="<?= e($status) ?>" aria-label="<?= e($label) ?>">
      <?php if ($isRail): ?>
        <button class="kcol-rail-toggle" type="button" data-rail aria-label="Show Later column"><?= icon('chevron-right', 16) ?><em class="count" data-count><?= count($list) ?></em><span><?= e($label) ?></span></button>
      <?php endif ?>
      <header class="kcol-head">
        <?= icon(STATUS_ICONS[$status], 18) ?>
        <h3><?= e($label) ?></h3>
        <em class="count" data-count><?= count($list) ?></em>
        <span class="kcol-tools">
          <button class="icon-btn icon-btn-sm" type="button" data-open="task-dialog" data-fill="<?= e(json_encode(['status' => $status] + ($cat ? ['category_id' => (int) $cat['id']] : []))) ?>" aria-label="Add task to <?= e($label) ?>"><?= icon('plus', 16) ?></button>
          <?php if ($isRail): ?><button class="icon-btn icon-btn-sm" type="button" data-rail aria-label="Collapse"><?= icon('chevron-left', 16) ?></button><?php endif ?>
        </span>
      </header>
      <div class="kcol-list" data-drop>
        <?php foreach ($list as $i => $t): ?>
          <?= partial('task-card', ['t' => $t, 'checklist' => $checklists[$t['id']] ?? [], 'extra' => $i >= BOARD_VISIBLE]) ?>
        <?php endforeach ?>
        <p class="kcol-empty" data-drop-end>No tasks. Drop one here.</p>
      </div>
      <?php if (count($list) > BOARD_VISIBLE): ?>
        <button class="kcol-more" type="button" data-more>Show <?= count($list) - BOARD_VISIBLE ?> more<?= icon('chevron-down', 16) ?></button>
      <?php endif ?>
      <?php if ($status !== 'done'): ?>
        <button class="kcol-add" type="button" data-open="task-dialog" data-fill="<?= e(json_encode(['status' => $status] + ($cat ? ['category_id' => (int) $cat['id']] : []))) ?>"><?= icon('plus', 16) ?>Add task</button>
      <?php endif ?>
    </section>
  <?php endforeach ?>
</div>
