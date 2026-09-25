<?php
declare(strict_types=1);

$inPkr = ($_GET['cur'] ?? '') === 'PKR';
$show = fn (float $usd) => $inPkr ? pkr(round($usd * rate())) : usd(round($usd));
$link = fn (array $change) => url('expenses', array_filter(array_merge(['cur' => $inPkr ? 'PKR' : null], $change), fn ($v) => $v !== null));
$spend = spend_month();
$months = spend_by_month(6);
$entries = expenses_list(month_start(), month_end());
$byKind = array_count_values(array_column($entries, 'kind'));
$renewals = renewals();
$connectsLeft = (int) setting('connects_left', '0');
$connectsBought = (int) val("SELECT COALESCE(SUM(quantity), 0) FROM expenses WHERE kind = 'connects' AND spent_on BETWEEN ? AND ?", [month_start(), month_end()]);
$connectsBuys = (int) val("SELECT COUNT(*) FROM expenses WHERE kind = 'connects' AND spent_on BETWEEN ? AND ?", [month_start(), month_end()]);
$toolNames = array_column(array_filter($entries, fn ($x) => $x['kind'] === 'tools'), 'title');
$monthName = now()->format('F');
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome">Expenses</h2>
    <p><?= e($monthName) ?>: <?= e(usd(round($spend['total']))) ?> spent · Rs <?= e(num(rate())) ?> to the dollar · daily costs logged in rupees</p>
  </div>
  <div class="actions">
    <div class="seg"><a class="<?= $inPkr ? '' : 'is-active' ?>" href="<?= e($link(['cur' => null])) ?>">USD</a><a class="<?= $inPkr ? 'is-active' : '' ?>" href="<?= e($link(['cur' => 'PKR'])) ?>">PKR</a></div>
    <a class="btn" href="<?= e(url('export', ['type' => 'expenses'])) ?>" data-no-prerender download><?= icon('arrow-up-right', 16) ?>Export</a>
    <button class="btn btn-primary" type="button" data-open="expense-dialog"><?= icon('plus', 16) ?>Add expense</button>
  </div>
</div>

<div class="grid grid-main">
  <section class="card">
    <?= card_head('wallet', 'Spent in ' . $monthName, '<span class="meta">1 to ' . e(now()->format('j M')) . '</span>') ?>
    <div class="panel income-panel">
      <div class="row">
        <span class="t-display"><?= e($show($spend['total'])) ?></span>
        <span class="muted"><?= $inPkr ? e(usd(round($spend['total']))) : e(pkr(round($spend['total'] * rate()))) ?></span>
        <?= trend_chip($spend['change'], signed_pct($spend['change']) . ' vs ' . day(month_start())->modify('-1 month')->format('F'), false) ?>
      </div>
      <div class="split-bar">
        <?php foreach (['daily', 'connects', 'tools', 'other'] as $kind): ?>
          <?php if ($spend[$kind] > 0): ?><i class="c-<?= EXPENSE_COLORS[$kind] ?>" style="flex-grow: <?= round($spend[$kind], 2) ?>" data-tip="<?= e('<b>' . e(EXPENSE_KINDS[$kind]) . '</b> ' . e(usd(round($spend[$kind])))) ?>"></i><?php endif ?>
        <?php endforeach ?>
      </div>
      <div class="kinds">
        <?php
        $notes = [
            'daily' => pkr(round($spend['daily_pkr'])) . ' · groceries, bills, fuel',
            'connects' => number_format($connectsBought) . ' Connects in ' . plural($connectsBuys, 'buy'),
            'tools' => $toolNames ? implode(', ', array_slice($toolNames, 0, 3)) : 'No tools this month',
        ];
        ?>
        <?php foreach (['daily', 'connects', 'tools'] as $kind): ?>
          <div class="kind">
            <span class="cat-tile c-<?= EXPENSE_COLORS[$kind] ?>"><?= icon(EXPENSE_ICONS[$kind], 16) ?></span>
            <span><small><?= e(EXPENSE_KINDS[$kind]) ?></small><b><?= e($show($spend[$kind])) ?> <em><?= pct($spend[$kind], $spend['total']) ?>%</em></b><small class="truncate"><?= e($notes[$kind]) ?></small></span>
          </div>
        <?php endforeach ?>
      </div>
      <p class="t-caps">Last 6 months</p>
      <?php
      $bars = [];
      $last = array_key_last($months);
      foreach ($months as $label => $x) {
          $bars[] = [
              'label' => $label . '  ' . usd(round($x['total'])),
              'segments' => [[$x['total'], $label === $last ? 'seg-ink' : 'seg-steel', 'Spent']],
              'tip' => '<b>' . e($label) . '</b>' . implode('', array_map(fn ($k) => '<div class="tip-row">' . e(EXPENSE_KINDS[$k]) . '<span>' . e(usd(round($x[$k]))) . '</span></div>', ['daily', 'connects', 'tools'])),
          ];
      }
      echo bar_chart($bars, ['height' => 110, 'ticks' => 2]);
      ?>
    </div>
  </section>

  <section class="card">
    <?= card_head('zap', 'Upwork Connects', '<button class="link" type="button" data-open="connects-dialog">Update</button>') ?>
    <div class="panel">
      <p class="connects-left"><b><?= $connectsLeft ?></b> <span class="muted">left, about <?= e(plural(intdiv($connectsLeft, 6), 'proposal')) ?></span></p>
      <?php $segments = 12; $on = $connectsBought ? (int) round($connectsLeft / max($connectsLeft, $connectsBought) * $segments) : 0; ?>
      <div class="counter-bar connects-bar<?= $connectsLeft < 20 ? ' is-low' : '' ?>" style="--n:<?= $segments ?>"><?php for ($i = 0; $i < $segments; $i++): ?><i<?= $i < max(1, $on) && $connectsLeft ? ' class="on"' : '' ?>></i><?php endfor ?></div>
      <p class="meta mt-8"><?= $connectsBought ? 'Used ' . number_format(max(0, $connectsBought - $connectsLeft)) . ' of ' . number_format($connectsBought) . ' bought this month · about 6 per proposal' : 'No Connects bought this month' ?></p>
      <button class="btn btn-block mt-14" type="button" data-buy-connects><?= icon('zap', 16) ?>Buy <?= CONNECTS_PACK[0] ?> Connects · <?= e(usd(CONNECTS_PACK[1])) ?></button>
      <hr class="mt-16">
      <p class="t-caps waiting-label">Renewing soon</p>
      <?php if (!$renewals): ?><p class="muted">No renewals in the next 30 days.</p><?php endif ?>
      <?php foreach ($renewals as $r): ?>
        <div class="renewal">
          <span class="date-tile"><small><?= e(day($r['renews_on'])->format('M')) ?></small><b><?= e(day($r['renews_on'])->format('j')) ?></b></span>
          <span class="truncate"><?= e($r['title']) ?></span>
          <b class="money"><?= e(money($r['amount'], $r['currency'])) ?></b>
        </div>
      <?php endforeach ?>
    </div>
  </section>
</div>

<section class="card section-gap" data-filter-scope>
  <?php
  $tools = '<div class="seg seg-sm"><button class="is-active" type="button" data-filter="">All ' . count($entries) . '</button>';
  foreach (['daily' => 'Daily', 'connects' => 'Connects', 'tools' => 'Tools', 'other' => 'Other'] as $k => $label) {
      if (!empty($byKind[$k])) {
          $tools .= '<button type="button" data-filter="' . $k . '">' . $label . ' ' . $byKind[$k] . '</button>';
      }
  }
  echo card_head('list-todo', 'All expenses', $tools . '</div>');
  ?>
  <div class="panel panel-flush">
    <?php if (!$entries): ?>
      <?= empty_state('wallet', 'No expenses this month', 'Log daily costs in rupees and tools in dollars.', '<button class="btn btn-sm" type="button" data-open="expense-dialog">Add expense</button>') ?>
    <?php else: ?>
    <div class="table-scroll">
      <table class="tbl tbl-fixed">
        <thead><tr><th class="w-120">Date</th><th>Item</th><th class="w-140">Category</th><th class="w-140">Paid with</th><th class="w-120 num">Amount</th><th class="col-menu"><span class="sr-only">Edit</span></th></tr></thead>
        <tbody>
        <?php foreach ($entries as $x): ?>
          <?php
          $usdAmount = to_usd($x['amount'], $x['currency'], $x['rate']);
          $json = json_encode(['id' => (int) $x['id'], 'title' => $x['title'], 'kind' => $x['kind'], 'amount' => (float) $x['amount'], 'currency' => $x['currency'],
              'spent_on' => $x['spent_on'], 'quantity' => $x['quantity'] ?? '', 'paid_with' => $x['paid_with'], 'renews_on' => (string) $x['renews_on'], 'note' => $x['note']], JSON_UNESCAPED_UNICODE);
          ?>
          <tr data-cat="<?= e($x['kind']) ?>" data-id="<?= (int) $x['id'] ?>" data-record="<?= e($json) ?>">
            <td><?= e(day($x['spent_on'])->format('D j M')) ?></td>
            <td><div class="cell-title"><?= e($x['title']) ?></div><?php if ($x['note'] || $x['quantity']): ?><div class="cell-sub"><?= e($x['note'] ?: number_format((int) $x['quantity']) . ' Connects') ?></div><?php endif ?></td>
            <td><span class="row"><span class="cat-tile cat-tile-sm c-<?= EXPENSE_COLORS[$x['kind']] ?>"><?= icon(EXPENSE_ICONS[$x['kind']], 14) ?></span><?= e(ucfirst($x['kind'])) ?></span></td>
            <td class="muted"><?= e($x['paid_with'] ?: 'Not set') ?></td>
            <td class="num money"><b><?= e(usd(round($usdAmount, 2))) ?></b><div class="cell-sub"><?= e(pkr(round($usdAmount * (float) $x['rate']))) ?></div></td>
            <td class="col-menu"><button class="icon-btn icon-btn-sm" type="button" data-edit="expense-dialog" aria-label="Edit"><?= icon('pencil', 16) ?></button></td>
          </tr>
        <?php endforeach ?>
        </tbody>
        <tfoot><tr><td colspan="2"><b>Total</b></td><td colspan="2" class="meta"><?= e(plural(count($entries), 'expense')) ?> this month</td><td class="num money"><b><?= e(usd(round($spend['total'], 2))) ?></b></td><td></td></tr></tfoot>
      </table>
    </div>
    <?php endif ?>
  </div>
</section>
<?= partial('expense-dialogs', ['connectsLeft' => $connectsLeft]) ?>
