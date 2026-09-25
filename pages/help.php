<?php
declare(strict_types=1);
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome">Help and support</h2>
    <p>How the dashboard works, in short</p>
  </div>
</div>
<div class="help">
  <section class="card"><?= card_head('clock', 'Your day') ?><div class="panel">
    <p>Everything follows the app clock (US Pacific by default). A task you finish at 3 AM in Depalpur still counts for the same Pacific workday. The top bar shows both clocks.</p>
  </div></section>
  <section class="card"><?= card_head('square-check-big', 'Tasks') ?><div class="panel">
    <ul>
      <li>Board: drag a card to another column to change its status, or drag it up and down to reorder.</li>
      <li>Timeline: drag a bar to move its dates, drag an edge to change the start or due date.</li>
      <li>Calendar: click a day to add a task due that day.</li>
      <li>Add a checklist inside any task. Progress follows the ticked items.</li>
    </ul>
  </div></section>
  <section class="card"><?= card_head('calendar-days', 'Schedule maker') ?><div class="panel">
    <p>Generate schedule fills free time in your work window for the next 7 nights: goal study blocks first, then tasks that are important and urgent, then by due date. Suggestions show dashed. Accept them, drag them, or dismiss them.</p>
  </div></section>
  <section class="card"><?= card_head('target', 'Goal wizard') ?><div class="panel">
    <p>New goal asks for the total (for example 64 hours over 9 courses), a finish date or a daily amount, rest days and an optional 10% buffer. It works out the daily pace, dates for each part and can add a daily block to Schedule. No API key needed.</p>
  </div></section>
  <section class="card"><?= card_head('circle-dollar-sign', 'Money') ?><div class="panel">
    <ul>
      <li>Income is in USD. Daily costs can be logged in rupees; each entry keeps the exchange rate from that day.</li>
      <li>Buying Connects adds them to your balance. Marking a buy list item bought adds it to Expenses.</li>
      <li>Late invoices show on the Overview. Remind adds a follow-up task for today.</li>
    </ul>
  </div></section>
  <section class="card"><?= card_head('keyboard', 'Keyboard') ?><div class="panel keys">
    <kbd>⌘ K</kbd><span>Search tasks, leads, goals and pages (also <kbd>/</kbd>)</span>
    <kbd>N</kbd><span>Add a task from anywhere</span>
    <kbd>Esc</kbd><span>Close a dialog or menu, or cancel a drag</span>
    <kbd>Enter</kbd><span>Open the focused card</span>
  </div></section>
</div>
