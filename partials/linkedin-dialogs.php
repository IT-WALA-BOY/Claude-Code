<dialog id="lead-dialog" aria-labelledby="lead-title">
  <form class="dialog-form" data-api="linkedin/save" data-delete-api="linkedin/delete" data-success="Lead saved"
        data-defaults="<?= e(json_encode(['market' => 'US', 'stage' => 'shortlisted', 'next_followup' => add_days(today(), 1)])) ?>">
    <input type="hidden" name="id">
    <div class="dialog-head">
      <h2 id="lead-title" data-title-new="Add lead" data-title-edit="Edit lead">Add lead</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <div class="form-grid">
        <label class="field"><span class="field-label">Name</span><span class="control"><input name="name" maxlength="120" required placeholder="e.g. Jake Morrison"></span></label>
        <label class="field"><span class="field-label">Company</span><span class="control"><input name="company" maxlength="160" placeholder="e.g. Morrison Family Dental"></span></label>
      </div>
      <div class="form-grid mt-14">
        <div class="field"><span class="field-label">Market</span><div class="pick">
          <?php foreach (LI_MARKETS as $m): ?><label><input type="radio" name="market" value="<?= e($m) ?>"><span class="pill c-gray"><?= e($m) ?></span></label><?php endforeach ?>
        </div></div>
        <label class="field"><span class="field-label">Industry</span><span class="control"><input name="industry" maxlength="60" list="industries" placeholder="e.g. Medical"></span></label>
      </div>
      <datalist id="industries"><option value="Medical"><option value="SaaS"><option value="B2B"><option value="Service"><option value="E-commerce"></datalist>
      <label class="field"><span class="field-label">Why the site looks neglected</span><span class="control"><input name="why_neglected" maxlength="255" placeholder="e.g. No reviews above the fold"></span></label>
      <div class="form-grid mt-14">
        <label class="field"><span class="field-label">Stage</span><span class="control control-select"><select name="stage">
          <?php foreach (LI_STAGES as $key => $label): ?><option value="<?= e($key) ?>"><?= e($label) ?></option><?php endforeach ?>
        </select></span></label>
        <label class="field"><span class="field-label">Next follow-up</span><span class="control"><input type="date" name="next_followup"></span></label>
      </div>
      <div class="form-grid mt-14">
        <label class="field"><span class="field-label">Deal value (USD)</span><span class="control"><span class="control-prefix">$</span><input name="value" inputmode="decimal" placeholder="0"></span></label>
        <label class="field"><span class="field-label">Profile link</span><span class="control"><input type="url" name="profile_url" maxlength="500" placeholder="https://www.linkedin.com/in/..."></span></label>
      </div>
    </div>
    <div class="dialog-foot">
      <button class="btn btn-quiet btn-danger" type="button" data-delete data-confirm="This lead will be deleted." hidden><?= icon('trash-2', 16) ?>Delete</button>
      <span class="spacer"></span>
      <button class="btn" type="button" data-action="close-dialog">Cancel</button>
      <button class="btn btn-primary" type="submit">Save</button>
    </div>
  </form>
</dialog>

<dialog id="post-dialog" aria-labelledby="post-title">
  <form class="dialog-form" data-api="linkedin/post" data-delete-api="linkedin/post_delete" data-success="Post saved"
        data-defaults="<?= e(json_encode(['status' => 'draft', 'post_on' => today()])) ?>">
    <input type="hidden" name="id">
    <div class="dialog-head">
      <h2 id="post-title" data-title-new="Add post" data-title-edit="Edit post">Add post</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <label class="field"><span class="field-label">Topic</span><span class="control"><input name="topic" maxlength="200" required placeholder="e.g. 3 fixes for a DTC checkout"></span></label>
      <div class="form-grid mt-14">
        <div class="field"><span class="field-label">Status</span><div class="pick">
          <label><input type="radio" name="status" value="draft"><span class="pill tone-bg-warning">Draft</span></label>
          <label><input type="radio" name="status" value="scheduled"><span class="pill tone-bg-muted">Scheduled</span></label>
          <label><input type="radio" name="status" value="posted"><span class="pill tone-bg-positive">Posted</span></label>
        </div></div>
        <label class="field"><span class="field-label">Date</span><span class="control"><input type="date" name="post_on"></span></label>
      </div>
      <label class="field"><span class="field-label">Post link</span><span class="control"><input type="url" name="url" maxlength="500" placeholder="https://www.linkedin.com/posts/..."></span></label>
      <div class="form-grid mt-14">
        <label class="field"><span class="field-label">Views</span><span class="control"><input type="number" min="0" name="impressions"></span></label>
        <label class="field"><span class="field-label">Reactions</span><span class="control"><input type="number" min="0" name="reactions"></span></label>
      </div>
    </div>
    <div class="dialog-foot">
      <button class="btn btn-quiet btn-danger" type="button" data-delete data-confirm="This post will be deleted." hidden><?= icon('trash-2', 16) ?>Delete</button>
      <span class="spacer"></span>
      <button class="btn" type="button" data-action="close-dialog">Cancel</button>
      <button class="btn btn-primary" type="submit">Save</button>
    </div>
  </form>
</dialog>
