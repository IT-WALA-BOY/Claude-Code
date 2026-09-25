# Design rules — [PROJECT NAME]

Copy this file into the project (e.g. `docs/design-rules.md` or `.claude/design-rules.md`), fill in the brackets, and paste it into any AI tool before it writes UI code: **"These are the rules. Follow them exactly."**

Write the rules down or they get broken. When one screen says Delete and another says Remove, the user feels friction they will never report — they just find the app annoying.

---

## Foundations

**Icon set:** [Lucide | Phosphor | Feather] — one set only, never mixed, no emoji anywhere in the product UI.
**Icon sizes:** 16px inline, 20px buttons, 24px headers. Stroke width [1.5].

**Type scale** (font: [ ]):
| Role | Size / weight / color |
|---|---|
| Page title | [24px / 600 / text-primary] |
| Section label | [13px / 500 / text-muted, sentence case] |
| Body | [14px / 400 / text-primary] |
| Meta / secondary | [13px / 400 / text-muted] |

Nothing outside this table ships.

**Spacing scale:** 4 / 8 / 12 / 16 / 24 / 32 / 48. Every margin, padding, and gap is one of these.

**Radius:** [8px] for buttons, inputs, cards and modals. [Full] only for avatars and dots. No other radii.

**Elevation:** hairline border `1px rgba(0,0,0,0.08)`. Shadows only on [modals, popovers] — never on cards or buttons.

## Color

| Token | Value | Used for |
|---|---|---|
| surface | [#FFFFFF] | page and card background |
| surface-alt | [#FAFAFA] | subtle grouping |
| border | [rgba(0,0,0,0.08)] | all dividers and outlines |
| text-primary | [#111111] | headings, body |
| text-muted | [#6B7280] | secondary, meta |
| accent | [ ] | the single primary action |
| success / warning / danger | [ ] | status only, muted tints |

Rule: color appears only on status, data visualization, and the one primary action per view. Everything else is neutral.

## Buttons

| Role | Style | Rule |
|---|---|---|
| Primary | solid accent | exactly one per view |
| Secondary | neutral + border | |
| Tertiary | text only, muted | destructive escape hatches live here |
| Destructive | [danger tint or text] | always requires confirmation |

Height [36px], horizontal padding [14px], label always sentence case.

## Action vocabulary

One verb per concept, product-wide. Fill in and never deviate:

| Concept | The word | Never |
|---|---|---|
| Remove permanently | Delete | Remove, Trash, Destroy |
| Create | New [object] | Add, Create new, + |
| Persist changes | Save | Update, Apply, Submit |
| Leave without saving | Cancel | Discard, Back, Close |
| Send | Send | Submit, Deliver |
| [ ] | [ ] | [ ] |

## Data formatting

- Truncate strings at [40] characters with an ellipsis; full value in a tooltip.
- Empty value placeholder: [—]. Never blank, never "null".
- Numbers: [thousands separators, 0 decimals unless currency].
- Dates: [DD MMM YYYY]. Relative time only for [activity feeds].
- Currency: [ ].

## Required states

Every data surface ships with: empty (explanatory + one action), loading (skeleton matching content shape), error (plain language + retry), and populated-with-ugly-data. No exceptions.

## Motion

Allowed: skeleton fade-in, completion check/confetti, transitions ≤200ms, drawer slide from its own edge.
Banned: scroll-jacking, parallax, entry animations on cards, decorative fades.
Pagination: **Load more** button, never infinite scroll.

## Destructive actions

Confirmation dialog stating what is removed and that it cannot be undone → [type-to-confirm for: deleting a workspace / project / billing changes] → completion toast with 5-second Undo.

## Per-page intent

One line per page. If a component on a page does not serve that page's sentence, it moves or dies.

| Page | The user came here to… |
|---|---|
| [Dashboard] | [ ] |
| [ ] | [ ] |

## Visual references

Screenshots we are matching: [paths or links]. When in doubt, match the reference over inventing something.
