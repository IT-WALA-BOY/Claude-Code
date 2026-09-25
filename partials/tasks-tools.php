<?php
/** @var callable $link  @var string $prio  @var string $sort  @var ?array $cat */
?>
<div class="actions">
  <div class="menu-wrap">
    <button class="btn<?= $prio ? ' has-value' : '' ?>" type="button" data-menu="filter-menu"><?= icon('funnel', 16) ?><?= $prio ? e(TASK_PRIORITIES[$prio]) . ' only' : 'Filter' ?></button>
    <div class="menu" id="filter-menu" role="menu" hidden>
      <p class="menu-title">Priority</p>
      <a class="menu-item" href="<?= e($link(['prio' => null])) ?>"><?= icon($prio ? 'circle' : 'circle-check-big', 16) ?>Any priority</a>
      <?php foreach (TASK_PRIORITIES as $key => $label): ?>
        <a class="menu-item" href="<?= e($link(['prio' => $key])) ?>"><?= icon($prio === $key ? 'circle-check-big' : 'circle', 16) ?><?= e($label) ?></a>
      <?php endforeach ?>
    </div>
  </div>
  <div class="menu-wrap">
    <button class="btn" type="button" data-menu="sort-menu"><?= icon('arrow-up-down', 16) ?>Sort: <?= e(strtolower(TASK_SORTS[$sort])) ?></button>
    <div class="menu" id="sort-menu" role="menu" hidden>
      <?php foreach (TASK_SORTS as $key => $label): ?>
        <a class="menu-item" href="<?= e($link(['sort' => $key])) ?>"><?= icon($sort === $key ? 'circle-check-big' : 'circle', 16) ?><?= e($label) ?></a>
      <?php endforeach ?>
    </div>
  </div>
  <button class="btn btn-primary" type="button" data-open="task-dialog"<?= $cat ? ' data-fill="' . e(json_encode(['category_id' => (int) $cat['id']])) . '"' : '' ?>><?= icon('plus', 16) ?>Add task</button>
</div>
