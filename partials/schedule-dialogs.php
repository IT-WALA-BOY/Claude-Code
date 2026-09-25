<dialog id="block-dialog" aria-labelledby="block-dialog-title">
  <form class="dialog-form" data-api="schedule/save" data-delete-api="schedule/delete" data-success="Block saved">
    <input type="hidden" name="id">
    <div class="dialog-head">
      <h2 id="block-dialog-title" data-title-new="Add block" data-title-edit="Edit block">Add block</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <label class="field"><span class="field-label">Title</span><span class="control"><input name="title" maxlength="160" required placeholder="e.g. LinkedIn outreach block"></span></label>
      <div class="field mt-14">
        <span class="field-label">Category</span>
        <div class="pick">
          <label><input type="radio" name="category_id" value=""><span class="pill c-gray">None</span></label>
          <?php foreach (categories() as $c): ?>
            <label><input type="radio" name="category_id" value="<?= (int) $c['id'] ?>"><span class="pill c-<?= e($c['color']) ?>"><?= icon($c['icon'], 14) ?><?= e($c['name']) ?></span></label>
          <?php endforeach ?>
        </div>
      </div>
      <div class="form-grid form-grid-3 mt-14">
        <label class="field"><span class="field-label">Night of</span><span class="control"><input type="date" name="day" required></span></label>
        <label class="field"><span class="field-label">Start</span><span class="control"><input type="time" name="start" step="900" required></span></label>
        <label class="field"><span class="field-label">End</span><span class="control"><input type="time" name="end" step="900" required></span><span class="field-help">Past midnight is fine.</span></label>
      </div>
      <label class="field"><span class="field-label">Note</span><span class="control"><input name="note" maxlength="255" placeholder="e.g. 12 new, 4 follow-ups"></span></label>
      <label class="row toggles"><input class="switch" type="checkbox" name="done"><span>Done (counts toward focus hours)</span></label>
    </div>
    <div class="dialog-foot">
      <button class="btn btn-quiet btn-danger" type="button" data-delete data-confirm="This block will be removed from your schedule." hidden><?= icon('trash-2', 16) ?>Delete</button>
      <span class="spacer"></span>
      <button class="btn" type="button" data-action="close-dialog">Cancel</button>
      <button class="btn btn-primary" type="submit">Save</button>
    </div>
  </form>
</dialog>

<dialog class="dialog-sm" id="availability-dialog" aria-labelledby="availability-title">
  <form class="dialog-form" data-api="settings/availability" data-success="Availability saved"
        data-defaults="<?= e(json_encode(['work_start' => setting('work_start', '18:00'), 'work_end' => setting('work_end', '02:00')])) ?>">
    <div class="dialog-head">
      <h2 id="availability-title">Availability</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <p class="muted">Your work window in Pacific time. The schedule maker only fills this window, and the week grid shows it.</p>
      <div class="form-grid mt-14">
        <label class="field"><span class="field-label">Starts</span><span class="control"><input type="time" name="work_start" step="900" required></span></label>
        <label class="field"><span class="field-label">Ends</span><span class="control"><input type="time" name="work_end" step="900" required></span><span class="field-help">Past midnight is fine.</span></label>
      </div>
    </div>
    <div class="dialog-foot"><span class="spacer"></span><button class="btn" type="button" data-action="close-dialog">Cancel</button><button class="btn btn-primary" type="submit">Save</button></div>
  </form>
</dialog>
