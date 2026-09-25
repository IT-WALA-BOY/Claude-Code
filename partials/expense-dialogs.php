<?php /** @var int $connectsLeft */ ?>
<dialog id="expense-dialog" aria-labelledby="expense-title">
  <form class="dialog-form" data-api="expenses/save" data-delete-api="expenses/delete" data-success="Expense saved"
        data-defaults="<?= e(json_encode(['spent_on' => today(), 'kind' => 'daily', 'currency' => 'PKR'])) ?>">
    <input type="hidden" name="id">
    <div class="dialog-head">
      <h2 id="expense-title" data-title-new="Add expense" data-title-edit="Edit expense">Add expense</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <label class="field"><span class="field-label">What for</span><span class="control"><input name="title" maxlength="160" required placeholder="e.g. Groceries, weekly"></span></label>
      <div class="field mt-14">
        <span class="field-label">Category</span>
        <div class="pick">
          <?php foreach (EXPENSE_KINDS as $key => $label): ?>
            <label><input type="radio" name="kind" value="<?= e($key) ?>"><span class="pill c-<?= e(EXPENSE_COLORS[$key]) ?>"><?= icon(EXPENSE_ICONS[$key], 14) ?><?= e($label) ?></span></label>
          <?php endforeach ?>
        </div>
      </div>
      <div class="form-grid form-grid-3 mt-14">
        <label class="field"><span class="field-label">Amount</span><span class="control"><input name="amount" inputmode="decimal" required placeholder="0"></span></label>
        <label class="field"><span class="field-label">Currency</span><span class="control control-select"><select name="currency"><option value="PKR">PKR</option><option value="USD">USD</option></select></span></label>
        <label class="field"><span class="field-label">Date</span><span class="control"><input type="date" name="spent_on" required></span></label>
      </div>
      <div class="form-grid form-grid-3 mt-14">
        <label class="field"><span class="field-label">Paid with</span><span class="control"><input name="paid_with" maxlength="40" list="paid-with" placeholder="JazzCash"></span></label>
        <label class="field"><span class="field-label">Connects bought</span><span class="control"><input name="quantity" type="number" min="0" placeholder="Only for Connects"></span></label>
        <label class="field"><span class="field-label">Renews on</span><span class="control"><input type="date" name="renews_on"></span></label>
      </div>
      <datalist id="paid-with"><option value="JazzCash"><option value="Easypaisa"><option value="Cash"><option value="Bank transfer"><option value="Card"><option value="Upwork balance"></datalist>
      <label class="field"><span class="field-label">Note</span><span class="control"><input name="note" maxlength="255" placeholder="e.g. Imtiaz Depalpur"></span></label>
    </div>
    <div class="dialog-foot">
      <button class="btn btn-quiet btn-danger" type="button" data-delete data-confirm="This expense will be deleted." hidden><?= icon('trash-2', 16) ?>Delete</button>
      <span class="spacer"></span>
      <button class="btn" type="button" data-action="close-dialog">Cancel</button>
      <button class="btn btn-primary" type="submit">Save</button>
    </div>
  </form>
</dialog>

<dialog class="dialog-sm" id="connects-dialog" aria-labelledby="connects-title">
  <form class="dialog-form" data-api="expenses/connects" data-success="Connects updated" data-defaults="<?= e(json_encode(['connects_left' => $connectsLeft])) ?>">
    <div class="dialog-head">
      <h2 id="connects-title">Connects left</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <p class="muted">Copy the number from Upwork. The dashboard warns you below 20.</p>
      <label class="field mt-14"><span class="field-label">Connects left</span><span class="control"><input name="connects_left" type="number" min="0" max="10000" required></span></label>
    </div>
    <div class="dialog-foot"><span class="spacer"></span><button class="btn" type="button" data-action="close-dialog">Cancel</button><button class="btn btn-primary" type="submit">Save</button></div>
  </form>
</dialog>
