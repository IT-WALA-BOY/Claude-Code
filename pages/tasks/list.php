<?php
/** List view: status groups with tinted headers, collapsible. */
$groups = ['todo' => 'blue', 'doing' => 'yellow', 'review' => 'pink', 'done' => 'green', 'later' => 'gray'];
$byStatus = array_fill_keys(array_keys($groups), []);
foreach ($tasks as $t) {
    $byStatus[$t['status']][] = $t;
}
$openId = (int) ($_GET['open'] ?? 0);
?>
<div class="groups"<?= $openId ? ' data-open-id="' . $openId . '"' : '' ?>>
  <?php foreach ($groups as $status => $color): ?>
    <?php $list = $byStatus[$status]; ?>
    <section class="group c-<?= e($color) ?>" data-group>
      <header class="group-head">
        <button class="group-toggle" type="button" data-collapse aria-expanded="true"><?= icon('chevron-down', 16) ?></button>
        <span class="group-pill"><?= icon(STATUS_ICONS[$status], 14) ?><?= e($status === 'done' ? 'Done this week' : TASK_STATUSES[$status]) ?></span>
        <em class="count"><?= count($list) ?></em>
        <button class="icon-btn icon-btn-sm group-add" type="button" data-open="task-dialog" data-fill="<?= e(json_encode(['status' => $status] + ($cat ? ['category_id' => (int) $cat['id']] : []))) ?>" aria-label="Add task"><?= icon('plus', 16) ?></button>
      </header>
      <div class="group-body">
        <?php if (!$list): ?>
          <p class="group-empty">No tasks here.</p>
        <?php else: ?>
          <div class="table-scroll">
            <table class="tbl tbl-fixed">
              <thead><tr>
                <th class="col-check"><span class="sr-only">Done</span></th>
                <th><?= icon('text', 14) ?>Task</th>
                <th class="w-120"><?= icon('tag', 14) ?>Category</th>
                <th class="w-120"><?= icon('flag', 14) ?>Priority</th>
                <th class="w-100"><?= icon('calendar', 14) ?>Start</th>
                <th class="w-140"><?= icon('calendar-check', 14) ?>Due</th>
                <th class="w-100"><?= icon('list-checks', 14) ?>Checklist</th>
                <th class="col-menu"><span class="sr-only">Actions</span></th>
              </tr></thead>
              <tbody>
              <?php foreach ($list as $t): ?>
                <tr data-id="<?= (int) $t['id'] ?>" data-record="<?= e(task_json($t, $checklists[$t['id']] ?? [])) ?>"<?= $t['status'] === 'done' ? ' class="is-done"' : '' ?>>
                  <td class="col-check"><input class="check" type="checkbox" data-done aria-label="Done"<?= $t['status'] === 'done' ? ' checked' : '' ?>></td>
                  <td><div class="cell-title"><?= e($t['title']) ?></div><?php if ($t['notes']): ?><div class="cell-sub"><?= e($t['notes']) ?></div><?php endif ?></td>
                  <td><?= category_pill($t['cat_name'], $t['cat_color']) ?></td>
                  <td><?= priority_pill($t['priority']) ?></td>
                  <td class="muted"><?= e(fmt_day($t['start_on'])) ?></td>
                  <td><?= due_html($t['due_text'], $t['due_tone']) ?></td>
                  <td class="muted num-left"><?= $t['checks_total'] ? (int) $t['checks_done'] . '/' . (int) $t['checks_total'] : '0' ?></td>
                  <td class="col-menu"><button class="icon-btn icon-btn-sm" type="button" data-edit="task-dialog" aria-label="Edit task"><?= icon('pencil', 16) ?></button></td>
                </tr>
              <?php endforeach ?>
              </tbody>
            </table>
          </div>
        <?php endif ?>
      </div>
    </section>
  <?php endforeach ?>
</div>
