# States, onboarding, motion, and destructive actions

The parts of an interface that only exist in real use — and are therefore the parts a builder never sees, because they live in a bubble of perfect demo data.

## 1. Every data surface needs four states

Design all four deliberately. A new user will hit at least two of them in the first minute.

| State | Requirement |
|---|---|
| **Empty** | Explain what goes here and give the one action that fills it. Never an empty box or "No data". |
| **Loading** | Skeleton matching the shape of the real content. Never a blank region. |
| **Error** | Say what failed, in plain language, plus a retry. No error codes as the whole message. |
| **Populated** | Tested with ugly data — long strings, nulls, 400 rows. |

## 2. Speed is an aesthetic

A one-second delay costs meaningful conversion. The fix is usually not faster code — it is showing something immediately.

- **Skeleton screens wherever data is fetched.** Grey bars in the layout's shape. Nothing loaded faster; it just felt like it did.
- **Progress indicator for anything over a second.**
- **Something playful for genuinely long jobs** — a small animated character or illustration. It converts waiting into a moment of personality.
- Users do not need speed so much as **evidence that something is happening**. Two seconds of skeleton is fine; two seconds of blank white is not.
- Where a platform-native spinner is available, using it early is worth considering — a delay that reads as the platform's rather than the app's costs you less goodwill.

## 3. Onboarding

The moment after someone starts a trial is when they are least patient and most critical. You have minutes — sometimes seconds.

- **Do not force a tour.** Forced tutorials feel like a lecture; people click through without reading to reach the app.
- **Progressive onboarding wins.** One obvious action that completes a real job, then reveal the next step once it is done.
- **Make the first step impossible to miss.** The primary card gets the accent color, a subtle elevation, and a literal "Start here". This is the one screen where being loud is correct.
- **Show the finish line.** A progress bar or "2 of 4" counter.
- **Celebrate completion.** Confetti, a checkmark, a short congratulations email that names the next step. Celebrating the user doing the job is cheap to build and disproportionately effective.

## 4. Show value in numbers

The most underused retention feature in B2B. Business buyers must justify the subscription — to a boss or to themselves. If the product does not show what it achieved, they will guess. **They always guess low.**

Put a value tracker on the dashboard:

> **Your impact this month** — 30 hours saved · 100 tasks automated · 26 leads found · $18k pipeline generated

Make the app a scoreboard, not just a tool. Trigger a celebration at milestones. This is simple to build and people look at it every single session.

## 5. Ethical friction on destructive actions

If a user clicks Delete and something vanishes instantly, two bad things happen: they cannot undo a mistake, and they are not certain it worked — so they refresh to check, and it is gone.

For anything destructive, expensive, or final:

1. **Confirm with consequences spelled out.** Title naming the object, body stating exactly what is removed and that it cannot be undone.
2. **Type-to-confirm for the truly serious.** Requiring the user to type the resource name is deliberately tedious — that is the point in a danger zone.
3. **Confirm the completion.** A toast: "Project deleted" with a 5-second **Undo**.

Unfinished or unconfirmed actions nag at people (the Zeigarnik effect). Closing the loop visibly is as important as the confirmation before it.

## 6. Motion budget

Inside the app, default to no animation. Then add back only what communicates:

- **Earns its place:** skeleton fade-in, completion checkmarks and confetti, state transitions under 200ms, a drawer sliding from the edge it belongs to.
- **Does not:** scroll-jacking, parallax, elements flying in, decorative fade-ins on every card.

**Load more over infinite scroll.** It gives the user control, lets them actually reach the footer, and avoids degrading the page as rows accumulate.

The test for any motion: does it tell the user something? If not, remove it.

## 7. Instrument what you cannot see

You know where everything is because you built it. Users do not, and they will not report friction — they will just find the app annoying and leave. Session recording and product analytics (Hotjar, PostHog, or equivalent) are how you see the maze from outside. Watch where people hesitate, then fix that screen against the checklists.
