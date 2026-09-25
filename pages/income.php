<?php
declare(strict_types=1);

$inPkr = ($_GET['cur'] ?? '') === 'PKR';
$show = fn (float $usd) => $inPkr ? pkr(round($usd * rate())) : usd(round($usd, 2));
$showShort = fn (float $usd) => $inPkr ? 'Rs ' . num($usd * rate() / 1000) . 'k' : usd_short($usd);
$monthParam = DateTimeImmutable::createFromFormat('!Y-m', (string) ($_GET['month'] ?? ''));
$month = $monthParam ?: day(month_start());
$mFrom = $month->format('Y-m-01');
$mTo = $month->format('Y-m-t');
$isThisMonth = $mFrom === month_start();
$link = fn (array $change) => url('income', array_filter(array_merge(['cur' => $inPkr ? 'PKR' : null, 'month' => $isThisMonth ? null : $month->format('Y-m')], $change), fn ($v) => $v !== null));

$m = income_month();
$year = (int) now()->format('Y');
$byMonth = income_by_month($year);
$lastYear = income_by_month($year - 1);
$thisMonthNum = (int) now()->format('n');
$yearTotal = array_sum(array_map(fn ($x) => $x['upwork'] + $x['direct'], $byMonth));
$lastYearSame = array_sum(array_map(fn ($x) => $x['upwork'] + $x['direct'], array_slice($lastYear, 0, $thisMonthNum, true)));
$yearChange = $lastYearSame > 0 ? ($yearTotal - $lastYearSame) / $lastYearSame * 100 : null;
$best = 0;
$bestMonth = null;
foreach ($byMonth as $n => $x) {
    if ($x['upwork'] + $x['direct'] > $best) {
        $best = $x['upwork'] + $x['direct'];
        $bestMonth = $n;
    }
}
$split = income_between($mFrom, $mTo);
$open = income_open();
$entries = income_list($mFrom, $mTo);
$bySource = array_count_values(array_column($entries, 'source'));
$received = array_sum(array_map(fn ($r) => $r['status'] === 'paid' ? to_usd($r['amount'], $r['currency'], $r['rate']) : 0, $entries));
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome">Income</h2>
    <p><?= e(now()->format('F')) ?>: <?= e($show($m['total'])) ?> of <?= e($show($m['goal'])) ?> · <?= e(plural($m['days_left'], 'day')) ?> left · months follow Pacific time</p>
  </div>
  <div class="actions">
    <div class="seg"><a class="<?= $inPkr ? '' : 'is-active' ?>" href="<?= e($link(['cur' => null])) ?>">USD</a><a class="<?= $inPkr ? 'is-active' : '' ?>" href="<?= e($link(['cur' => 'PKR'])) ?>">PKR</a></div>
    <a class="btn" href="<?= e(url('export', ['type' => 'income'])) ?>" data-no-prerender download><?= icon('arrow-up-right', 16) ?>Export</a>
    <button class="btn btn-primary" type="button" data-open="income-dialog"><?= icon('plus', 16) ?>Log income</button>
  </div>
</div>

<div class="grid grid-main">
  <section class="card">
    <?= card_head('chart-column', 'Earned in ' . $year, '<span class="meta">Jan to ' . e(now()->format('M')) . '</span>') ?>
    <div class="panel income-panel">
      <div class="row">
        <span class="t-display"><?= e($show($yearTotal)) ?></span>
        <?= trend_chip($yearChange, signed_pct($yearChange) . ' vs ' . ($year - 1), true) ?>
        <?php if ($bestMonth): ?><span class="meta">Best month: <?= e(date('F', mktime(0, 0, 0, $bestMonth, 1))) ?>, <?= e($show($best)) ?></span><?php endif ?>
      </div>
      <?php
      $bars = [];
      foreach ($byMonth as $n => $x) {
          $total = $x['upwork'] + $x['direct'];
          $name = date('M', mktime(0, 0, 0, $n, 1));
          $isNow = $n === $thisMonthNum;
          $bars[] = [
              'label' => $name,
              'segments' => [[$total, $isNow ? 'seg-accent' : 'seg-steel', 'Earned']],
              'state' => $n > $thisMonthNum ? 'future' : '',
              'tip' => $n > $thisMonthNum ? null : '<b>' . e($name . ($isNow ? ' so far' : '')) . '</b><div class="tip-row">Upwork<span>' . e($show($x['upwork'])) . '</span></div><div class="tip-row">Direct<span>' . e($show($x['direct'])) . '</span></div>' . ($isNow ? '<div class="tip-row">To go<span>' . e($show($m['left'])) . '</span></div>' : ''),
          ];
      }
      echo bar_chart($bars, ['line' => [income_goal(), usd_short(income_goal()) . ' monthly goal'], 'height' => 200, 'format' => $showShort]);
      ?>
    </div>
  </section>

  <section class="card">
    <?= card_head('circle-dollar-sign', 'Where it came from', '<span class="meta">' . e($month->format('F')) . '</span>') ?>
    <div class="panel">
      <div class="donut-row">
        <?= donut([[$split['upwork'], 'var(--chart-green)', 'Upwork'], [$split['direct'], 'var(--blue-text)', 'Direct clients']], 148, 18, $inPkr ? 'Rs ' . num($split['total'] * rate() / 1000) . 'k' : usd($split['total']), 'received') ?>
        <ul class="donut-legend">
          <li><i class="dot legend-upwork"></i><span>Upwork</span><b><?= e($show($split['upwork'])) ?> <small><?= pct($split['upwork'], $split['total']) ?>%</small></b></li>
          <li><i class="dot legend-blue"></i><span>Direct clients</span><b><?= e($show($split['direct'])) ?> <small><?= pct($split['direct'], $split['total']) ?>%</small></b></li>
        </ul>
      </div>
      <p class="t-caps waiting-label">Waiting on</p>
      <?php if (!$open): ?><p class="muted">Nothing pending. Every invoice is paid.</p><?php endif ?>
      <?php foreach ($open as $inv): ?>
        <?php $late = $inv['status'] === 'pending' && $inv['due_on'] && $inv['due_on'] < today(); ?>
        <div class="waiting" data-id="<?= (int) $inv['id'] ?>">
          <span class="waiting-text"><b class="truncate"><?= e($inv['client'] . ($inv['title'] ? ', ' . $inv['title'] : '')) ?></b>
            <small><?= e($show(to_usd($inv['amount'], $inv['currency'], $inv['rate']))) ?> · <?= $inv['status'] === 'escrow' ? 'in escrow' : 'sent ' . e(fmt_day($inv['received_on'])) ?><?= $inv['due_on'] ? ' · due ' . e(fmt_day($inv['due_on'])) : '' ?></small></span>
          <?php if ($late): ?>
            <?= status_badge(plural(days_between($inv['due_on'], today()), 'day') . ' late', 'negative') ?>
            <button class="btn btn-sm" type="button" data-remind>Remind</button>
          <?php else: ?>
            <?= status_badge($inv['status'] === 'escrow' ? 'Funded' : 'Sent', 'muted') ?>
          <?php endif ?>
          <button class="btn btn-sm" type="button" data-paid>Mark paid</button>
        </div>
      <?php endforeach ?>
    </div>
  </section>
</div>

<section class="card section-gap" data-filter-scope>
  <?php
  $tools = '<div class="range-nav range-nav-sm"><a class="icon-btn icon-btn-xs" href="' . e($link(['month' => $month->modify('-1 month')->format('Y-m')])) . '" aria-label="Previous month">' . icon('chevron-left', 16) . '</a>'
      . '<a class="icon-btn icon-btn-xs" href="' . e($link(['month' => $month->modify('+1 month')->format('Y-m')])) . '" aria-label="Next month">' . icon('chevron-right', 16) . '</a></div>'
      . '<div class="seg seg-sm"><button class="is-active" type="button" data-filter="">All ' . count($entries) . '</button>'
      . '<button type="button" data-filter="upwork">Upwork ' . ($bySource['upwork'] ?? 0) . '</button><button type="button" data-filter="direct">Direct ' . ($bySource['direct'] ?? 0) . '</button></div>';
  echo card_head('list-todo', $month->format('F Y') . ' income', $tools);
  ?>
  <div class="panel panel-flush">
    <?php if (!$entries): ?>
      <?= empty_state('circle-dollar-sign', 'No income this month yet', 'Log a payment when it lands.', '<button class="btn btn-sm" type="button" data-open="income-dialog">Log income</button>') ?>
    <?php else: ?>
    <div class="table-scroll">
      <table class="tbl tbl-fixed">
        <thead><tr><th class="w-120">Date</th><th>Client and project</th><th class="w-100">Source</th><th class="w-140">Status</th><th class="w-140 num">Amount</th><th class="col-menu"><span class="sr-only">Edit</span></th></tr></thead>
        <tbody>
        <?php foreach ($entries as $r): ?>
          <?php
          $late = $r['status'] === 'pending' && $r['due_on'] && $r['due_on'] < today();
          [$statusText, $statusTone] = match ($r['status']) {
              'paid' => ['Paid', 'positive'],
              'escrow' => ['In escrow', 'muted'],
              default => $late ? [plural(days_between($r['due_on'], today()), 'day') . ' late', 'negative'] : ['Invoice sent', 'warning'],
          };
          $json = json_encode(['id' => (int) $r['id'], 'client' => $r['client'], 'title' => $r['title'], 'source' => $r['source'], 'amount' => (float) $r['amount'],
              'currency' => $r['currency'], 'status' => $r['status'], 'received_on' => $r['received_on'], 'due_on' => (string) $r['due_on']], JSON_UNESCAPED_UNICODE);
          ?>
          <tr data-cat="<?= e($r['source']) ?>" data-id="<?= (int) $r['id'] ?>" data-record="<?= e($json) ?>">
            <td><?= e(day($r['received_on'])->format('D j M')) ?></td>
            <td><div class="cell-title"><?= e($r['client']) ?></div><?php if ($r['title']): ?><div class="cell-sub"><?= e($r['title']) ?></div><?php endif ?></td>
            <td><span class="pill <?= $r['source'] === 'upwork' ? 'c-green' : 'c-blue' ?>"><?= e(INCOME_SOURCES[$r['source']]) ?></span></td>
            <td><?= status_badge($statusText, $statusTone) ?></td>
            <td class="num money strong<?= $late ? ' tone-negative' : '' ?>"><?= e($r['currency'] === 'PKR' && !$inPkr ? pkr($r['amount']) : $show(to_usd($r['amount'], $r['currency'], $r['rate']))) ?></td>
            <td class="col-menu"><button class="icon-btn icon-btn-sm" type="button" data-edit="income-dialog" aria-label="Edit"><?= icon('pencil', 16) ?></button></td>
          </tr>
        <?php endforeach ?>
        </tbody>
        <tfoot><tr><td colspan="2"><b>Received</b></td><td colspan="2" class="meta">Unpaid invoices are not counted</td><td class="num money"><b><?= e($show($received)) ?></b></td><td></td></tr></tfoot>
      </table>
    </div>
    <?php endif ?>
  </div>
</section>
<?= partial('income-dialog') ?>
