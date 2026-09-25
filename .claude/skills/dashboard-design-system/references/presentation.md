# Presentation — Koala UI style (multi-page)

## Pages (in this order)
```
👋 Welcome                       Thumbnail 1920×1080 + Information frame
📋 Documentation                 Documentation kit (Header, Footer, anatomy)
----- 🏠 Foundation 🏠 -----
🎨 Color                         Primitive Colors · Semantic Colors
🆎 Typography                    Product Typography · Documentation Typography
🧱 Spacing & Radius              Spacing · Radius
🪄 Effects                       Shadows & focus
🫰 Icons                         Icons
----- ❖ UI COMPONENTS ❖ -----
❖ Buttons  ❖ Form Controls  ❖ Badges & Tags  ❖ Data Display
❖ Feedback  ❖ Navigation  ❖ Overlays
---- 🗒️ PRODUCT PAGES 🗒️ ----
🗒️ Dashboard                     flagship screen (+ states)
```
Emoji are fine in **page names**. Never put them in text layers, because DM Sans renders them as empty boxes.

## Documentation frame
- One white frame per topic, **1440 wide** (wider only when content needs it), radius 8, clip content, vertical auto layout, frames 80px apart on the page, top-aligned.
- **Documentation Header** component (props `Section`, `Title`, `Description`): padding 32, bottom hairline, gap 16.
  - Top row: "Styleguide" (`Docs/Body`, text/secondary) left · "<Product> DS v1.0 · <font> · Phosphor Bold" (`Docs/Body`, text/muted) right
  - Breadcrumbs: section crumb (`Docs/Meta`, text/secondary) › current crumb (`Docs/Meta`, text/primary on `bg/canvas`, radius sm, padding 4/6)
  - Title `Docs/Title` 48/56 · Description `Docs/Subtitle` 20/28, 820 wide
- **Content container**: padding 32 for components, 48–64 for foundations; gaps 32–48; group headings `Docs/H2` 30/36 Bold; labels `Docs/Label` 20/28; specs `Docs/Meta` 14/20.
- **Documentation Footer**: padding 32, top hairline, brand mark + name (`Docs/Body Bold`) left, "Design System v1.0 · <date> · <accent hex> on the Koala UI scale" right.
- Component sets keep a purple dashed outline (`#9747FF`, dash 10/5, radius 5).
- Every component page opens with an **Overview** frame: a list with one row per component (name `Docs/Label` 320 wide · description `Docs/Body`).

## Docs text styles (DM Sans, Koala scale)
| Style | Weight | Size/line | Tracking |
|---|---|---|---|
| `Docs/Display` | SemiBold | 60/68 | −1% |
| `Docs/H1` | Bold | 60/68 | −1% |
| `Docs/Title` | SemiBold | 48/56 | 0 |
| `Docs/H2` | Bold | 30/36 | −1% |
| `Docs/Style Name` | Bold | 24/32 | −2% |
| `Docs/Lead` | Regular | 24/32 | −2% |
| `Docs/Subtitle` | Regular | 20/28 | −3% |
| `Docs/Label` | Medium | 20/28 | −3% |
| `Docs/Body` | Regular | 18/28 | −3% |
| `Docs/Body Bold` | Bold | 18/28 | −3% |
| `Docs/Meta` | Medium | 14/20 | −3% |

## Foundation pages
- **Primitive Colors**: swatch rows (label 200 wide `Docs/Subtitle` + 144×112 tiles, radius 12, step in `Docs/Body Bold`, hex in `Docs/Meta`, light tiles get a hairline).
- **Semantic Colors**: groups Background · Text · Icon · Border & focus. 216×124 tiles show the token name, `var(--…)` and hex.
- **Typography**: specimen rows. The meta column is 380 wide (name `Docs/Style Name`, spec line, description); the sample in the style fills the rest.
- **Spacing**: bars at 8× scale in accent. **Radius**: 152 tiles on accent-soft with accent stroke. **Effects**: 152 tiles per effect.
- **Icons**: 128-wide cells (icon + `Docs/Meta` name) plus a sizes row (16 · 20 · 28).

## Welcome page
- **Thumbnail** 1920×1080. Left: brand lockup (52 mark, radius 12), a version chip on accent-soft ("Design system v1.0 · <accent> on the Koala UI scale"), a `Docs/Display` headline about the product promise (≤ 2 lines at 780 wide), a `Docs/Lead` description (680 wide), and 4 counts (`Title/Stat` + `Meta/Regular`). Right: a canvas panel (x 880) with the dashboard clone at 0.66 scale with `Shadow/Menu`, and the Needs-attention card cloned 1:1 overlapping its lower left edge.
- **Information** frame 1440: `Docs/H1` "Welcome to <Product> DS", then `Docs/H2` sections: What is it? · How it's built · How this file is organised · Principles · Open decisions.
