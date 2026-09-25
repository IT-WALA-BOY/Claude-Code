# Components: /dashboard-design-system

Bind every fill, stroke and radius to tokens. Bind padding to `spacing/*` wherever the value is on the scale. Variant values are Title Case. Icons are INSTANCE_SWAP properties whose preferred values are all icon components. Icon glyph colour follows the sibling label's token (`text/x` → `icon/x`).

## Icons (Phosphor Bold, 16px components)
layout `SquaresFour` · bookings `Ticket` · wallet `Wallet` · building `Buildings` · bank `Bank` · receipt `Receipt` · chart `ChartBar` · users `Users` · user-gear `UserGear` · gear `GearSix` · search `MagnifyingGlass` · bell `Bell` · calendar `CalendarBlank` · plus `Plus` · x `X` · check `Check` · more `DotsThree` · chevron-down `CaretDown` · chevron-right `CaretRight` · chevron-left `CaretLeft` · caret-up-down `CaretUpDown` · arrow-left `ArrowLeft` · arrow-up-right `ArrowUpRight` · trend-up `TrendUp` · trend-down `TrendDown` · filter `FunnelSimple` · download `DownloadSimple` · printer `Printer` · refund `ArrowUUpLeft` · history `ClockCounterClockwise` · alert `WarningCircle` · inbox `Tray` · key `Key` · logout `SignOut` · sidebar `SidebarSimple` · coins `HandCoins` · command `Command` · question `Question` · shield-check `ShieldCheck` · clipboard `ClipboardText`
Add domain icons as needed (e.g. `AirplaneTilt`, `Mosque`, `IdentificationCard` for travel).

## Buttons page
| Component | Matrix | Spec |
|---|---|---|
| `Button` (32) | Style Primary · Secondary · Quiet · Danger × Size Medium · Small × State Default · Hover · Focus · Disabled | Medium 36h (pad 8 vertical, 12 horizontal), Small 32h; radius md; gap 6; label Body/Medium (Primary: Body/Semibold); Primary `bg/accent` + `text/on-accent`; Secondary `bg/surface` + `border/strong`; Quiet no fill, `text/secondary`; Danger `border/strong` + `text/negative`, hover `bg/negative-soft`; Focus adds `border/accent` + `Focus/Ring`; Disabled opacity 50%. Props: Label, Show icon, Icon |
| `Icon Button` (8) | Style Secondary · Quiet × State ×4 | 36×36, pad 10, radius md, icon 16. Prop: Icon |

## Form Controls page
| Component | Matrix | Spec |
|---|---|---|
| `Field` (10) | Type Text · Select × State Default · Focus · Error · Disabled · Read-only | Label Meta/Medium `text/secondary`; control 36h, pad 8/12, radius md, `bg/surface` + `border/strong`; Focus `border/accent` + ring; Error `border/negative` + helper `text/negative`; Read-only dashed border on `bg/subtle`; Select trailing chevron. Props: Label, Value, Helper, Show helper, Show icon, Icon |
| `Calculated Value` | Single | dashed `border/strong` on `bg/subtle`, value Body/Semibold, sub-line Meta muted |
| `Checkbox` (6) | Checked Off · On × State Default · Disabled · … | 16 box, radius 4, checked `bg/inverse` + white check (neutral: long permission and filter lists must not spend the accent) |
| `Choice Chip` (2) | Selected Off · On | pill, selected `bg/accent-soft` + `border/accent-soft` + `text/accent` |
| `Segment` (2) + `Segmented Control` | Selected Off · On | track `bg/subtle` + hairline, radius md, pad 2; segment 28h, selected `bg/surface` + Shadow/Card + Meta/Semibold |

## Badges & Tags page
| `Badge` (4) | Tone Positive · Warning · Negative · Neutral | 20h pill, 6px dot, Meta/Medium, soft tint + 700 text. Add a **status → tone map** frame listing every status word from the brief |
| `Tag` | Single | 0/6 padding, radius 4, hairline `border/strong`, Meta/Medium `text/secondary` (Shared, Refund, Reissue…) |
| `Chip` (domain) | e.g. passenger type | soft tint pill, Label/Small |

## Data Display page
| `Stat` (3) | Tone Default · Negative · Positive | label Label/Small muted · value Title/Stat · meta Meta muted |
| `Stat Strip` | Single | a row of Stats with vertical hairlines in one card, for secondary pages (not the flagship hero) |
| `Table Header Cell` (2) | Align Left · Right | 36h, pad 10/16, `bg/subtle`, Label/Small muted, bottom hairline |
| `Table Cell` (7) | Type Text · Two-line · Amount · Amount Negative · Amount Positive · Badge · Actions | 44h, pad 12/16, bottom hairline; amounts right-aligned tabular; two-line = Body/Semibold + Meta muted; actions = Quiet Icon Button `more` |
| `Table Footer Cell` (2) | Align Left · Right | `bg/subtle`, Body/Semibold, top `border/strong` |

## Feedback page
| `Alert` (3) | Tone Error · Success · Info | soft tint, radius md, icon 16, Body text |
| `Toast` (2) | Tone Default · Error | `bg/inverse` or `bg/negative`, white text, radius md, check / alert icon |
| `Empty State` (4) | Type Empty · Filtered · Error · No permission | 28 icon muted, Body/Semibold title, Meta sentence (≤ 360), optional button |
| `Skeleton Row` | Single | bars on `bg/skeleton`, radius 3, widths echoing the table columns |
| `Attention Item` | Severity Overdue · Due soon · Info | 32 icon tile (radius md, severity soft tint + severity icon), title Body/Medium, sub-line Meta in severity text, trailing Small Secondary button (verb) |

## Navigation page
| `Nav Item` (3) | State Default · Hover · Active | 36h, pad 8/10, radius md, lead group (icon 16 + label, gap 10), SPACE_BETWEEN with Count; Default `text/secondary`; Hover `bg/muted`; **Active `bg/surface` + hairline + Shadow/Card + `icon/accent` + Body/Semibold**. Props: Label, Icon, Show label, Count, Show count. Urgent count override: `bg/accent-soft` + `text/accent` |
| `Sidebar` | Single | see dashboard-recipe §2 |
| `Top Bar` (optional) | Single | 52h white bar, for products that must keep top navigation |
| `Impersonation Bar` (if roles) | Single | warning-soft strip with the Return button |

## Overlays page
| `Menu Item` (4) | Type Default · Danger × State Default · Hover | 32h, radius 6, icon 16 + Body |
| `Menu` | Single | `bg/surface`, `border/strong`, radius md, pad 4, Shadow/Menu, separators |
| `Modal` (2) | Size Normal 560 · Wide 760 | radius lg; head (title Title/Section + sub Meta + close), body grid of Fields, foot (Quiet Cancel + Primary). Wide = inputs left, live breakdown right (Calculated Values) |
| `Drawer` | Single | 760 × full height, right edge, head with actions (print, history, close), Stat Strip, table |
| `On scrim` example | Single | Modal over `bg/scrim` |

## Screen-level components (promoted from the product screens)
Built from real screen nodes with `createComponentFromNode`, then every on-screen copy was replaced with an instance.

| Component | Page | Variants / props | Used by archetype |
|---|---|---|---|
| `Search Field` | Form Controls | Placeholder, Show shortcut (⌘K) | Header of list screens |
| `Service Tile` | Form Controls | Status Included · Not taken; Name, Meta, Icon | Builder (services a booking includes) |
| `Pagination` | Data Display | Label, Page 1–5, Show page 2–5 | Every table card footer |
| `Summary Line` | Data Display | Tone Default · Positive · Negative; Label, Value | Sticky summary and totals cards |
| `Account Card` | Data Display | Bank, Account number, Balance, Last movement, Icon; Tag exposed | Accounts screen |
| `Bill Due Item` | Feedback | Tone Overdue · Due soon · Not entered; Day, Month, Name, Status; Button exposed | Bills and spend: "Due next" |
| `Bill Tile` | Feedback | Name, Provider, Amount, Amount note, Show action, Icon; Badge and Button exposed | Bills and spend: monthly bills grid |
| `Notification Button` | Navigation | Show unread | Header of every app screen |
| `Section Step` | Navigation | State Done · Current · Not taken; Title, Meta | Builder section rail |

Also: the `Checkbox` checked state uses `bg/inverse` (neutral), so permission lists and filters never spend the accent.

## Work-management extras (Workflow Dashboard)
Add these when the product tracks tasks, pipelines or goals.

| Component | Matrix | Spec |
|---|---|---|
| `KPI Card` | Single | nested shell: title row (18px icon, Title/Section, `⋯` or open arrow) + panel with value (Title/Stat) and Trend Chip, divider, one footnote whose lead figure is bold or red |
| `Trend Chip` | Direction Up · Down · Flat × Tone Positive · Negative · Neutral | 22h, radius 6, icon 14 (`trending-up/down`, `minus`), Meta/Medium; direction and tone are independent |
| `Category Pill` | Color blue · green · purple · pink · orange · yellow | 22h, radius 6, pastel bg + 700-ish text; optional 14px icon. The same pastel set colours Gantt bars, calendar blocks and chart series |
| `Priority Pill` | Urgent · Moderate · Low | flag icon + label; red-soft · orange · green tints |
| `Task Card` (kanban) | State Default · Dragging | priority header band (tinted shell with caps label and flag) around a white panel: category pill + `⋯`, title (Body/Semibold), notes or a checklist, "Progress" with a 4-segment bar and %, footer with due date, checklist count, estimate. Dragging = rotate 2°, scale 1.02, `Shadow/Drag`, with a dashed placeholder where it will land |
| `Lead Card` (pipeline) | State Default · Late · Due today | name + company, market/industry tags, footer with next follow-up and stage fraction (3/7); Late = red border + alert icon |
| `Field` extras | Single | 40h control, radius 10, optional leading icon and trailing unit ("USD", "h") |
| `Toggle` | Off · On | 36×20 track, black when on (never the accent) |
| `Option Card` | Selected Off · On | radio as a card: title + one-line help; selected = 1.5px black ring |
| `Stepper` | Step Done · Current · Upcoming | 24px circles joined by lines; done = black with a check |

Icons (Lucide names) that covered the whole product: `layout-dashboard square-check-big calendar-days target figma circle-dollar-sign wallet shopping-cart megaphone chart-column settings circle-help search bell clock plus ellipsis chevron-* trending-up trending-down flag list-todo loader file-search pause circle-check-big list-checks timer grip-vertical send user-plus scan-search sparkles zap briefcase-business graduation-cap lock eye`.
