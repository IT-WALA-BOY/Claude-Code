<?php /** @var ?array $goal */ ?>
<dialog class="dialog-sm" id="log-dialog" aria-labelledby="log-title">
  <form class="dialog-form" data-api="goals/log" data-success="Progress logged">
    <input type="hidden" name="goal_id">
    <div class="dialog-head">
      <h2 id="log-title">Log progress</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <div class="form-grid">
        <label class="field"><span class="field-label">Amount<?= $goal ? ' (' . e($goal['unit']) . ')' : '' ?></span><span class="control"><input name="amount" inputmode="decimal" required placeholder="1.5"></span></label>
        <label class="field"><span class="field-label">Date</span><span class="control"><input type="date" name="logged_on" required></span></label>
      </div>
      <label class="field"><span class="field-label">Note</span><span class="control"><input name="note" maxlength="255" placeholder="e.g. Lesson 4, quiz done"></span></label>
    </div>
    <div class="dialog-foot"><span class="spacer"></span><button class="btn" type="button" data-action="close-dialog">Cancel</button><button class="btn btn-primary" type="submit">Save</button></div>
  </form>
</dialog>

<dialog class="dialog-wide wizard" id="goal-wizard" aria-labelledby="wizard-title">
  <form class="dialog-form" data-api="goals/save" data-success="Goal saved" data-after="none" novalidate>
    <input type="hidden" name="id">
    <input type="hidden" name="done">
    <div class="dialog-head wizard-head">
      <div>
        <h2 id="wizard-title" data-title-new="New goal" data-title-edit="Edit plan">New goal</h2>
        <p class="muted">Answer a few questions and the dashboard breaks it into daily steps.</p>
      </div>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <ol class="steps" aria-label="Steps">
      <li data-step-dot="1"><span>1</span>Goal</li>
      <li data-step-dot="2"><span>2</span>Pace</li>
      <li data-step-dot="3"><span>3</span>Review</li>
    </ol>
    <div class="dialog-body wizard-body">
      <div class="wizard-form">
        <section data-step="1">
          <label class="field"><span class="field-label">What do you want to finish?</span><span class="control"><?= icon('target', 16) ?><input name="title" maxlength="160" required placeholder="e.g. CXL mini degree"></span></label>
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
            <label class="field"><span class="field-label">Total</span><span class="control"><input name="target" inputmode="decimal" required placeholder="64"></span></label>
            <label class="field"><span class="field-label">Measured in</span><span class="control"><input name="unit" maxlength="20" list="goal-units" required placeholder="h"></span></label>
            <label class="field"><span class="field-label">Parts</span><span class="control"><input name="part_count" type="number" min="0" max="40" inputmode="numeric" placeholder="9"></span></label>
          </div>
          <datalist id="goal-units"><option value="h"><option value="screens"><option value="pages"><option value="posts"><option value="lessons"><option value="reviews"></datalist>
          <p class="field-help mt-8">Use "h" for hours. Parts are courses, chapters or screens, done in order.</p>
          <div class="field mt-14" data-parts-field>
            <span class="field-label">Name the parts</span>
            <div class="parts-edit" data-list="parts"></div>
            <template data-row-for="parts">
              <div class="pe" data-row>
                <input type="text" data-key="title" maxlength="160" placeholder="Part name">
                <input type="text" data-key="target" inputmode="decimal" aria-label="Size">
                <input type="hidden" data-key="done">
                <button class="icon-btn icon-btn-xs" type="button" data-remove-row aria-label="Remove part"><?= icon('x', 14) ?></button>
              </div>
            </template>
            <div class="row">
              <button class="btn btn-quiet btn-sm" type="button" data-add-row="parts"><?= icon('plus', 16) ?>Add part</button>
              <?php if (ai_enabled()): ?>
                <button class="btn btn-sm" type="button" data-ai-split><?= icon('sparkles', 16) ?>Ask Claude to split it</button>
              <?php endif ?>
            </div>
          </div>
        </section>

        <section data-step="2" hidden>
          <p class="t-section">How should we pace it?</p>
          <div class="options mt-8">
            <label class="option"><input type="radio" name="mode" value="date"><span><b>Finish by a date</b><small>We work out the amount per day for you.</small></span></label>
            <label class="option"><input type="radio" name="mode" value="daily"><span><b>Fixed amount per day</b><small>We work out the finish date for you.</small></span></label>
          </div>
          <div class="form-grid mt-14">
            <label class="field" data-mode="date"><span class="field-label">Finish by</span><span class="control"><input type="date" name="due_on"></span><span class="field-help" data-starts></span></label>
            <label class="field" data-mode="daily" hidden><span class="field-label">Per day</span><span class="control"><input name="per_day" inputmode="decimal" placeholder="1.5"><span class="control-prefix" data-unit-label>h</span></span></label>
            <label class="field"><span class="field-label">Rest days</span><span class="control control-select"><select name="rest_days"><option value="0">None, every day</option><option value="1">1 a week (Sunday)</option><option value="2">2 a week (weekend)</option></select></span></label>
          </div>
          <label class="toggle-card mt-14"><span><b>Add a 10% buffer</b><small>Leaves room for client work spilling over.</small></span><input class="switch" type="checkbox" name="buffer"></label>
        </section>

        <section data-step="3" hidden>
          <p class="t-section">Check the plan</p>
          <p class="muted mt-8">Save it as it is, or go back and change the pace. You can edit the plan any time.</p>
          <dl class="review" data-review></dl>
        </section>
      </div>

      <aside class="plan" aria-live="polite">
        <p class="t-caps plan-label"><?= icon('sparkles', 16) ?>Your plan</p>
        <p class="plan-big"><b data-plan-big>0</b> <span data-plan-per>a day</span></p>
        <p class="meta" data-plan-sub>Fill in the total to see the plan.</p>
        <hr>
        <p class="plan-parts-title">Sub-goals we will create</p>
        <ul class="plan-parts" data-plan-parts></ul>
        <label class="toggle-card plan-block" data-block-toggle><span><b data-block-text>Add a daily block to Schedule</b></span><input class="switch" type="checkbox" name="add_block"></label>
      </aside>
    </div>
    <div class="dialog-foot">
      <button class="btn btn-quiet" type="button" data-back><?= icon('chevron-left', 16) ?>Back</button>
      <span class="spacer"></span>
      <button class="btn" type="button" data-action="close-dialog">Cancel</button>
      <button class="btn btn-primary" type="button" data-next>Next<?= icon('chevron-right', 16) ?></button>
      <button class="btn btn-primary" type="submit" data-save hidden>Save plan</button>
    </div>
  </form>
</dialog>
