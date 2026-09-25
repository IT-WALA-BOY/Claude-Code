<?php
$projects = figma_project_options();
?>
<dialog class="palette" id="palette" aria-label="Search">
  <div class="palette-input"><?= icon('search', 18) ?><input type="search" placeholder="Search tasks, leads, goals, projects" aria-label="Search"><kbd>Esc</kbd></div>
  <div class="palette-list" role="listbox"></div>
</dialog>

<dialog class="dialog-sm" id="confirm-dialog">
  <div class="dialog-head"><h2>Are you sure?</h2></div>
  <div class="dialog-body"><p class="muted" data-confirm-text></p></div>
  <div class="dialog-foot"><span class="spacer"></span><button class="btn" type="button" data-confirm-no>Cancel</button><button class="btn btn-primary" type="button" data-confirm-yes>Delete</button></div>
</dialog>

<dialog id="task-dialog" aria-labelledby="task-dialog-title">
  <form class="dialog-form" data-api="tasks/save" data-delete-api="tasks/delete" data-success="Task saved" data-defaults='{"priority":"moderate","status":"todo","category_id":""}'>
    <input type="hidden" name="id">
    <div class="dialog-head">
      <h2 id="task-dialog-title" data-title-new="Add task" data-title-edit="Edit task">Add task</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <label class="field">
        <span class="field-label">Title</span>
        <span class="control"><input name="title" maxlength="200" required autofocus placeholder="e.g. Follow up with Jake Morrison"></span>
      </label>
      <div class="field">
        <span class="field-label">Category</span>
        <div class="pick">
          <label><input type="radio" name="category_id" value=""><span class="pill c-gray">None</span></label>
          <?php foreach (categories() as $c): ?>
            <label><input type="radio" name="category_id" value="<?= (int) $c['id'] ?>"><span class="pill c-<?= e($c['color']) ?>"><?= icon($c['icon'], 14) ?><?= e($c['name']) ?></span></label>
          <?php endforeach ?>
        </div>
      </div>
      <div class="form-grid mt-14">
        <div class="field">
          <span class="field-label">Priority</span>
          <div class="pick">
            <?php foreach (TASK_PRIORITIES as $key => $label): ?>
              <label><input type="radio" name="priority" value="<?= e($key) ?>"><span class="pill prio-<?= e($key) ?>"><?= icon('flag', 14) ?><?= e($label) ?></span></label>
            <?php endforeach ?>
          </div>
        </div>
        <label class="field">
          <span class="field-label">Status</span>
          <span class="control control-select"><select name="status">
            <?php foreach (TASK_STATUSES as $key => $label): ?><option value="<?= e($key) ?>"><?= e($label) ?></option><?php endforeach ?>
          </select></span>
        </label>
      </div>
      <div class="form-grid form-grid-3 mt-14">
        <label class="field"><span class="field-label">Start</span><span class="control"><input type="date" name="start_on"></span></label>
        <label class="field"><span class="field-label">Due</span><span class="control"><input type="date" name="due_on"></span></label>
        <label class="field"><span class="field-label">Due time</span><span class="control"><input type="time" name="due_time"></span></label>
      </div>
      <div class="form-grid mt-14">
        <label class="field"><span class="field-label">Estimate (minutes)</span><span class="control"><input type="number" name="est_minutes" min="0" step="5" inputmode="numeric" placeholder="60"></span></label>
        <label class="field"><span class="field-label">Figma project</span><span class="control control-select"><select name="project_id">
          <option value="">None</option>
          <?php foreach ($projects as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?><?= $p['status'] === 'done' ? ' (done)' : '' ?></option><?php endforeach ?>
        </select></span></label>
      </div>
      <label class="field">
        <span class="field-label">Notes</span>
        <span class="control"><textarea name="notes" rows="2" placeholder="Context, links, what done looks like"></textarea></span>
      </label>
      <div class="field">
        <span class="field-label">Checklist</span>
        <div class="checklist-edit" data-list="checklist"></div>
        <template data-row-for="checklist">
          <div class="ci" data-row>
            <input class="check" type="checkbox" data-key="done" aria-label="Done">
            <input type="text" data-key="text" maxlength="200" placeholder="Checklist item">
            <button class="icon-btn icon-btn-xs" type="button" data-remove-row aria-label="Remove item"><?= icon('x', 14) ?></button>
          </div>
        </template>
        <button class="btn btn-quiet btn-sm self-start" type="button" data-add-row="checklist"><?= icon('plus', 16) ?>Add item</button>
      </div>
      <div class="row toggles">
        <label class="row"><input class="switch" type="checkbox" name="important"><span>Important</span></label>
        <label class="row"><input class="switch" type="checkbox" name="urgent"><span>Urgent</span></label>
      </div>
    </div>
    <div class="dialog-foot">
      <button class="btn btn-quiet btn-danger" type="button" data-delete data-confirm="This task and its checklist will be deleted." hidden><?= icon('trash-2', 16) ?>Delete</button>
      <span class="spacer"></span>
      <button class="btn" type="button" data-action="close-dialog">Cancel</button>
      <button class="btn btn-primary" type="submit">Save</button>
    </div>
  </form>
</dialog>
