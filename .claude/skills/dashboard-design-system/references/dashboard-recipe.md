# The flagship dashboard: measurement by measurement

This is the layout the Al Qafla owner called "extremely high end". It is built only from tokens and library instances; the chart is drawn with `areaChart()` from helpers.js.

## 0. Before drawing: fill in this sentence
**"<Viewer> opens this screen to see <the one question> and to act on <what needs attention>."**
Map the slots:

| Slot | Al Qafla (travel back-office) | SaaS example | E-commerce admin example |
|---|---|---|---|
| Hero metric | Gross profit, last 30 days | MRR | Net revenue |
| Hero sub-metrics (4) | Net after expenses · Client charges · Margin · Bookings | New MRR · Churned · ARPA · Active accounts | Orders · AOV · Refunds · Conversion |
| Position card (3 blocks) | Receivable (aging bar) · Vendor payable · Collected today | Cash in bank · Burn · Runway | Pending payouts · Inventory value · Returns in transit |
| Main table | Recent bookings | Newest accounts / invoices | Latest orders |
| Needs attention | Overdue bill · Bill due · Refund to pay | Failed payments · Expiring trials · Open tickets | Low stock · Delayed shipments · Chargebacks |
| Breakdown | Profit by service | Revenue by plan | Revenue by category |

## 1. Frame
`Dashboard · <Viewer> · 1440`: horizontal auto layout, width 1440, fill `bg/canvas`, min height 1024.
Children: **Sidebar** (264, fill container height) · **Main** (fill, vertical, padding 32, gap 24).

## 2. Sidebar (264 wide, `bg/surface`, right hairline)
Vertical, padding 20 top/bottom and 16 sides, gap 24.
1. **Brand row** (SPACE_BETWEEN): mark 32×32 radius 8 `bg/accent` with a white 18px brand icon · name `Title/Brand` + tagline `Meta/Regular` muted · Quiet Icon Button `sidebar` in `icon/muted`.
2. **Workspace / branch switcher**: row padding 8 / 10, radius md, `bg/surface`, `border/default`, `Shadow/Card`; 32 tile radius 6 `bg/muted` with an icon · name `Body/Semibold` + sub-line `Meta/Regular` muted · `caret-up-down` muted. Hide it for single-workspace products.
3. **Navigation** (gap 20 between groups, gap 2 within a group):
   - First group has no label: `Dashboard` (Active).
   - Labelled groups: label `Label/Small` muted with 10 left padding and 6 bottom padding (e.g. Sales · Money · Office · Admin).
   - Counts on the right: neutral `bg/muted` + `text/secondary`; **one** urgent count on `bg/accent-soft` + `text/accent` ("2 due").
4. **Spacer** (fill).
5. **Footer**: `Settings` Nav Item, then the user row. The user row has a top hairline and 16 top padding: 32 avatar round `bg/accent-soft` with initials `Meta/Semibold` `text/accent` · name `Body/Semibold` + role `Meta/Regular` muted · `caret-up-down`.

## 3. Header row (Main, SPACE_BETWEEN, centre aligned)
- **Left**: greeting `Title/Page` ("Good afternoon, Bilal") and a context line `Meta/Regular` muted ("Sunday, 13 September 2026 · Lahore Office").
- **Right** (gap 8):
  - Search field 280 wide, 36h, `bg/surface`, `border/default`, radius md, `search` icon, placeholder "Search clients, PNR, ticket no.", and a trailing `⌘K` key chip (`bg/subtle`, hairline, radius 4, `Meta/Medium`).
  - Secondary Icon Button `bell` with an 8px `bg/accent` dot at its top-right.
  - Secondary Button with `calendar` icon, "Last 30 days".
  - **Primary Button** with `plus` icon: "New booking" (the only primary on the screen).

## 4. Row 1: performance (horizontal, gap 24)
### 4a. Hero card (fill ≈ 2/3): `bg/surface`, `border/default`, radius lg, `Shadow/Card`, padding 24, gap 20
- **Head** (SPACE_BETWEEN): title `Title/Section` "Gross profit" + sub-line `Meta/Regular` muted "Client charges − vendor cost · last 30 days" · Segmented Control Daily / Weekly / Monthly.
- **Figure row** (gap 12, centre aligned): `Title/Display` "PKR 433,100" · trend chip (`bg/positive-soft`, radius full, padding 2/8, `trend-up` 14px + `Meta/Semibold` `text/positive` "12.4%") · `Meta/Regular` muted "vs PKR 385,300 the 30 days before". A falling metric uses `trend-down` + negative tokens.
- **Sub-metrics** (horizontal, gap 40): each is a label `Meta/Regular` muted over a value `Body/Semibold`.
- **Chart** (fill width × 200):
  - y-axis labels `Meta/Regular` muted (k / lac / cr compact) with dashed gridlines (`border/default`, dash 4/4).
  - Plot: `areaChart(values, { accent: 'bg/accent', areaOpacity: 0.08 })`. The line is 2px with round caps.
  - Highlight point: 10px circle `bg/surface` with a 2.5px `bg/accent` stroke, a 1px vertical guide in `border/strong` down to the axis, and a tooltip above it (`bg/inverse`, radius md, padding 8/10, date `Meta/Regular` white 70%, value `Meta/Semibold` white).
  - x labels: 5 dates, `Meta/Regular` muted.
  - Use realistic, non-monotonic data (about 30 points).

### 4b. Position card (≈ 1/3): same card style, padding 24, 3 blocks separated by hairlines (gap 20)
- **Head**: `Title/Section` "Cash position" · Quiet link "Ledgers" + `chevron-right`.
- **Block 1**: label `Meta/Regular` muted "Receivable" + count right "9 clients" · figure `Title/Stat` · aging bar (8h, radius full, three segments with 2px gaps: `icon/secondary` 0–30d · `text/warning` 31–60d · `text/negative` 60d+, widths proportional) · legend (6px dots + `Meta/Regular` "0–30d · 255k").
- **Block 2**: "Vendor payable" + "4 vendors" · `Title/Stat` · `Meta/Regular` "Next due 18 Sep · PIA Consolidators · PKR 96,000".
- **Block 3**: "Collected today" + "3 receipts" · `Title/Stat` · `Meta/Regular` "Cash 30,000 · Bank transfer 20,000".

## 5. Row 2: work (horizontal, gap 24, top aligned)
### 5a. Main table card (fill ≈ 2/3, padding 0, clip)
- **Head** (padding 20/24, SPACE_BETWEEN): title "Recent bookings" + sub-line "Every sale recorded at this branch · updated 2 min ago" · Segmented Control All / Tickets / Umrah / Visa + Secondary Icon Button `filter`.
- **Columns**: Booking (two-line: name `Body/Semibold`, sub-line `Meta` "Ticketing · PK 301 · LHE–JED · 4 passengers") · Client · Date (two-line: date + time) · Client charge (Amount, right) · Status (Badge) · actions (`more`).
- **Rows**: 5 real rows with a refund (−72,500, positive tint) and a long name.
- **Foot**: centred Quiet link "View all bookings" + `chevron-right`, with a top hairline.

### 5b. Right column (≈ 1/3, vertical, gap 24)
- **Needs attention card**: head `Title/Section` + count badge (`bg/accent-soft`, `text/accent`, "3") + "Today" `Meta` muted on the right; 3 Attention Items separated by gap 16. Examples:
  - Internet bill · Overdue by 2 days (`bg/negative-soft` tile, `receipt`) · **Pay**
  - Electricity bill · Due in 4 days (warning) · **Enter**
  - Refund due · Sana Iqbal (neutral `refund`) · **Pay**
- **Breakdown card**: `Title/Section` "Profit by service" + "Last 30 days" muted. Each row is a label `Body/Medium` with the right-aligned value `Meta/Regular` "PKR 208,000 · 48%", over a 6h bar (radius full) on a `bg/muted` track. **Only the top row's bar is `bg/accent`**; the others use `icon/secondary`.

## 6. Optional overlay
A default Toast centred 24px above the bottom edge ("Booking saved: 4 passengers. PKR 50,000 received; balance PKR 280,000."), absolute positioned.

## 7. Checks before calling it done
- [ ] Count the accent touches: 5 or fewer (CTA, brand mark, active nav icon, urgent count, chart line or top breakdown bar). The notification dot counts as part of the bell.
- [ ] No uppercase labels, no emoji, no gradients, no green outside status.
- [ ] Every number formatted with its currency, thousands separators and the correct sign; refunds negative.
- [ ] The empty, loading and restricted variants exist, or are listed as a follow-up.
- [ ] `auditUnboundPaints(dashboard)` is empty. Chart-area paint opacity is bound to `bg/accent` at 8%, not a raw hex.
- [ ] Screenshot at scale 0.5 for the whole frame, plus a 1:1 crop of row 1.
