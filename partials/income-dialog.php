<dialog id="income-dialog" aria-labelledby="income-dialog-title">
  <form class="dialog-form" data-api="income/save" data-delete-api="income/delete" data-success="Income saved"
        data-defaults="<?= e(json_encode(['received_on' => today(), 'source' => 'upwork', 'currency' => 'USD', 'status' => 'paid'])) ?>">
    <input type="hidden" name="id">
    <div class="dialog-head">
      <h2 id="income-dialog-title" data-title-new="Log income" data-title-edit="Edit income">Log income</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <div class="form-grid">
        <label class="field"><span class="field-label">Client</span><span class="control"><input name="client" maxlength="120" required placeholder="e.g. Qualitas"></span></label>
        <label class="field"><span class="field-label">For</span><span class="control"><input name="title" maxlength="160" placeholder="e.g. Milestone 4"></span></label>
      </div>
      <div class="field mt-14">
        <span class="field-label">Source</span>
        <div class="pick">
          <label><input type="radio" name="source" value="upwork"><span class="pill c-green"><?= icon('briefcase-business', 14) ?>Upwork</span></label>
          <label><input type="radio" name="source" value="direct"><span class="pill c-blue"><?= icon('handshake', 14) ?>Direct client</span></label>
        </div>
      </div>
      <div class="form-grid mt-14">
        <label class="field"><span class="field-label">Amount</span><span class="control"><input name="amount" inputmode="decimal" required placeholder="0"></span></label>
        <label class="field"><span class="field-label">Currency</span><span class="control control-select"><select name="currency"><option value="USD">USD</option><option value="PKR">PKR</option></select></span></label>
      </div>
      <div class="field mt-14">
        <span class="field-label">Status</span>
        <div class="pick">
          <label><input type="radio" name="status" value="paid"><span class="pill tone-bg-positive">Paid</span></label>
          <label><input type="radio" name="status" value="pending"><span class="pill tone-bg-warning">Invoice sent</span></label>
          <label><input type="radio" name="status" value="escrow"><span class="pill tone-bg-accent">In escrow</span></label>
        </div>
      </div>
      <div class="form-grid mt-14">
        <label class="field"><span class="field-label">Date (paid or invoiced)</span><span class="control"><input type="date" name="received_on" required></span></label>
        <label class="field"><span class="field-label">Payment due</span><span class="control"><input type="date" name="due_on"></span><span class="field-help">Only for invoices and escrow.</span></label>
      </div>
    </div>
    <div class="dialog-foot">
      <button class="btn btn-quiet btn-danger" type="button" data-delete data-confirm="This income entry will be deleted." hidden><?= icon('trash-2', 16) ?>Delete</button>
      <span class="spacer"></span>
      <button class="btn" type="button" data-action="close-dialog">Cancel</button>
      <button class="btn btn-primary" type="submit">Save</button>
    </div>
  </form>
</dialog>
