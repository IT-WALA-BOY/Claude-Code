<?php
declare(strict_types=1);

const TASK_VIEWS = ['list' => ['List', 'list-todo'], 'board' => ['Board', 'layout-dashboard'], 'timeline' => ['Timeline', 'chart-gantt'], 'calendar' => ['Calendar', 'calendar']];
const TASK_SORTS = ['manual' => 'Manual order', 'due' => 'Due date', 'priority' => 'Priority'];
const STATUS_ICONS = ['todo' => 'list-todo', 'doing' => 'loader', 'review' => 'file-search', 'done' => 'circle-check-big', 'later' => 'pause'];

$view = isset(TASK_VIEWS[$_GET['view'] ?? '']) ? $_GET['view'] : 'board';
$catId = (int) ($_GET['cat'] ?? 0);
$cat = category($catId);
$prio = isset(TASK_PRIORITIES[$_GET['prio'] ?? '']) ? $_GET['prio'] : '';
$sort = isset(TASK_SORTS[$_GET['sort'] ?? '']) ? $_GET['sort'] : ($view === 'board' ? 'manual' : 'due');
$page['module'] = 'tasks-' . $view;

$order = match ($sort) {
    'due' => 't.due_on IS NULL, t.due_on, t.due_time IS NULL, t.due_time, t.position',
    'priority' => "FIELD(t.priority, 'urgent', 'moderate', 'low'), t.due_on IS NULL, t.due_on",
    default => 't.position, t.id',
};
$tasks = tasks_list(['category' => $cat ? $catId : null, 'with_done_since' => week_start(), 'order' => $order]);
if ($prio) {
    $tasks = array_values(array_filter($tasks, fn ($t) => $t['priority'] === $prio));
}
$checklists = task_checklists(array_column($tasks, 'id'));
$counts = task_counts();
$byCat = open_counts_by_category();

/** Link to this page keeping the current filters, with some changed. */
$filters = ['view' => $view, 'cat' => $catId ?: null, 'prio' => $prio ?: null, 'sort' => $sort];
$link = fn (array $change) => url('tasks', array_filter(array_merge($filters, $change), fn ($v) => $v !== null && $v !== ''));

$hint = match ($view) {
    'board' => 'drag a card to change its status',
    'timeline' => 'drag a bar to move it, drag its edges to change dates',
    'calendar' => 'click a day to add a task',
    default => 'tick a task to finish it',
};
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome"><?= e($cat ? $cat['name'] . ' tasks' : 'All tasks') ?></h2>
    <p><?= $counts['open'] ?> open · <?= $counts['today'] ?> due today · <?= $counts['overdue'] ?> overdue · <?= e($hint) ?></p>
  </div>
  <nav class="views" aria-label="Views">
    <?php foreach (TASK_VIEWS as $key => [$label, $ic]): ?>
      <a class="btn<?= $key === $view ? ' is-active' : '' ?>" href="<?= e($link(['view' => $key])) ?>"<?= $key === $view ? ' aria-current="page"' : '' ?>><?= icon($ic, 16) ?><?= e($label) ?></a>
    <?php endforeach ?>
  </nav>
</div>

<?php if ($view !== 'timeline'): ?>
<div class="toolbar">
  <div class="chips">
    <a class="fchip<?= !$cat ? ' is-active' : '' ?>" href="<?= e($link(['cat' => null])) ?>">All <em><?= $counts['open'] ?></em></a>
    <?php foreach (categories() as $c): ?>
      <a class="fchip<?= $catId === (int) $c['id'] ? ' is-active' : '' ?>" href="<?= e($link(['cat' => $c['id']])) ?>">
        <span class="cat-tile c-<?= e($c['color']) ?>"><?= icon($c['icon'], 14) ?></span><?= e($c['name']) ?> <em><?= (int) ($byCat[$c['id']] ?? 0) ?></em>
      </a>
    <?php endforeach ?>
  </div>
  <?= partial('tasks-tools', ['link' => $link, 'prio' => $prio, 'sort' => $sort, 'cat' => $cat]) ?>
</div>
<?php endif ?>

<?php require __DIR__ . '/tasks/' . $view . '.php'; ?>
