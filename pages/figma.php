<?php
declare(strict_types=1);

$projects = figma_projects();
$groups = ['client' => ['Client work', 'briefcase-business', 'blue'], 'portfolio' => ['Portfolio', 'pen-tool', 'purple']];
$byKind = ['client' => [], 'portfolio' => []];
foreach ($projects as $p) {
    $byKind[$p['kind']][] = $p;
}
$recent = array_slice(array_values(array_filter($projects, fn ($p) => $p['last_opened_at'])), 0, 4);
usort($recent, fn ($a, $b) => strcmp($b['last_opened_at'], $a['last_opened_at']));
$figmaCat = array_values(array_filter(categories(), fn ($c) => $c['name'] === 'Figma'))[0]['id'] ?? null;
$json = fn (array $p) => json_encode(['id' => (int) $p['id'], 'name' => $p['name'], 'client' => $p['client'], 'kind' => $p['kind'], 'source' => $p['source'],
    'note' => $p['note'], 'figma_url' => $p['figma_url'], 'due_on' => (string) $p['due_on'], 'status' => $p['status']], JSON_UNESCAPED_UNICODE);
$recentColors = ['blue', 'green', 'purple', 'pink'];
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome">Figma projects</h2>
    <p><?= e(plural(count($byKind['client']), 'client project')) ?> · <?= e(plural(count($byKind['portfolio']), 'portfolio piece')) ?> · tasks linked to a project count toward its progress</p>
  </div>
  <div class="actions">
    <nav class="views" aria-label="Views">
      <span class="btn is-active"><?= icon('list-todo', 16) ?>List</span>
      <a class="btn" href="<?= e(url('tasks', array_filter(['view' => 'board', 'cat' => $figmaCat]))) ?>"><?= icon('layout-dashboard', 16) ?>Board</a>
      <a class="btn" href="<?= e(url('tasks', array_filter(['view' => 'timeline', 'cat' => $figmaCat]))) ?>"><?= icon('chart-gantt', 16) ?>Timeline</a>
    </nav>
    <button class="btn btn-primary" type="button" data-open="project-dialog"><?= icon('plus', 16) ?>New project</button>
  </div>
</div>

<div class="groups">
  <?php foreach ($groups as $kind => [$label, $ic, $color]): ?>
    <?php $list = $byKind[$kind]; ?>
    <section class="group c-<?= e($color) ?>" data-group>
      <header class="group-head">
        <button class="group-toggle" type="button" data-collapse aria-expanded="true"><?= icon('chevron-down', 16) ?></button>
        <span class="group-pill"><?= icon($ic, 14) ?><?= e($label) ?></span>
        <em class="count"><?= count($list) ?></em>
        <button class="icon-btn icon-btn-sm group-add" type="button" data-open="project-dialog" data-fill="<?= e(json_encode(['kind' => $kind, 'source' => $kind === 'portfolio' ? 'self' : 'upwork'])) ?>" aria-label="Add project"><?= icon('plus', 16) ?></button>
      </header>
      <div class="group-body">
        <?php if (!$list): ?>
          <p class="group-empty">No projects here yet.</p>
        <?php else: ?>
          <div class="table-scroll">
            <table class="tbl tbl-fixed">
              <thead><tr>
                <th><?= icon('figma', 14) ?>Project</th><th class="w-160"><?= icon('users', 14) ?>Client</th><th class="w-140"><?= icon('loader', 14) ?>Status</th>
                <th class="w-160"><?= icon('chart-column', 14) ?>Progress</th><th class="w-120"><?= icon('calendar', 14) ?>Due</th><th class="w-120"><span class="sr-only">Open</span></th>
              </tr></thead>
              <tbody>
              <?php foreach ($list as $p): ?>
                <?php
                [$statusText, $statusTone] = project_status($p);
                $percent = pct((float) $p['tasks_done'], (float) $p['tasks_total']);
                [$srcLabel, $srcColor] = PROJECT_SOURCES[$p['source']];
                ?>
                <tr data-id="<?= (int) $p['id'] ?>" data-record="<?= e($json($p)) ?>">
                  <td>
                    <button class="project-cell" type="button" data-edit="project-dialog">
                      <span class="cat-tile c-pink"><?= icon('figma', 16) ?></span>
                      <span class="min-0"><span class="cell-title"><?= e($p['name']) ?></span><?php if ($p['note']): ?><span class="cell-sub"><?= e($p['note']) ?></span><?php endif ?></span>
                    </button>
                  </td>
                  <td><div class="cell-title"><?= e($p['client'] ?: 'Self') ?></div><span class="pill c-<?= e($srcColor) ?> mt-4"><?= e($srcLabel) ?></span></td>
                  <td><?= status_badge($statusText, $statusTone) ?></td>
                  <td class="progress-cell">
                    <div class="row-between meta"><span><?= (int) $p['tasks_done'] ?> of <?= e(plural((int) $p['tasks_total'], 'task')) ?></span><b class="strong"><?= $percent ?>%</b></div>
                    <?= seg_bar($percent) ?>
                  </td>
                  <td><?= e($p['due_on'] ? day($p['due_on'])->format('D j M') : 'Not set') ?></td>
                  <td><?php if ($p['figma_url']): ?><a class="btn btn-sm" href="<?= e($p['figma_url']) ?>" target="_blank" rel="noopener noreferrer" data-opened data-no-prerender><?= icon('arrow-up-right', 16) ?>Open file</a><?php endif ?></td>
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

<?php if ($recent): ?>
  <section class="card section-gap">
    <?= card_head('file-clock', 'Recently opened files', '<span class="meta">Links open in Figma</span>') ?>
    <div class="panel recent">
      <?php foreach ($recent as $i => $p): ?>
        <a class="recent-file" href="<?= e($p['figma_url']) ?>" target="_blank" rel="noopener noreferrer" data-id="<?= (int) $p['id'] ?>" data-opened data-no-prerender>
          <span class="recent-thumb c-<?= e($recentColors[$i % 4]) ?>" aria-hidden="true"><i></i><b></b><b></b></span>
          <span class="recent-name"><?= icon('figma', 14) ?><b class="truncate"><?= e($p['name']) ?></b></span>
          <small><?= e(ago($p['last_opened_at'])) ?></small>
        </a>
      <?php endforeach ?>
    </div>
  </section>
<?php endif ?>

<dialog id="project-dialog" aria-labelledby="project-title">
  <form class="dialog-form" data-api="figma/save" data-delete-api="figma/delete" data-success="Project saved" data-defaults="<?= e(json_encode(['kind' => 'client', 'source' => 'upwork', 'status' => 'active'])) ?>">
    <input type="hidden" name="id">
    <div class="dialog-head">
      <h2 id="project-title" data-title-new="New project" data-title-edit="Edit project">New project</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <div class="form-grid">
        <label class="field"><span class="field-label">Project</span><span class="control"><input name="name" maxlength="160" required placeholder="e.g. Qualitas dashboard redesign"></span></label>
        <label class="field"><span class="field-label">Client</span><span class="control"><input name="client" maxlength="120" placeholder="Leave empty for portfolio"></span></label>
      </div>
      <label class="field"><span class="field-label">Figma link</span><span class="control"><input type="url" name="figma_url" maxlength="500" placeholder="https://www.figma.com/design/..."></span></label>
      <label class="field"><span class="field-label">Note</span><span class="control"><input name="note" maxlength="255" placeholder="e.g. 12 screens, revisions v3 tonight"></span></label>
      <div class="form-grid mt-14">
        <div class="field"><span class="field-label">Type</span><div class="pick">
          <label><input type="radio" name="kind" value="client"><span class="pill c-blue">Client work</span></label>
          <label><input type="radio" name="kind" value="portfolio"><span class="pill c-purple">Portfolio</span></label>
        </div></div>
        <div class="field"><span class="field-label">Source</span><div class="pick">
          <?php foreach (PROJECT_SOURCES as $key => [$label, $color]): ?><label><input type="radio" name="source" value="<?= e($key) ?>"><span class="pill c-<?= e($color) ?>"><?= e($label) ?></span></label><?php endforeach ?>
        </div></div>
      </div>
      <div class="form-grid mt-14">
        <label class="field"><span class="field-label">Status</span><span class="control control-select"><select name="status">
          <?php foreach (PROJECT_STATUSES as $key => $label): ?><option value="<?= e($key) ?>"><?= e($label) ?></option><?php endforeach ?>
        </select></span></label>
        <label class="field"><span class="field-label">Due</span><span class="control"><input type="date" name="due_on"></span></label>
      </div>
    </div>
    <div class="dialog-foot">
      <button class="btn btn-quiet btn-danger" type="button" data-delete data-confirm="The project will be deleted. Its tasks stay, without a project." hidden><?= icon('trash-2', 16) ?>Delete</button>
      <span class="spacer"></span>
      <button class="btn" type="button" data-action="close-dialog">Cancel</button>
      <button class="btn btn-primary" type="submit">Save</button>
    </div>
  </form>
</dialog>
