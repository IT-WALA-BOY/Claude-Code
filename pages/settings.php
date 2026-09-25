<?php
declare(strict_types=1);

const TIMEZONES = [
    'America/Los_Angeles' => 'US Pacific (Los Angeles)',
    'America/Denver' => 'US Mountain (Denver)',
    'America/Chicago' => 'US Central (Chicago)',
    'America/New_York' => 'US Eastern (New York)',
    'Europe/London' => 'UK (London)',
    'Asia/Dubai' => 'Gulf (Dubai)',
    'Asia/Karachi' => 'Pakistan (Depalpur)',
];
const CATEGORY_ICONS = ['megaphone', 'briefcase-business', 'graduation-cap', 'figma', 'pen-tool', 'code', 'book-open', 'camera', 'dumbbell', 'house', 'heart', 'lightbulb', 'music', 'palette', 'plane', 'shopping-bag', 'star', 'tag', 'users'];

$me = admin();
$targets = li_targets();
$failed24 = (int) val('SELECT COUNT(*) FROM login_attempts WHERE attempted_at > ?', [date('Y-m-d H:i:s', time() - 86400)]);
$lastSignIn = setting('last_sign_in');
$hasKey = setting('anthropic_key') !== '';
$sections = [
    'time' => ['Time and currency', 'clock'],
    'targets' => ['Targets', 'target'],
    'availability' => ['Availability', 'calendar-days'],
    'categories' => ['Categories', 'tag'],
    'ai' => ['AI assistant', 'sparkles'],
    'account' => ['Account and security', 'users'],
    'data' => ['Data', 'database'],
];
$categoryRows = array_map(fn ($c) => ['id' => (int) $c['id'], 'name' => $c['name'], 'color' => $c['color'], 'icon' => $c['icon']], categories());
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome">Preferences</h2>
    <p>One admin account · changes apply to every screen</p>
  </div>
  <div class="actions">
    <button class="btn" type="reset" form="prefs"><?= icon('rotate-ccw', 16) ?>Discard</button>
    <button class="btn btn-primary" type="submit" form="prefs"><?= icon('check', 16) ?>Save changes</button>
  </div>
</div>

<div class="settings">
  <nav class="card settings-nav" aria-label="Settings sections">
    <?php foreach ($sections as $id => [$label, $ic]): ?>
      <a class="menu-item" href="#<?= e($id) ?>"><?= icon($ic, 16) ?><?= e($label) ?></a>
    <?php endforeach ?>
  </nav>

  <div class="stack">
    <form id="prefs" class="stack" data-api="settings/save" data-success="Settings saved">
      <section class="card" id="time">
        <?= card_head('clock', 'Time and currency') ?>
        <div class="panel">
          <p class="meta">The dashboard runs on US Pacific time, so a task finished at 3 AM in Depalpur still counts for the same workday.</p>
          <div class="form-grid mt-14">
            <label class="field"><span class="field-label">App time zone</span><span class="control control-select"><?= icon('clock', 16) ?><select name="timezone">
              <?php foreach (TIMEZONES as $tz => $label): ?><option value="<?= e($tz) ?>"<?= setting('timezone') === $tz ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach ?>
            </select></span><span class="field-help">Today, due dates and monthly totals follow this.</span></label>
            <label class="field"><span class="field-label">Second clock</span><span class="control control-select"><?= icon('clock', 16) ?><select name="second_timezone">
              <?php foreach (TIMEZONES as $tz => $label): ?><option value="<?= e($tz) ?>"<?= setting('second_timezone') === $tz ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach ?>
            </select></span><span class="field-help">Shown in the top bar and on Schedule.</span></label>
          </div>
          <div class="form-grid form-grid-3 mt-14">
            <label class="field"><span class="field-label">Main currency</span><span class="control"><?= icon('circle-dollar-sign', 16) ?><input value="USD" disabled></span><span class="field-help">Income, Connects, tools.</span></label>
            <label class="field"><span class="field-label">Local currency</span><span class="control"><?= icon('wallet', 16) ?><input value="PKR" disabled></span><span class="field-help">Daily costs and the buy list.</span></label>
            <label class="field"><span class="field-label">Exchange rate</span><span class="control"><?= icon('circle-dollar-sign', 16) ?><input name="pkr_rate" inputmode="decimal" value="<?= e(setting('pkr_rate', '282')) ?>" required><span class="control-prefix">Rs</span></span><span class="field-help">Rupees per dollar. Old entries keep their own rate.</span></label>
          </div>
        </div>
      </section>

      <section class="card" id="targets">
        <?= card_head('target', 'Targets') ?>
        <div class="panel">
          <div class="form-grid">
            <label class="field"><span class="field-label">Monthly income goal</span><span class="control"><?= icon('circle-dollar-sign', 16) ?><input name="income_goal" inputmode="decimal" value="<?= e(setting('income_goal', '10000')) ?>" required><span class="control-prefix">USD</span></span><span class="field-help">Drives the Overview and Income screens.</span></label>
            <label class="field"><span class="field-label">Call me</span><span class="control"><?= icon('users', 16) ?><input name="greeting_name" maxlength="40" value="<?= e(greeting_name()) ?>"></span><span class="field-help">Used in greetings.</span></label>
          </div>
          <p class="t-section mt-16">LinkedIn, every day</p>
          <div class="form-grid form-grid-4 mt-8">
            <?php foreach ([...array_keys(LI_METRICS), 'posts'] as $m): ?>
              <label class="field"><span class="field-label"><?= e(LI_METRICS[$m] ?? 'Posts') ?></span><span class="control"><?= icon(LI_METRIC_ICONS[$m], 16) ?><input type="number" min="0" max="100" name="li_<?= e($m) ?>" value="<?= (int) $targets[$m] ?>"></span></label>
            <?php endforeach ?>
          </div>
        </div>
      </section>

      <section class="card" id="availability">
        <?= card_head('calendar-days', 'Availability') ?>
        <div class="panel">
          <div class="form-grid">
            <label class="field"><span class="field-label">Work starts (Pacific)</span><span class="control"><input type="time" name="work_start" step="900" value="<?= e(setting('work_start', '18:00')) ?>" required></span></label>
            <label class="field"><span class="field-label">Work ends</span><span class="control"><input type="time" name="work_end" step="900" value="<?= e(setting('work_end', '02:00')) ?>" required></span><span class="field-help">Past midnight is fine. The schedule maker fills only this window.</span></label>
          </div>
        </div>
      </section>

      <section class="card" id="ai">
        <?= card_head('sparkles', 'AI assistant') ?>
        <div class="panel">
          <p class="meta">Optional. Leave it empty and the goal wizard and schedule maker still work on their own. With a key, the goal wizard can split a goal into parts for you.</p>
          <label class="field mt-14"><span class="field-label">Anthropic API key</span><span class="control"><?= icon('sparkles', 16) ?><input type="password" name="anthropic_key" autocomplete="off" placeholder="<?= $hasKey ? 'Saved. Type a new key to replace it' : 'Not set' ?>"></span><span class="field-help">Stored on your server only, never sent to the browser.</span></label>
          <?php if ($hasKey): ?>
            <label class="check-row"><input class="check" type="checkbox" name="remove_key"><span>Remove the saved key</span></label>
          <?php endif ?>
        </div>
      </section>
    </form>

    <section class="card" id="categories">
      <?= card_head('tag', 'Categories', '<span class="meta">Colors show on tasks, the timeline and the schedule</span>') ?>
      <form class="panel" data-api="settings/categories" data-success="Categories saved">
        <div class="cat-edit" data-list="categories"></div>
        <template data-row-for="categories">
          <div class="ce" data-row>
            <input type="hidden" data-key="id">
            <input type="text" data-key="name" maxlength="60" placeholder="Category name" aria-label="Name" required>
            <select data-key="color" aria-label="Color"><?php foreach (CATEGORY_COLORS as $c): ?><option value="<?= e($c) ?>"><?= e(ucfirst($c)) ?></option><?php endforeach ?></select>
            <select data-key="icon" aria-label="Icon"><?php foreach (CATEGORY_ICONS as $ic): ?><option value="<?= e($ic) ?>"><?= e(str_replace('-', ' ', $ic)) ?></option><?php endforeach ?></select>
            <button class="icon-btn icon-btn-xs" type="button" data-remove-row aria-label="Delete category"><?= icon('trash-2', 14) ?></button>
          </div>
        </template>
        <script type="application/json" data-categories><?= json_encode($categoryRows, JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
        <div class="row-between mt-14">
          <button class="btn btn-quiet btn-sm" type="button" data-add-row="categories"><?= icon('plus', 16) ?>Add category</button>
          <button class="btn btn-sm" type="submit">Save categories</button>
        </div>
        <p class="field-help mt-8">Deleting a category keeps its tasks, without a category.</p>
      </form>
    </section>

    <section class="card" id="account">
      <?= card_head('users', 'Account and security') ?>
      <div class="panel">
        <div class="row-between">
          <span class="row"><span class="avatar"><?= e(initials($me['name'])) ?></span><span class="min-0"><b><?= e($me['name']) ?></b><small class="meta">Username: <?= e($me['username']) ?></small></span></span>
          <button class="btn btn-sm" type="button" data-open="password-dialog">Change password</button>
        </div>
        <dl class="facts">
          <dt>Last sign in</dt><dd><?= $lastSignIn ? e(fmt_day(substr($lastSignIn, 0, 10)) . ', ' . fmt_time($lastSignIn) . ' Pacific') : 'Not recorded yet' ?></dd>
          <dt>Failed attempts</dt><dd><?= status_badge($failed24 . ' in the last 24 h', $failed24 ? 'warning' : 'positive') ?></dd>
          <dt>Lockout</dt><dd><?= LOGIN_LOCK_MINUTES ?> min after <?= LOGIN_MAX_TRIES ?> wrong tries</dd>
        </dl>
      </div>
    </section>

    <section class="card" id="data">
      <?= card_head('database', 'Data') ?>
      <div class="panel">
        <p class="meta">Download a backup any time. For a full restore, export and import the database in phpMyAdmin.</p>
        <div class="actions mt-14">
          <a class="btn btn-sm" href="<?= e(url('export', ['type' => 'backup'])) ?>" data-no-prerender download><?= icon('database', 16) ?>Full backup (JSON)</a>
          <a class="btn btn-sm" href="<?= e(url('export', ['type' => 'income'])) ?>" data-no-prerender download><?= icon('circle-dollar-sign', 16) ?>Income (CSV)</a>
          <a class="btn btn-sm" href="<?= e(url('export', ['type' => 'expenses'])) ?>" data-no-prerender download><?= icon('wallet', 16) ?>Expenses (CSV)</a>
        </div>
      </div>
    </section>
  </div>
</div>

<dialog class="dialog-sm" id="password-dialog" aria-labelledby="password-title">
  <form class="dialog-form" data-api="settings/password" data-success="Password changed" data-after="none">
    <div class="dialog-head">
      <h2 id="password-title">Change password</h2>
      <button class="icon-btn icon-btn-sm" type="button" data-action="close-dialog" aria-label="Cancel"><?= icon('x', 18) ?></button>
    </div>
    <div class="dialog-body">
      <label class="field"><span class="field-label">Current password</span><span class="control"><input type="password" name="current" autocomplete="current-password" required></span></label>
      <label class="field"><span class="field-label">New password</span><span class="control"><input type="password" name="new" autocomplete="new-password" minlength="10" required></span><span class="field-help">At least 10 characters.</span></label>
      <label class="field"><span class="field-label">Repeat new password</span><span class="control"><input type="password" name="repeat" autocomplete="new-password" minlength="10" required></span></label>
    </div>
    <div class="dialog-foot"><span class="spacer"></span><button class="btn" type="button" data-action="close-dialog">Cancel</button><button class="btn btn-primary" type="submit">Save</button></div>
  </form>
</dialog>
