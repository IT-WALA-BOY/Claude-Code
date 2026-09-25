<?php
declare(strict_types=1);

$items = buy_items();
$byQuad = array_fill_keys(array_keys(BUY_QUADRANTS), []);
foreach ($items as $it) {
    $byQuad[($it['important'] ? 'i' : 'n') . ($it['urgent'] ? 'u' : 'n')][] = $it;
}
$pkrOf = fn (array $it) => $it['currency'] === 'PKR' ? (float) $it['est_cost'] : (float) $it['est_cost'] * rate();
$totalPkr = array_sum(array_map($pkrOf, $items));
$bought = buy_bought_since(month_start());
$itemJson = fn (array $it) => json_encode(['id' => (int) $it['id'], 'title' => $it['title'], 'note' => $it['note'], 'est_cost' => (float) $it['est_cost'],
    'currency' => $it['currency'], 'important' => (bool) $it['important'], 'urgent' => (bool) $it['urgent']], JSON_UNESCAPED_UNICODE);
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome">What to buy next</h2>
    <p><?= e(plural(count($items), 'item')) ?> · <?= e(pkr(round($totalPkr))) ?> (<?= e(usd(round($totalPkr / rate()))) ?>) for everything · drag items between boxes as priorities change</p>
  </div>
  <button class="btn btn-primary" type="button" data-open="buy-dialog"><?= icon('plus', 16) ?>Add item</button>
</div>

<div class="matrix" data-matrix>
  <span class="axis axis-top">Urgent</span><span class="axis axis-top">Not urgent</span>
  <span class="axis axis-side axis-important">Important</span><span class="axis axis-side axis-not">Not important</span>
  <?php foreach (['iu', 'in', 'nu', 'nn'] as $q): ?>
    <?php [$imp, $urg, $title, $sub, $ic, $color] = BUY_QUADRANTS[$q]; $list = $byQuad[$q]; ?>
    <section class="quad c-<?= e($color) ?> quad-<?= e($q) ?>" data-important="<?= $imp ?>" data-urgent="<?= $urg ?>">
      <header class="quad-head">
        <?= icon($ic, 18) ?>
        <span class="quad-title"><b><?= e($title) ?></b><small><?= e($sub) ?></small></span>
        <span class="quad-total"><b data-quad-total><?= e(pkr(round(array_sum(array_map($pkrOf, $list))))) ?></b><small data-quad-count><?= e(plural(count($list), 'item')) ?></small></span>
      </header>
      <div class="quad-list">
        <?php foreach ($list as $it): ?>
          <article class="buy" data-id="<?= (int) $it['id'] ?>" data-record="<?= e($itemJson($it)) ?>" data-pkr="<?= round($pkrOf($it)) ?>" tabindex="0">
            <?= icon('grip-vertical', 16, 'buy-grip') ?>
            <span class="buy-text"><span><b><?= e($it['title']) ?></b> <small class="money"><?= e(money($it['est_cost'], $it['currency'])) ?></small></span><?php if ($it['note']): ?><small class="truncate"><?= e($it['note']) ?></small><?php endif ?></span>
            <button class="btn btn-sm" type="button" data-bought><?= icon('check', 16) ?>Mark bought</button>
            <button class="icon-btn icon-btn-sm" type="button" data-edit="buy-dialog" aria-label="Edit item"><?= icon('ellipsis', 18) ?></button>
          </article>
        <?php endforeach ?>
        <button class="kcol-add" type="button" data-drop-end data-open="buy-dialog" data-fill="<?= e(json_encode(['important' => (bool) $imp, 'urgent' => (bool) $urg])) ?>"><?= icon('plus', 16) ?>Add item</button>
      </div>
    </section>
  <?php endforeach ?>
</div>

<div class="notice section-gap">
  <?= icon('circle-check', 16, 'tone-positive') ?>
  <span class="grow">
    <?php if ($bought): ?>
      Bought this month: <?= e(implode(', ', array_map(fn ($b) => $b['title'] . ', ' . money($b['est_cost'], $b['currency']) . ' on ' . fmt_day(substr($b['bought_at'], 0, 10)), array_slice($bought, 0, 3)))) ?>.
    <?php endif ?>
    Marking an item bought adds it to Expenses automatically.
  </span>
  <a class="link" href="<?= e(url('expenses')) ?>">View in Expenses<?= icon('chevron-right', 16) ?></a>
</div>

<dialog id="buy-dialog" aria-labelledby="buy-title">
  <form class="dialog-form" data-api="buy/save" data-delete-api="buy/delete" data-success="Item saved" data-defaults="<?= e(json_encode(['currency' => 'PKR'])) ?>">
    <input type="hidden" name="id">
    <div class="dialog-head">
      <h2 id="buy-title" data-title-new="Add item" data-title-edit="Edit item">Add item</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <label class="field"><span class="field-label">Item</span><span class="control"><input name="title" maxlength="160" required placeholder="e.g. UPS battery"></span></label>
      <label class="field"><span class="field-label">Why</span><span class="control"><input name="note" maxlength="255" placeholder="e.g. Load shedding cuts night shifts short"></span></label>
      <div class="form-grid mt-14">
        <label class="field"><span class="field-label">Estimated cost</span><span class="control"><input name="est_cost" inputmode="decimal" placeholder="0"></span></label>
        <label class="field"><span class="field-label">Currency</span><span class="control control-select"><select name="currency"><option value="PKR">PKR</option><option value="USD">USD</option></select></span></label>
      </div>
      <div class="row toggles">
        <label class="row"><input class="switch" type="checkbox" name="important"><span>Important</span></label>
        <label class="row"><input class="switch" type="checkbox" name="urgent"><span>Urgent</span></label>
      </div>
    </div>
    <div class="dialog-foot">
      <button class="btn btn-quiet btn-danger" type="button" data-delete data-confirm="This item will be removed from the list." hidden><?= icon('trash-2', 16) ?>Delete</button>
      <span class="spacer"></span>
      <button class="btn" type="button" data-action="close-dialog">Cancel</button>
      <button class="btn btn-primary" type="submit">Save</button>
    </div>
  </form>
</dialog>
