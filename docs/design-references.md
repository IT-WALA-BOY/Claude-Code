# Design references (from Figma)

Source: https://www.figma.com/design/MpJESdIHTiE4uCqCdcnbi6/Task-Management-Dashboard

The file has 10 notes. Each one is quoted exactly below, followed by what it means for the dashboard.

## 1. Dashboard 9 "Tasklify" (node 1:2): icons and cards
> "This is how I want the icons on my dashboard same styling same everything, how clean rounded, sleek and modern these look i loved it. I liked there Cards feature as well overall this is a perfect fit."

- The icons are **Lucide** (layer names: `users`, `file-clock`, `gallery-vertical-end`, `circle-check-big`, `calendar-minus-2`, `message-circle-more`, `flag`, `sparkles`, `zap`, `chart-bar`, `network`, `route`, `inbox`, `circle-help`).
- This frame is a scaled copy (about 0.338×) of a 1440 px design. Values at real size:
  - **Stat card:** 1 px border `#E3E1DD`, radius 10, padding 16, gap 16.
  - **Icon tile:** black `#111` background, radius 8, padding 6, white 24 px Lucide icon.
  - **"View Details" link:** 15 px medium `#3D3D3D` with a `chevron-right` icon.
  - **Value:** 20 px semibold `#111`. **Label:** 15 px medium `#666`.
- **Font:** Inter Display (Medium, SemiBold).
- **Sidebar:** 20 px outline Lucide icons, grouped sections (Dashboard / Tools), a count badge on Notifications, the profile at the bottom.
- **Kanban card:** task ID (`WEB - 21`), priority pill (Urgent red, Medium purple, Low green), title, sub-label, "Due to" date, avatars, comment count, date.
- **Column header:** status pill with an icon and a count (Not Started gray, Pending pink, Completed green, Under Review purple), plus "+ New Page" at the bottom of each column.
- **View switch:** Kanban / Timeline / Spreadsheet / Calendar.

## 2. Task (node 1:564): Gantt timeline
> "Look at the pills color then calender design, I loved it i want something very similar to it, if we dont discuss this feature change the plan and add it."

- The task table sits on the left (Task pill, Assigned, Status, Priority). The **Gantt bars** sit on the right, grouped by week.
- The pastel pill colors are shared by table and bars: blue `#D6E4F5`, green `#D5F5D8`, pink `#F5DADA`, purple `#DCD8F5`.
- Top controls: `< This Month >` and a segmented control Day / Week / Month / Quarter / Year.
- Status icons: spinner = Progress, check = Done, pause = Paused, play = Not started. Priority arrows: High green-up, Medium orange-up, Low down.
- **Plan change:** add a **Timeline (Gantt) view**. It wasn't in the plan.

## 3. Kanban (symbol 2:4808) + Kibo UI Gantt link
> "I like this kanban functionality we want some thing similar to this in our dashboard as well. see this link for inspiration https://www.kibo-ui.com/components/gantt"

- **Columns:** Pending / In Progress / Complete / Do Later. Each column has a colored dot header and an "+ Add new" button at the **top**.
- **Cards:** tag chips, bold title, and either a **checklist inside the card** (this covers the "note blocks with checkboxes" request) or a description. Footer has avatars, comment count and attachment count.
- **Kibo Gantt behavior:** drag bars to move dates, drag the edges to resize, a today marker, zoom levels, rows grouped with a sidebar, and add an item by clicking an empty row.

## 4. Flowza, 7 screens (images 6 to 12): the main reference
> "These 7 Screens nailed they have all the things that i want ... if you implement the way they did it i think you are good to go but do look at others as well."
> https://www.behance.net/gallery/248310091/Flowza-SaaS-Project-Management-Dashboard

- **Dashboard:**
  - "Welcome back" header with a secondary "New Project" button and a black "Add Task" button.
  - 4 KPI cards: title + `...` menu, a big number with a delta chip (green up / red down), and a footnote ("+25 tasks being worked on").
  - A **rounded stacked bar chart** (complete / new / overdue, where overdue is hatched red) with a hover tooltip.
  - An **activity heatmap** grid, plus Recent Activity, My Meeting and Task Distribution cards.
- **Projects:** a view switch with pill buttons List / Board / Calendar / Files.
  - List is grouped tables with a count badge and a `+`.
  - Board is columns with a colored left bar and count, holding cards with a date, checkbox, title, description, avatars and priority pill.
- **Calendar:** month grid with small colored dot pills per task, a black circle marking today, and a "+2 tasks" overflow.
- **Schedule:** week grid with pastel event blocks that have a time chip at the top, and a **current time line** with a time pill.
- **Performance:** KPI cards, a smooth area line chart with a tooltip, a completion vs overdue bar meter, and a red "Bottleneck Alert" box.
- **Task list:** filter chips (All / To Do / In Progress / Completed / Overdue), Filter and Sort By buttons, and rows with priority pills and status dot pills.
- **Sidebar:** MENU / GENERAL / PROJECTS sections (projects have a color square), an active item with a filled accent, and the profile card at the bottom.
- **Top bar:** page title, a search field, a bell, and a theme toggle (not used, since the dashboard is light only).

## 5. FacilityFlow (images 4, 5)
> "Look at this high end case study and these fucking clean screens I loved them both"
> https://www.behance.net/gallery/254898551/FacilityFlow-Facility-SaaS-UX-UI-Dashboard-Design

- A black pill-segmented top nav, and a soft gray canvas with white rounded panels.
- **Task table:** checkboxes, a two-line title (name + location), a priority icon with its label, status pills (Active green, In progress blue, Inactive gray), and a two-line date. Pagination and "Per page" at the bottom.
- **Right panel:** a bar chart of reported vs resolved tasks, a pill tab switch (Overview / Team / Trends / Activity), and rows with % stats.
- **Timeline view:** status groups on the left (In progress / Active / Inactive / Complete, collapsible), a day timeline on the right with event chips, a tooltip, and a green "today" marker.

## 6. Oripio list view (image 13)
> "This is the best example of kanban and look how clean is it. i think we can consider this as well."

- View switch: Kanban View / List View / Calendar View with an underline on the active tab.
- **Status groups** each have a tinted header bar (To Do blue, On Process yellow, On Review pink, Completed green) with a count and a `+`, and the groups can collapse.
- **Table columns** have icons in their headers: Task name, Description, Assignee, Start, Due Date, Priority (flag pill), Attach, Chat.
- The sidebar has drag handles on the project items.

## 7. Schedule (node 1:1211)
> "Look at this calender design and everyday task I loved it, i want something very similar to it, if we dont discuss this feature change the plan and add it."

- **Left panel:** a mini month calendar with today circled, then an **agenda list** (Today / Tomorrow / date), where each event has an icon, time, title, location and a Join button.
- **Main area:** a week grid, with pastel event blocks that have a colored left border, time + icon, and title. Today's column is highlighted.
- A **second timezone column** on the right (GMT +5:30 in the reference). We use it for **Pacific + PKT**.
- **Plan change:** the schedule becomes a real calendar screen (mini calendar + daily agenda + week grid).

## 8. Analytics (node 1:3787)
> "Look at this Presentation of graphs i want similar not simliar but same like charts specially rounded for analytics in my dashboard."

- Tabs: Task / Schedule, plus the Day…Year segmented control.
- A **KPI table** card.
- A **donut with rounded, gapped segments** (black + pastel purple / blue / green) with callout labels and a legend with counts.
- **Rounded horizontal progress bars** with colored % labels.
- A **Time chart:** diverging rounded bars (behind in blue, on time in green).
- A **Workload chart:** rounded stacked horizontal bars (completed / remaining / overdue).
- **Plan change:** add an **Analytics screen**. The charts must match this style exactly.

## 9. Pipeline variant 5 (node 1:2314)
> "Not a design inspiration but looks at task list we cand drag and reorder cute boxes like that, the design going to be light mode not dark mode."

- Compact cards you can drag and reorder: name, value, a stage fraction (1/5), and an arrow or a warning icon.
- Column headers show a total and a count badge.
- **Decision:** the whole dashboard is **light mode only**.

## 10. Kanban trio (images 1 to 3, inside frame 2:5256)
> "I'm in love with this kanban design please create somethinng similar to it and that much cleaning Ness. 3rd one is my babe we need the card drag and drop in this 🥹🥹"

- **Image 1:** week schedule with pastel blocks, filter pills (All / category), a "+ Add Event" black button, and a current time marker.
- **Image 2:** month calendar with 4 stat tiles above it, category filter checkboxes, and an event details side panel.
- **Image 3 (the favourite):** a kanban where each card has a **colored priority header band** (Urgent red, Moderate orange, Low green) with a tag chip, title, description, **segmented progress bar** with %, and comment/attachment counts + avatars.
  - While dragging, the card **tilts** and a **dashed placeholder** shows where it will drop.
  - Columns have a status circle, a count badge, `+` and `...`, and "+ Add Task" at the bottom.

## Design system summary (to confirm in the design system step)
- **Light mode only.** Soft gray canvas (`#F5F5F4` range), white rounded cards, warm 1 px borders (`#E3E1DD`).
- **Text:** `#111` for primary, `#666` for secondary.
- **Font:** Inter Display (fallback Inter).
- **Icons:** Lucide everywhere, in Tasklify styling (outline, rounded caps). Stat icons sit in black rounded tiles.
- **Primary action:** black button. Open choice: add a blue accent (Flowza) for the active nav item, or stay neutral (Tasklify).
- **Pastel pill set:** blue, green, pink/red, purple, orange/yellow. The same colors are used for status pills, Gantt bars, calendar blocks and chart series.
- **Charts:** rounded bar ends, donut segments with gaps and rounded corners, rounded progress tracks.
