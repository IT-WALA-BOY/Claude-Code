---
name: saas-ui-design
description: Design and review interfaces so they look like a paid product instead of AI-generated slop. Use when building or restyling any web UI (SaaS dashboards, admin panels, internal tools, marketing/landing pages, or any website) and when asked to "make this look better", "make it look professional", fix a vibe-coded design, choose colors/spacing/icons, design empty/loading/error states, plan onboarding, or audit an existing page. Covers both in-app product UI and marketing pages.
---

# SaaS & Web UI Design

The gap between a vibe-coded app and a product people pay for is almost never the code: it is interface decisions. This skill is that decision checklist. Taste is not required; following the rules is.

**Core principle: AI decorates, your job is to inform.** Every AI-default flourish (emoji, gradients, glow shadows, repeated KPI cards, five colored chips per row) adds visual material where information should be. For every element on a screen, ask: *does this help the user finish the job they came here for?* If it is decoration, delete it.

## How to use this skill

1. **Read the intent first.** Before laying anything out, answer: what did the person arrive on this screen to do? Design around that one answer. Add functionality only when a genuinely new user intent appears, never because there is empty space to fill.
2. **Run the quick audit below** on whatever exists (or on your plan, before writing code).
3. **Load the reference that matches the surface:**
   - In-app screens: dashboards, tables, forms, settings, modals → `references/app-ui.md`
   - Marketing site, landing page, pricing page → `references/landing-page.md`
   - States, motion, onboarding, destructive actions → `references/states-and-flow.md`
   - Turning a design into fast, smooth, clean code (code-standards file, tokens and icons, optimistic UI, drag and drop, testing on the real server) → `references/build-notes.md`
4. **Write the rules down.** Create or update a project design-rules file from `references/design-rules-template.md` and keep referring to it. Undocumented rules get broken: the same action ends up labeled "Delete" on one screen and "Remove" on another, and users experience that as friction they never report.

## Quick audit: the eleven tells of a generated UI

Check each. Every "yes" is a fix, and most take under an hour.

1. **Emoji used as icons or decoration.** Replace with one real icon set: Lucide, Phosphor, or Feather. Never mix sets. Icons are small but they are the most visible taste signal in the whole app.
2. **More than one accent color.** AI defaults to saturated blue or purple, then adds a second bright accent that fights it. Pick a restrained near-neutral base plus exactly one accent.
3. **Color used for chrome.** Reserve color for meaning: statuses, charts, the single primary action. A dashboard of colored buttons looks cheap; the same dashboard in muted greys with color only on data looks expensive.
4. **Inconsistent radii.** Some buttons pill-shaped, some square. Pick one radius scale and apply it everywhere: buttons, cards, inputs, modals.
5. **Inconsistent type.** Random font sizes, mixed casing, arbitrary weights. Define a scale (see the rules template) and use only steps on it.
6. **Glow shadows / clashing gradients.** Delete them. Thin 1px borders on a near-white surface read as more expensive than any shadow.
7. **Repeated components across pages.** The same four KPI cards on the dashboard, the analytics page, and billing. AI has no memory of what it already built. Each page answers exactly one question: delete everything on it that answers a different one.
8. **Overloaded cards and rows.** Every button, chip and timestamp visible at once. Compress: collapse the button row into a `⋯` menu, turn text chips into icons or a single dot, push the one number that matters to the right.
9. **Nothing is dominant.** If everything is the same size and weight, the user must read all of it. Every screen is a sentence: one element is the subject; turn the volume down on everything else.
10. **Only demo data has been seen.** Short tidy names, round numbers. Real data has 60-character names, nulls, zero results, and 400 rows.
11. **AI punctuation in the copy.** Em dashes everywhere, "seamlessly", "effortlessly", title-cased labels. Many people now read em dashes as a sign that a machine wrote the product. Use commas, colons and "to" ("9:30 to 11:00 PM"); show empty values as `0` or "Not set".

## The rules that do not change

Interface fashion turns over every few years: skeuomorphic, flat, neomorphic, glass. Underneath sit a few principles that make an app look competent and trustworthy:

- **Subtract before adding.** The instinct is to see how much fits on a screen. The real question is how little you can get away with while the user still finishes the job. A design is done when there is nothing left to remove.
- **Trust comes from restraint.** Boring, muted, grayscale, generous white space: this is what serious software looks like. Glowing buttons and purple gradients read as "the person who built this cannot build software."
- **Hierarchy is the whole system.** Bold title, muted secondary text, one primary action. That is often the entire design language of an excellent product.
- **Consistency beats cleverness.** Humans pattern-match. One verb per action, one label per destination, one size per role.
- **Hold the user's hand more than feels necessary.** You built the maze; they have never seen it. You know where everything is because you spent months in it: they are not tourists exploring your interface, they came for a result.

## Working from references, not imagination

The fastest way to a good result is to copy a good one. Collect screenshots of apps and sites whose look you want (Mobbin and Dribbble are useful sources, as are the real products you admire) and give those to the model as the explicit target alongside these rules. "Match this reference" beats "make it look nice" every time.

Well-regarded reference points named in the source material for this skill: Attio and Airtable for muted near-white app chrome with color only on data; Stripe for a deliberately boring, high-trust palette; Basecamp for hierarchy achieved with almost nothing; Linear-class density for tables; Supabase for how destructive confirmation should work; PostHog for playful long-running-job states.

## Handing this to a coding agent

When prompting Cursor/Claude/Bolt/Lovable to build or restyle, include: the project design-rules file, the reference screenshots, and this instruction:

> Use one icon set (Lucide). No emoji. One accent color; everything else neutral. Color only for status, charts, and the single primary action. One radius scale, one type scale, one spacing scale. No shadows beyond a hairline border. Each page answers one question. Design the empty, loading, and error state for every data surface. Do not add animation unless it communicates state; when you do, animate only transform and opacity, under 200ms. No em dashes in any copy.

Better still, put these rules and the project's structure in a `docs/code-standards.md` in the repo and have the agent read it before every chunk (`references/build-notes.md` §1).
