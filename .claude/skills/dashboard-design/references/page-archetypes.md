# Page archetypes — measured from the Al Qafla build

All screens: frame 1440 wide (min height 1024, height hugs), `bg/canvas`, Sidebar 264 (fill height), Main padding 28 / 32 / 32 / 32 with gap 24 (content width 1112). Cards: `bg/surface`, 1px `border/default`, radius `radius/lg` (16), `Shadow/Card`, padding 24. Table cards use padding 0, clip content, and a head row padded 20/24. Side columns are 320–368 wide. Two-column body gap is 24.

Header row (every screen): SPACE_BETWEEN, centred. Left: optional back Icon Button (Secondary) · `Title/Page` · `Meta/Regular` muted context line (question · branch · period or freshness). Right: search (≥ 280) · notification button (clone from the flagship) · Secondary actions · **one Primary**.

---

## 1. Overview (flagship)
Built by `/dashboard-design-system` → `dashboard-recipe.md`. Everything below must not copy its hero/position/attention composition.

## 2. Balances list — e.g. Ledgers (clients owe us / we owe vendors)
**Question:** who owes money, and how much?
1. **Balance overview card** (horizontal, gap 32):
   - **Left block** (fixed ~620): label row ("Clients owe us" · "9 clients · 1 refund due") · `Title/Display` total + Negative badge with the risky slice ("PKR 62,000 older than 60 days") · aging bar (8h, three segments in `icon/secondary` / `text/warning` / `text/negative`, 2px gaps, widths proportional) · legend with 6px dots.
   - **Divider:** 1px vertical.
   - **Right block** (fill): "We owe vendors" `Title/Stat` plus the top 3 dues (name `Body/Medium`, "· Due 18 Sep" `Meta` muted, amount `Body/Semibold` right).
2. **Table card**:
   - **Head:** title + sub-line explaining the ordering rule · tabs (`Clients owe us | We owe vendors`) · an "Outstanding only" **checkbox** (neutral, not an accent chip) · filter Icon Button.
   - **Columns:** Party (two-line: name, phone · bookings) 292 · Last activity (two-line: date-time, what · who) 210 · Charges 140 R · Received 140 R · Balance 140 R (Balance Owed; Balance Credit for negatives) · Aging badge 130 · actions (fill).
   - **Totals footer:** "Totals · 7 of 9 clients" plus column sums.
   - **Pagination row:** "Showing 1–7 of 9 clients · positive balance = client owes us".
**Pitfalls:** tab icons turn orange when selected (hide them) · keep the placeholder short.

## 3. Drawer over list — e.g. Client statement
Clone the list screen, then add an absolute Scrim (`bg/scrim`, full frame) and an absolute **Drawer** (720 wide, full height, right edge, `bg/surface`, left hairline, `Shadow/Menu`):
1. **Head** (padding 20/24, bottom hairline): name `Title/Section` + Negative badge "Owes PKR 180,000" · sub-line (statement type · phone · since · branch) · Quiet Icon Buttons: print, history, close.
2. **Summary strip** (`bg/subtle`, 4 equal blocks: Opening · Charged · Received · Balance, value `Title/Section`; only Balance is red).
3. **Toolbar:** tabs "All lines · Charges · Payments" + Secondary Small date-range button.
4. **Lines table:** Date (two-line) 150 · Particulars (two-line: what — who, detail · by whom) fill · Debit 92 R · Credit 92 R · Balance 104 R. **Running balances are neutral `Amount`**, not red. Footer: "Closing balance · 6 lines".
5. **Spacer** (fill), then the **Foot** (top hairline): ordering rule `Meta` muted · Secondary "Print statement" + **Primary "Record receipt"**.

## 4. Form with sticky summary — e.g. New booking
**Question:** record this sale correctly, fast.
- **Header:** back button · "New booking" · "Ticketing · branch · draft autosaved 2 min ago" · Quiet "Discard" + Secondary "Save draft" (no primary here).
- **Type tabs** under the header (Ticketing · Umrah package · Visa · …).
- **Body:** form column (fill, gap 24) + summary column (336).
  - **Field cards:** head row = title + `Meta` hint on the right. Fields in 3-column rows (gap 16), using Field instances with the calendar icon for dates, Select for pick-lists, and Required on key pickers.
  - **Repeating rows editor** (e.g. passengers):
    - Head: title + live count ("4 passengers · 2 adults, 1 child, 1 infant") + Secondary Small "Add passenger".
    - Label row (`Label/Small` muted) over input rows of compact 36h inputs (radius md, `border/strong`, mono for IDs, right-aligned amounts) + a Quiet remove button.
    - Hint line explaining copy behaviour.
  - **Payment-now card:** disabled fields show *why* ("Not needed for cash"), plus helper text for constraints.
  - **Summary card:** title + Draft badge · party block · divider · label/value lines (vendor cost, client charge, profit in `text/positive`, received) · divider · "Client still owes on this booking" `Title/Stat` · Status select · **full-width Primary "Save and print invoice"** · shortcut hint.

## 5. Multi-section builder — e.g. Umrah package
**Question:** what does this package consist of, and what does it cost?
- **Header:** back · name + status badge in the title row · a sub-line with id, package type, who created it and when services were added · Secondary "Print summary" + **Primary "Update booking"**.
- **Body:** section rail (208) · content (fill) · totals column (320).
  - **Rail:** a card with padding 8. Each item is a 20px mark + name + meta:
    - Done: `bg/positive-soft` circle with a 12px check.
    - Current: `bg/inverse` circle with a white dot; row `bg/muted`.
    - Not taken: dashed `border/strong` circle, muted text.
    - **Neutral, not accent.**
  - **Services card:** 4 equal tiles. Included = `border/strong` + Positive badge "Included" + "Added <date> by <who>". Not taken = dashed border, `bg/subtle`, Secondary Small "Add". Meta text must wrap.
  - **Repeating sub-cards** (hotel legs): `bg/subtle` card with a city badge, name, "via vendor · covers 6 of 6 passengers", leg total right with cost below, then a facts strip (white, hairline-divided cells: Check-in · Nights · Room · Charge · Cost).
  - **Passengers table:** two-line name · passport · type badge · per-person total.
  - **Totals column:** lines per service → divider → total charge / vendor payable / profit / received → divider → "Balance due" `Title/Stat` → Secondary "Record receipt". Second card: payable by vendor.

## 6. Master-detail list — e.g. Vendors
**Question:** who do we buy from, and what do we owe them?
- **Header:** search + Secondary "Add vendor" (the primary lives in the detail panel).
- **Table card (fill):**
  - Head: title + "7 active · 4 shared" · tabs Active / Inactive · filter.
  - Columns: Party (two-line: name, sharing scope · accounts count) 264 · Contact (two-line) 180 · Cost 108 R · We owe 108 R (Balance Owed; `0` as plain Amount) · actions.
  - **Selected row:** cells filled `bg/muted`.
- **Detail panel (352):**
  - Head: name + Tag "Shared", sub-line (FILL + wrap), Quiet close.
  - "We owe · Lahore share" `Title/Stat` + due line.
  - Tabs: Ledger · Bookings · Bank accounts.
  - Account tiles: 32 bank tile, bank `Body/Semibold` + "Default" badge, account title, IBAN in `Mono/Regular`.
  - Last payment row (top hairline, history icon).
  - **Primary "Pay vendor"** (full width), then Secondary "Open full ledger".

## 7. Accounts / balances — e.g. Bank accounts
**Question:** where did money come in and go out?
- **Cash position card** (horizontal, gap 40):
  - Left: "Total balance · 4 accounts" · `Title/Display` + positive trend chip · two mini stats (Money in / Money out, each with its composition line).
  - Right (456 wide): weekly paired bars (in = `icon/secondary`, out = `border/strong`, **current week's in-bar = `bg/accent`**, legend on top, week labels below, baseline hairline).
- **Account cards row:** 4 equal cards, **equalised heights**.
  - Top: 32 tile, name (wraps), type Tag.
  - IBAN in mono (or a location line for cash).
  - "Balance" + `Title/Stat`.
  - "Last movement …" meta.
  - **Card balances must sum to the total.**
- **Movements table:**
  - Columns: Date 150 · Movement (two-line: kind — party, method · handled by whom) fill · Account (two-line: bank, •••• last 4) 220 · Reference 110 · In 120 R · Out 120 R (neutral, em dash when empty) · actions.
  - Footer: "Totals" | "6 of 48 movements shown" (don't cram the label into a 150 column).

## 8. Bills and spend — e.g. Expenses
**Question:** what does the branch spend, and which bills are due?
- **Header:** notifications · Secondary month picker · Secondary "Set up a bill" · **Primary "Add expense"**.
- **Row 1:**
  - **Spend card (fill):** "Spent in <month> · 23 expenses" · `Title/Display` + "vs last month · +7.8%" · stacked split bar (paid `icon/secondary` / unpaid `text/warning` / overdue `text/negative`) · legend blocks with amounts.
  - **"Due next" card (368):** rows with a **date tile** (40×40 hairline box: day `Body/Semibold`, month `Meta/Medium` muted) · name · status line coloured by severity · Secondary Small verb (Pay / Mark paid / Enter bill).
- **Recurring items grid:** a card with 3 × 2 tiles: icon tile · name + provider/due day · status badge · amount `Title/Section` + usual/paid line · Secondary Small verb only when action is needed.
- **Table:** tabs with counts ("All 23 · Paid 17 · Unpaid 4 · Overdue 1") · category dropdown · filter.
  - Columns: Date (two-line with "by") · Expense (two-line with payment detail) · Category · Due (red text when overdue) · Amount R · Status badge · actions.
  - Totals footer and pagination.

## 9. Reports
**Question:** how did we do over this period?
- **Header:** period tabs (This month · Last month · This quarter · FY · Custom) · **Primary "Export report"**.
- **Row 1:**
  - **Net result card (fill):** label + formula hint · `Title/Display` + trend chip + comparison.
    - **Formula strip:** `bg/subtle`, hairline, terms separated by muted operators — Client charges − Vendor cost = Gross profit − Expenses = Net profit. It teaches the maths.
    - Monthly bars (6 months, `bg/muted`; **current month `bg/accent`**, value labels on top).
  - **Contribution card (352):** by staff — avatar initials, name + role/bookings, value + share %, 6px neutral share bar. A note on permission limits.
- **Row 2:**
  - **Breakdown table:** category (two-line with counts) · charges · cost · gross profit · margin. Footer rows: Gross profit totals → "Less branch expenses −184,600" → "Net profit". Tabs "grouped / split".
  - **Exports card (352):** rows with a download icon, name + row count, and a Quiet Small "Download".

## 10. Administration — staff and permissions
**Question:** who can use the system, and who did what?
- **Header:** tabs Staff · Branches · Hotels · Activity · Settings · **Primary "Add staff"**.
- **Table (fill):**
  - Columns: Person (two-line: name, @username in the sub-line) · Role badge (Neutral; Branch Admin as Tag) · Permissions ("14 of 28") · Records (amount) · Last sign-in (two-line) · Status badge (Active Positive / Deactivated Neutral) · actions.
  - **Selected row:** `bg/muted`.
- **Permissions panel (368):**
  - Person head with a Quiet close.
  - Grouped checkbox lists (Records · Money · Ledgers & reports · Expenses), each group labelled `Label/Small` muted, 2 columns.
  - Sensitive permissions (profit.view, payments.void) carry a one-line consequence.
  - "Changes apply on their next click" note · Secondary "Save permissions" · Quiet "Sign in as Ayesha" (Super Admin only).
- **Activity card below the panel:** 3 entries (time · readable summary · who · field change).

## 11. Sign in
**Question:** who are you?
- Frame 1440 × 900, no sidebar. Split: form column 560 (`bg/surface`, padding 80, vertically centred) + showcase panel (fill, `bg/canvas`).
- **Form column:**
  - Brand lockup (40 mark).
  - "Sign in" `Title/Page` + one-line purpose.
  - Username and Password Fields.
  - Checkbox "Keep me signed in on this computer" + a note about shared computers.
  - **Full-width Primary "Sign in"**.
  - `Meta` "Forgot your password? Ask your branch admin to reset it."
  - Footer meta with branches.
- **Showcase panel:** the flagship dashboard cloned at ~0.55 scale with `Shadow/Menu`, clipped at the panel edge, plus a one-line promise in `Title/Section` (e.g. "Every rupee traceable — who booked it, who collected it, where it went").
- **Error variant:** clone the frame, add an Alert (Error) above the fields ("That username and password don't match. Try again or ask your branch admin."), and set the Password Field state to Error.

---

## Content slots cheat-sheet (reuse for any domain)
| Archetype | Finance back-office | SaaS admin | E-commerce admin |
|---|---|---|---|
| Balances list | Client / vendor ledgers | Invoices & dunning | Supplier payables |
| Drawer over list | Statement | Customer billing history | Order timeline |
| Form + sticky summary | New booking | New subscription / quote | Manual order |
| Builder with rail | Umrah package | Onboarding / workflow builder | Product with variants |
| Master-detail | Vendors | Integrations / API keys | Suppliers |
| Accounts | Bank accounts | Payout accounts | Wallets & gateways |
| Bills & spend | Expenses | Cloud costs | Operating expenses |
| Reports | Profit reports | Revenue & retention | Sales & margin |
| Admin | Staff & permissions | Members & roles | Staff & roles |
