# Project Tracker: FMidterm_Project

**Project:** Agriculture Information System (AIS), AP3 Midterm, Cebu Technological University
**Location:** C:\xampp\htdocs\FMidterm_Project (run at http://localhost/FMidterm_Project/)
**Stack:** Native PHP (OOP models + PDO), Supabase PostgreSQL, Tailwind CSS via CDN, XAMPP Apache. No frameworks, no Composer.
**Purpose:** Track progress so work can continue even if the AI runs out of tokens or the session ends.

## Key Files
- AGENTS.md: instructions Codex reads automatically
- PROJECT_REQUIREMENTS.md: what must be built (plain-text copy of ap3_project.docx)
- CODING_STANDARDS.md: the teacher's coding style (must be followed)
- PROJECT_TASKS.md: task checklist
- ap3_project.docx: original guidelines (the docx wins if anything conflicts)

## Current Status

- **Phase:** 9 Final testing and submission
- **Last updated:** 2026-11-10
- **Currently working on:** Final testing and submission; confirming responsive layouts and night-mode contrast
- **Next step:** Review responsive layouts and green-on-dark contrast on devices, then capture clean final screenshots and complete populated-database checks

## Decisions and Open Questions

Decided:
- Database credentials go in a private file (config/db_credentials.php) that is kept out of shared or public code. config/Database.php reads from it. Reason: the docx says credentials must not be exposed in public source code, while the teacher's Database.php example hard-codes them.
- Tailwind CSS via CDN (from CODING_STANDARDS.md; the docx allows any CSS framework).
- Shared page messages use one-time `$_SESSION['success']` and `$_SESSION['error']` values, escaped and cleared by `includes/header.php`.
- Role permissions follow the user's clarification: Admin can create, edit, and delete; Staff is view-only.
- The project is connected to `https://github.com/coronelstephen4/FMidterm_Project.git`; the initial commit has been pushed to `main`. `.gitignore` excludes `config/db_credentials.php`.

Open (ask the student or teacher, then record the answer here):
- Sample data has harvest dates after today (harvest_id 5 on 2026-11-20, harvest_id 15 on 2026-12-20). Decide whether future harvest dates are allowed.

Known risks:
- XAMPP's PHP needs the `pdo_pgsql` and `pgsql` extensions enabled in php.ini (remove the `;` in front of them) and Apache restarted.
- The database password was visible in a screenshot; rotate it before deployment and update the ignored local credentials file.
- Migration 08 is now confirmed successful in Supabase. Its first attempt failed because `farm.soil_type` was already absent; the corrected script skipped missing legacy columns, checked the remaining mappings, and completed successfully.

Waiting for:
- Run final CRUD/report checks against the now-normalized Supabase schema and seeded demo data.
- Capture application screenshots and visually confirm 390px, 768px, and 1440px layouts; headless Chrome/Edge could not launch due GPU-process failures.

Decision:
- Add lookup tables for all other fixed-value fields whose options are listed in PROJECT_REQUIREMENTS.md: planting status, harvest quality grade, farm irrigation type and soil type, crop category, and recommended season.
- Migration 08 checks each remaining lookup ID/label mapping before dropping duplicate legacy text labels; it skips columns already removed and does not delete business rows.
- Updated farm, crop, planting, and harvest write models store lookup IDs only. Run migration 08 before using create/edit forms for those entities.
- Demo seed data uses clearly labeled fictional records and avoids duplicate inserts on reruns.

## Progress Log

Newest entry at the top. Format:
`[Date Time] What was done | Files changed | What is next`

### 2026-11-10
- Pushed the responsive layout, dashboard contrast, and project documentation updates to `origin/main` in commit `39ad553`. Confirmed private database credentials remain ignored. Left local review screenshots out of Git because one visibly contains database credentials.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Rotate the exposed database password; capture clean final screenshots after responsive checks and finish populated-database verification.

### 2026-11-10
- Reviewed local screenshot files before publishing. Kept the screenshot images out of Git because at least one contains private database configuration; updated the screenshot folder note to distinguish review captures from final submission evidence.
- Files changed: `submission_materials/screenshots/README.md`, `PROJECT_TRACKER.md`
- Next: Rotate the exposed database password; capture clean final screenshots after responsive checks.

### 2026-11-10
- Improved night-mode contrast by changing green text to white and darkening emerald count badges, so dashboard crop links and status totals remain readable. Light mode is unchanged.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Refresh the dashboard in Night mode and confirm the labels and counts are readable.

### 2026-11-10
- Confirmed responsive layout changes use shared CSS breakpoints rather than iOS-specific rules, so phones (including Android), tablets, and laptops receive the appropriate layout for their viewport width.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Visually review the responsive layouts on phone, tablet, and laptop widths; capture final screenshots after the layout is approved.

### 2026-11-10
- Aligned the navigation's first item with the logo's left edge and set the role and Light/Night controls to matching heights at phone and desktop breakpoints.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Visually confirm the shared header on desktop and iPhone.

### 2026-11-10
- Kept the Reports landing page at two columns on all screen sizes after the desktop screenshot showed it expanded to three columns; report cards now remain paired like the dashboard cards.
- Files changed: `reports/index.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Reload the Reports page and confirm two cards appear in each row.

### 2026-11-10
- Changed the Reports landing page to a responsive two-column card grid on phones, matching the dashboard summary layout; it uses three columns on larger screens.
- Files changed: `reports/index.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Review the paired Reports cards on an iPhone.

### 2026-11-10
- Updated shared mobile table styling so wide tables in the dashboard, modules, and reports keep their columns readable and can be swiped horizontally instead of squeezing or clipping cell content.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Review the table scrolling on an iPhone and confirm the data columns can be reached.

### 2026-11-10
- Refined phone layout from the user's marked screenshots: role stays left while the theme control moves to the right edge, navigation links align to the logo, and dashboard totals display two cards per row on small screens.
- Files changed: `includes/header.php`, `index.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Review the updated pages on an iPhone; responsive visual confirmation and final screenshots remain pending.

### 2026-11-10
- Updated the shared header for narrow screens: reduced logo and control sizes, aligned role/theme controls in one row, and arranged navigation links in a compact three-column grid.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Review the updated header on an iPhone; responsive visual confirmation and final screenshots remain pending.

### 2026-11-07
- Removed the optional Settings normalization page and navigation link after confirming the DOCX does not require them; normalization remains documented in `PROJECT_DOCUMENTATION.md`.
- Files changed: `includes/header.php`, removed `settings/index.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Capture required application screenshots, then complete populated-database CRUD/report and responsive checks.

### 2026-11-07
- Removed the Settings navigation link and normalization page at the user's request; marked the feature deferred in the checklist.
- Files changed: `includes/header.php`, removed `settings/index.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Continue final testing and submission; revisit Settings only when requested.

### 2026-11-07
- Expanded Settings normalization summary to identify all 13 tables by group and describe their relationships.
- Files changed: `settings/index.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-07
- Added an Admin-only Settings page with a concise UNF-to-3NF explanation and exposed its navigation link only to Admin users.
- Files changed: `includes/header.php`, `settings/index.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-07
- Removed the dashboard shortcut button row that repeated the main navigation; kept linked summary cards and recent activity sections.
- Files changed: `index.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:32
- Increased the header logo badge to 56px, icon to 40px, and brand typography one step for stronger visibility.
- Files changed: `includes/header.php`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:31
- Increased the header logo badge and icon slightly and raised the two-line brand typography for better visibility.
- Files changed: `includes/header.php`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:30
- Added a compact inline agriculture field logo beside the system name and styled the name as a two-line serif/sans brand lockup in the shared header.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:29
- Made the appearance control text reflect the active setting: “Light” or “Night”; its sun/moon icon continues to match the selected mode.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:28
- Styled the Theme control as a compact pill and replaced its slider with a sun/moon icon button. The icon and accessible switch label update with the selected theme.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:27
- Renamed the visible appearance-switch label to “Theme” and updated its accessible label to describe light/dark theme switching.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:26
- Changed the Crops navigation symbol to a land-only field outline with furrows; removed plant and flower shapes.
- Files changed: `includes/header.php`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:25
- Refined the Crops navigation symbol to show crop plants growing across field rows, linking the icon to both crops and farmland.
- Files changed: `includes/header.php`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:24
- Updated the Crops navigation icon from a generic leaf to a grain stalk to better represent crop records.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:23
- Replaced the Farmers group icon with a single farmer wearing a brimmed hat.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:22
- Replaced the similar chart/storage symbols with distinct crop-leaf, planting-seedling/rows, and harvest-basket icons in shared navigation.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Capture application screenshots in `submission_materials/screenshots/`, then complete populated-database CRUD/report and responsive checks.

### 2026-11-06 06:21
- Audited the project documentation against the DOCX and checklist. Corrected outdated statements about migration 08 and the demo data; the required written documentation sections are present. Screenshots, populated-database end-to-end checks, and manual responsive confirmation remain outstanding.
- Created `submission_materials/` to organize screenshots and test evidence without moving the project documentation.
- Files changed: `PROJECT_DOCUMENTATION.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`, `submission_materials/README.md`, `submission_materials/screenshots/README.md`
- Next: Capture the application screenshots and then verify CRUD/reports and responsive layouts against the current Supabase data.

### 2026-11-06 05:56
- Confirmed from user screenshot: `git push -u origin main` succeeded and created the remote `main` branch. Marked GitHub setup complete; the committed file list did not include `config/db_credentials.php`.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Continue final authenticated CRUD/report checks against the seeded database, then capture module and responsive screenshots.

### 2026-11-06 05:28
- Initialized local Git repository on `main`. Verified `config/db_credentials.php` is ignored and does not appear in status; source folders and project files are ready for review and initial commit.
- The DOCX itself contains no GitHub/Git command requirement; the user reports the professor gave this separately. Initial repository setup is now complete.
- Files changed: `.git/` (initialized), `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: User creates an empty GitHub repository and supplies its HTTPS URL; then connect `origin`, commit, and push.

### 2026-11-06 04:55
- Added a persistent Night mode switch beneath the role badge. It toggles dark theme colors across shared page surfaces, forms, tables, and text, stores the preference in localStorage, and applies it on page load.
- Test: XAMPP PHP lint passed for `includes/header.php`; local login route returned HTTP 200. Authenticated visual toggle interaction remains for user confirmation.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_DOCUMENTATION.md`, `PROJECT_TRACKER.md`
- Next: Continue populated-database integration checks when Supabase connectivity is available to the test environment.

### 2026-11-06 04:52
- Moved the role badge beside the Agriculture Information System title in the header row; navigation and Log out remain on the row below.
- Test: XAMPP PHP lint passed for `includes/header.php`.
- Files changed: `includes/header.php`, `PROJECT_TRACKER.md`
- Next: Continue populated-database integration checks when Supabase connectivity is available to the test environment.

### 2026-11-06 04:51
- Refined the navigation role label into a compact, accessible account badge with a user icon, “Role” label, and Administrator/Staff value.
- Test: XAMPP PHP lint passed for `includes/header.php`.
- Files changed: `includes/header.php`, `PROJECT_TRACKER.md`
- Next: Continue populated-database integration checks when Supabase connectivity is available to the test environment.

### 2026-11-06 04:48
- Improved account clarity: authenticated navigation now labels the current session as Administrator or Staff beside the Log out link.
- Test: XAMPP PHP lint passed for `includes/header.php`.
- Files changed: `includes/header.php`, `PROJECT_TRACKER.md`
- Next: Continue populated-database integration checks when Supabase connectivity is available to the test environment.

### 2026-11-06 04:37
- Updated role access to match the user's clarification: Admin can create, edit, and delete; Staff is view-only. Protected all 10 create/edit routes with `requireAdmin()`, hid Add/Edit/Delete controls for Staff in all five lists and detail pages, and updated project guidance.
- Validation: PHP syntax passed for all 49 files; all 10 create/edit routes contain the Admin-only guard.
- Files changed: `AGENTS.md`, `PROJECT_TASKS.md`, `PROJECT_DOCUMENTATION.md`, `PROJECT_TRACKER.md`, all five module list/detail pages, all create/edit pages.
- Next: Continue populated-database integration checks when Supabase connectivity is available to the test environment.

### 2026-11-06 04:28
- Clarified and fixed the signed-out view: shared header now hides module navigation as well as the Log out control when no user session exists.
- Test: XAMPP PHP lint passed; local login route returned HTTP 200 with both module links and logout link absent.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Continue populated-database integration checks when Supabase connectivity is available to the test environment.

### 2026-11-06 04:26
- Fixed: Added a visible Log out link to the shared navigation for authenticated sessions; it is hidden from signed-out visitors.
- Test: XAMPP PHP lint passed for `includes/header.php`.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Continue populated-database integration checks when Supabase connectivity is available to the test environment.

### 2026-11-06 03:59
- Local checks: XAMPP PHP 8.2.12 lint passed for all 49 PHP files; `auth/login.php` returned HTTP 200.
- Not verified: Authenticated CRUD, filtered list, dashboard, and report pages against the populated Supabase database. Direct PDO execution was rejected by the execution sandbox before connecting; no database test operations ran.
- Files changed: `PROJECT_TRACKER.md`
- Next: Run authenticated app checks in an environment with Supabase database connectivity, then capture module screenshots.

### 2026-11-06 03:55
- Confirmed from user screenshot: Corrected migration 08 completed successfully in Supabase (Success. No rows returned). Marked migration 08 complete; screenshots continue to show the seeded counts (15 farmers, 15 farms, 10 crops, 30 plantings, 20 harvests).
- Updated project requirements and documentation to remove stale statements that migration 08 and seed data were still pending.
- Verification attempt: A transaction-wrapped test was prepared to exercise populated list/filter/report queries and create/read/update/delete across all five modules, with rollback. The execution environment rejected the command before it started, so no test queries or writes ran and no records were changed.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`, `PROJECT_REQUIREMENTS.md`, `PROJECT_DOCUMENTATION.md`
- Next: Complete the populated-database CRUD/report smoke check in the local XAMPP app; capture screenshots after the checks.

### 2026-11-06 03:47
- Supabase feedback: User's migration 08 attempt failed because `farm.soil_type` was already absent. Fixed the migration to check each legacy label only when present and use `DROP COLUMN IF EXISTS`, preserving mapping validation for remaining labels and supporting reruns.
- Confirmed from user screenshots: Demo seed is present (15 farmers, 15 farms, 10 crops, 30 plantings, 20 harvests); marked the seed task complete. Migration 08 remains pending until a successful rerun is confirmed.
- Files changed: `database/08_normalize_lookup_columns.sql`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Test CRUD, filters, dropdowns, joins, dashboard, and reports against populated tables.

### 2026-11-06 03:41
- Done: Added `PROJECT_DOCUMENTATION.md` with project description/objectives, functional requirements, ERD, UNF-to-3NF explanation, data dictionary, constraints, user guide, and test evidence.
- Done: Added guarded `database/08_normalize_lookup_columns.sql` to verify labels before dropping six redundant text columns; changed farm/crop/planting/harvest models to write only lookup IDs.
- Prepared: Added idempotent, fictional demo data in `database/09_seed_demo_data.sql` (15 farmers/farms, 10 crops, 30 plantings, 20 harvests). Database instructions require the student to run both SQL scripts in Supabase.
- Test result: Read-only migration guards returned zero mismatches across farm/crop/planting/harvest. All 49 PHP files pass syntax checks. Apache smoke-tested 18 authenticated pages (HTTP 200, no app error banners). All 94 static local link/form targets and 5 dashboard card routes resolve.
- Access check: 12 protected routes redirect unauthenticated visitors to login; the login page returns HTTP 200.
- Not complete: SQL migrations/seed have not been applied; final CRUD verification against the new schema and populated data is pending. Screenshot capture and rendered responsive checks are also pending because headless browsers failed on GPU startup.
- Files changed: PROJECT_DOCUMENTATION.md, PROJECT_REQUIREMENTS.md, database/08_normalize_lookup_columns.sql, database/09_seed_demo_data.sql, models/Farm.php, models/Crop.php, models/Planting.php, models/Harvest.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Student runs migrations 08 and 09 in Supabase; then rerun CRUD and report tests with demo rows and capture screenshots.

### 2026-11-06 03:27
- Partial: Reviewed responsive markup across dashboard, modules, forms, and reports. Navigation wraps, forms/filters use narrow-first grids, and data tables are inside horizontal-scroll wrappers.
- Not visually verified: Chrome and Edge headless rendering both terminated with GPU-process errors, so actual 390px, 768px, and 1440px viewport rendering could not be inspected.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Open Chrome DevTools Device Toolbar and visually verify at 390px (phone), 768px (tablet), and 1440px (desktop); fix any observed issues.

### 2026-11-06 03:24
- Done: Reviewed application error handling and user-facing messages. Database connection errors and module failures use generic friendly messages; login errors do not reveal whether a username exists. Found no code that prints exception details, SQLSTATEs, or credentials.
- Test result: Scanned PHP files for raw exception/detail output and found none; all 49 PHP files pass PHP syntax checks.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Check responsive layouts at desktop, tablet, and phone widths.

### 2026-11-06 03:21
- Done: Audited create/edit validation across all five modules for required fields, input lengths, numeric ranges, dates and logical date order, and existing foreign keys/lookups.
- Fixed: Crop growing-period validation now compares the normalized digit string to PostgreSQL's integer maximum before casting, preventing oversized input from overflowing and being silently accepted. Applied to create and edit.
- Test result: PHP syntax checks passed for all 10 create/edit pages. Apache form checks confirmed 2147483648 is rejected and the maximum valid integer 2147483647 passes the range check. No test records were inserted.
- Files changed: crops/create.php, crops/edit.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Review user-facing errors for friendly wording and technical-detail leakage.

### 2026-11-06 03:15
- Done: Added the Estimated Harvest Value report. It calculates `quantity_kg * selling_price_per_kg`, shows total value, quantity and record count, and breaks results down by crop. Includes a harvest date range and a Reports landing page link.
- Test result: PHP syntax checks passed; Apache aggregate queries passed with and without a date range. Current data returns 0 harvest records, 0 quantity, and 0 estimated value, so nonzero totals await sample harvest data. Unauthenticated access redirects to login.
- Files changed: models/Report.php, reports/index.php, reports/estimated_harvest_value.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Audit form validation across modules.

### 2026-11-06 03:11
- Done: Added the Total Harvested Quantity report with harvest-date filters, total kilograms and record count, plus a quality-grade breakdown. Added it to the Reports landing page.
- Test result: PHP syntax checks passed; Apache aggregate queries passed with and without a date range. Supabase currently returns 0 harvest records and 0 kg across 3 quality grades. Unauthenticated access redirects to login.
- Files changed: models/Report.php, reports/index.php, reports/total_harvested_quantity.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build the Estimated Harvest Value report.

### 2026-11-06 03:08
- Done: Added the Harvest Records report with quality-grade and harvest-date filters, search, sortable columns, pagination, and JOINed farmer/farm/crop/quality/remark details. Added it to the Reports landing page.
- Test result: PHP syntax checks passed; Apache queries passed for quality options, unfiltered/paginated records, and combined quality/date/search filters. Supabase has 3 quality grades and 0 harvest records. Unauthenticated access redirects to login.
- Files changed: models/Report.php, reports/index.php, reports/harvest_records.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build the Total Harvested Quantity report.

### 2026-11-06 03:05
- Done: Added the Plantings by Status report with summary counts, status/date filters, search, sortable planting details, and pagination. Added a link on the Reports landing page.
- Test result: PHP syntax checks passed; Apache query checks passed for lookup statuses, status summaries, unfiltered/paginated records, and combined status/date/search filters. Database currently has 2 status options and 0 planting rows. Unauthenticated access redirects to login.
- Files changed: models/Report.php, reports/index.php, reports/plantings_by_status.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build the Harvest Records report.

### 2026-11-06 03:02
- Done: Added the Crops Planted per Farm report with farm selection, planting-date range, text search, sortable columns, pagination, and farm/farmer/crop/status joins. Added it to the reports landing page.
- Test result: PHP syntax checks passed; Apache database queries for farm options, unfiltered report, paginated report, and date/search filters passed with zero rows (Supabase tables are empty). Unauthenticated report access redirects to login. Fixed empty-date binding discovered in the first query check.
- Files changed: models/Report.php, reports/index.php, reports/crops_per_farm.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build the Plantings by Status report.

### 2026-11-06 03:00
- Test result: Dashboard content and database queries loaded for Admin and Staff; unauthenticated visitors were redirected to login.
- Done: Added a Reports landing page and Farmers and Farms report with search, address filter, sorting, pagination, and LEFT JOINs so farmers without farms remain visible.
- Test result: Report model/page passed PHP syntax checks; Apache database query checks passed with 0 results (the Supabase core tables are empty); report route redirects logged-out visitors to login. Fixed boolean parameter binding found during the first query check.
- Files changed: models/Report.php, reports/index.php, reports/farmers_farms.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build the Crops Planted per Farm report.

### 2026-11-06 02:53
- Test result: Harvest module passed date validation, create, view/JOINs, lookup labels, search, quality filter, sorting, edit, confirmed delete, and Staff restriction. All temporary test records were removed.
- Done: Added `index.php` dashboard with database summary cards, recent planting and harvest tables, planting counts by status, total harvested quantity, and module links.
- Files changed: index.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build the filterable Farmers and Farms report.

### 2026-11-06 02:42
- Done: Plantings tests passed for date validation, create, JOINed details, status filtering, search, sorting, edit, confirmed deletion, and Staff restriction; all temporary records were removed.
- Done: Built the Harvests module with PDO model, lookup model, related-data JOINs, quality filter, remarks/quality dropdowns, validation, CRUD pages, and Admin-only confirmed deletion.
- Done: Role checks are applied to module deletes; foreign-key-blocked deletes return friendly messages.
- Files changed: models/Harvest.php, models/Lookup.php, harvests/index.php, harvests/create.php, harvests/edit.php, harvests/view.php, harvests/delete.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build the dashboard summary and recent activity tables.

### 2026-11-06 02:40
- Test result: Crops module passed validation, create, view, category/season labels, search, category filter, sorting, edit, confirmed delete, and Staff delete restriction. Temporary crop removed.
- Done: Built the Plantings module with farm/farmer/crop JOINs, status lookup, status filter, search, sorting, pagination, CRUD pages, numeric/date validation, and Admin-only confirmed deletion.
- Decision: Planting writes keep the legacy status label synchronized with the lookup ID until safe column cleanup.
- Files changed: models/Planting.php, plantings/index.php, plantings/create.php, plantings/edit.php, plantings/view.php, plantings/delete.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build Harvests with `models/Harvest.php`, `models/Lookup.php`, and harvest pages.

### 2026-11-06 02:15
- Test result: Farms module passed validation, create, view/JOINs, search, irrigation filter, sorting, edit, confirmed delete, and Admin-only delete checks. Temporary farm and farmer records were removed.
- Done: Completed the Crops module with PDO model, search, crop-category filter, sorting, pagination, CRUD pages, lookup-backed category/season dropdowns, validation, and Admin-only confirmed delete.
- Decision: Crop create/update keeps legacy category and season labels synchronized with lookup IDs until safe column cleanup.
- Files changed: models/Crop.php, crops/index.php, crops/create.php, crops/edit.php, crops/view.php, crops/delete.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build the Plantings module with farm, farmer, and crop JOINs.

### 2026-11-06 02:14
- Done: Completed the Farms module with PDO model, farmer JOINs, search, irrigation-type filter, sorting, pagination, create, edit, view, and Admin-only confirmed delete pages.
- Decision: Farm writes keep legacy soil/irrigation text labels synchronized with selected lookup IDs until a later cleanup migration.
- Files changed: models/Farm.php, farms/index.php, farms/create.php, farms/edit.php, farms/view.php, farms/delete.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build the Crops module with crop-category filtering.

### 2026-11-06 02:14
- Done: Completed the Farmers module with PDO model, search, address filter, sortable columns, pagination, create, edit, view, and Admin-only confirmed delete pages.
- Validation: Required fields, field lengths, and registration date are checked server-side; dynamic output is escaped and database errors are friendly.
- Files changed: models/Farmer.php, farmers/index.php, farmers/create.php, farmers/edit.php, farmers/view.php, farmers/delete.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build the Farms module with farmer JOIN and irrigation-type filter.

### 2026-11-06 02:02
- Done: Added `auth/login.php` with input checks, prepared model lookup, `password_verify()`, session ID regeneration, and generic login/connection errors.
- Files changed: auth/login.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Create `auth/logout.php`.

### 2026-11-06 02:05
- Verified: Local HTTP login test accepted valid admin credentials and redirected to the dashboard path; invalid credentials returned to the login page.
- Done: Added `auth/logout.php` to clear session data, expire the session cookie, destroy the session, and redirect to login.
- Role note: Authentication helpers support Admin-only routes; apply and verify role checks as module pages are implemented.
- Files changed: auth/logout.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Start the Farmers module with `models/Farmer.php` and its CRUD pages.

### 2026-11-06 01:57
- Done: Added `models/User.php` with prepared lookups by username and ID, password-hashed account creation and password updates, and role validation.
- Files changed: models/User.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build `auth/login.php` with `password_verify()` and session regeneration.

### 2026-11-06 01:56
- Done: Local Apache test page connected successfully to Supabase using PDO pgsql. Removed `test_db_connection.php` after the successful check.
- Decision: The Supabase session pooler settings work from this environment.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md; removed temporary test_db_connection.php
- Next: Create `models/User.php` for authentication queries.

### 2026-11-06 01:44
- Prepared: `test_db_connection.php` calls the PDO connection and displays a generic success/failure message without technical details.
- Not yet verified: The private credentials screenshot still showed a placeholder Supabase host and was unsaved, so the connection cannot be tested yet.
- Files changed: test_db_connection.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Save a valid host and rotated password locally, visit the temporary page, and report its result; remove the page after success.

### 2026-11-06 01:44
- Done: Added `includes/header.php` with Tailwind CDN, navigation to the dashboard and all modules, page title support, and escaped one-time success/error messages.
- Decision: Shared flash messages use `$_SESSION['success']` and `$_SESSION['error']`.
- Files changed: includes/header.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Create `includes/footer.php`.

### 2026-11-06 01:50
- Done: Added `includes/footer.php` to close the shared page markup and show the system footer.
- Files changed: includes/footer.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Create `includes/auth_check.php` for login protection and role checks.

### 2026-11-06 01:55
- Done: Added `includes/auth_check.php` with login redirection, an Admin role helper, and an Admin-only guard.
- Files changed: includes/auth_check.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Test the PDO connection using a temporary web page and remove the test page afterward.

### 2026-11-06 01:36
- Done: Added `config/db_credentials.example.php` with placeholders matching `Database.php` and ignored the private `config/db_credentials.php` file in `.gitignore`.
- Files changed: config/db_credentials.example.php, .gitignore, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Build the shared `includes/header.php` layout.

### 2026-11-06 01:35
- Done: Added `config/Database.php`; it loads private credentials, connects via PDO pgsql with SSL required, configures exception/default fetch modes, and returns a generic connection error.
- Files changed: config/Database.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Add a placeholder credentials example and ignore the private credentials file in `.gitignore`.

### 2026-11-06 01:29
- Confirmed from user screenshot: Supabase SQL Editor reported â€œSuccess. No rows returnedâ€ for integrity constraints.
- Done: Marked integrity constraints complete. Saved the missing historical `harvest_remarks` migration as `database/01_harvest_remarks_migration.sql` and marked SQL-file tracking complete; the file warns not to rerun because the user already applied it.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md, database/01_harvest_remarks_migration.sql
- Next: Start Phase 2 by creating `config/Database.php` for a PDO PostgreSQL connection using the private credentials file.

### 2026-11-06 00:25
- Done: Read ap3_project.docx (requirements and the 5 table designs) and CODING_STANDARDS.md.
- Done: Created PROJECT_REQUIREMENTS.md so Codex can read the requirements as text.
- Done: Replaced the generic checklist in PROJECT_TASKS.md with the real phases.
- Fixed: Stack was wrongly written as MySQL. It is Supabase PostgreSQL. Corrected here and in AGENTS.md.
- Files changed: PROJECT_REQUIREMENTS.md (new), PROJECT_TASKS.md, PROJECT_TRACKER.md, AGENTS.md
- Next: Run Codex in the project folder, enable pdo_pgsql, start Phase 1.

### 2026-11-06 00:43
- Checked: Read the project instructions, checklist, requirements, coding standards, and original DOCX; DOCX confirms the same Supabase PostgreSQL and lookup-table requirements.
- Found: `pdo_pgsql` and `pgsql` are uncommented in `C:\xampp\php\php.ini`. The command-line PHP is PHP 8.5 and cannot load the 2023 DLLs, so its module listing is not a valid check for XAMPP's Apache PHP.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Verify the extensions in Apache's phpinfo().

### 2026-11-06 00:54
- Confirmed from user screenshot: Apache phpinfo() is using PHP 8.2.12, Thread Safety enabled, with `C:\xampp\php\php.ini` loaded. This is distinct from the PHP 8.5 CLI binary. The screenshot's summary does not show whether PostgreSQL extensions loaded.
- Files changed: PROJECT_TRACKER.md
- Next: Search the Apache phpinfo page for `pdo_pgsql` and `pgsql`; if both are present, mark Phase 0 setup complete.

### 2026-11-06 01:00
- Done: User confirmed `pdo_pgsql` and `pgsql` both work in Apache phpinfo(); marked the XAMPP task complete. Marked starting Codex from the project folder complete based on this session.
- Started: Next checklist task is confirming the five Supabase core tables and sample data.
- Found: No database credentials or connection settings are present in the workspace, and no Supabase database integration is available in this session. Cannot inspect the hosted database yet.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Run the read-only verification query above in Supabase SQL Editor or provide read-only connection access, then record the table and row counts.

### 2026-11-06 01:08
- Done (per user confirmation): Core tables exist; `harvest_remarks` has the five required values; `harvest.remark_id` was added and the old `remarks` column dropped.
- Decision: Use lookup tables for all six remaining fixed-value fields documented in PROJECT_REQUIREMENTS.md.
- Prepared: `database/02_fixed_value_lookups.sql` creates the six lookup tables, seeds documented values, adds FK columns and constraints, and copies recognized text values without dropping the original columns.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md, database/02_fixed_value_lookups.sql
- Next: Run the SQL in Supabase SQL Editor, check that existing values were mapped, and confirm success. Sample-data presence remains unconfirmed.

### 2026-11-06 01:12
- Confirmed from user screenshot: Supabase SQL Editor reported â€œSuccess. No rows returnedâ€ after the fixed-value lookup migration.
- Done: Marked lookup tables/FK migration task complete. Added a separate read-only mapping verification query because successful DDL execution does not show whether existing text values all matched.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md, database/03_verify_fixed_value_lookups.sql
- Next: Run the verification query and share its `populated_rows` and `unmapped_rows` results. Sample-data presence remains unconfirmed.

### 2026-11-06 01:16
- Confirmed from user screenshot: the mapping query returned 0 populated rows and 0 unmapped rows for all six fields. No existing fixed-value data needed mapping; this does not by itself establish core table row counts.
- Done: Marked the mapping verification complete. Started checking core table sample-data counts.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md, database/04_check_core_table_counts.sql
- Next: Run `database/04_check_core_table_counts.sql` in Supabase SQL Editor and share the five counts.

### 2026-11-06 01:23
- Confirmed from user screenshot: all five core tables currently contain 0 rows.
- Done: Marked the sample-data check complete with the finding that tables are empty; sample data is still needed for the demo.
- Prepared next task: `database/05_users_and_initial_accounts.sql` creates `public.users` and inserts admin/staff accounts with PHP `password_hash()` hashes.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md, database/05_users_and_initial_accounts.sql
- Next: Run the users SQL in Supabase SQL Editor and confirm success. Temporary passwords were generated for the accounts and are shared separately with the student.

### 2026-11-06 01:19
- Confirmed from user screenshot: Supabase SQL Editor reported â€œSuccess. No rows returnedâ€ for `public.users` creation and the admin/staff inserts.
- Done: Marked the users table/accounts task complete.
- Prepared next task: `database/06_add_audit_timestamps.sql` adds `created_at` and `updated_at` to farmer, farm, crop, planting, and harvest, and installs update triggers for `updated_at`.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md, database/06_add_audit_timestamps.sql
- Next: Run the audit timestamp SQL in Supabase SQL Editor and confirm success.

### 2026-11-06 01:24
- Confirmed from user screenshot: Supabase SQL Editor reported â€œSuccess. No rows returnedâ€ for audit timestamp columns and update triggers.
- Done: Marked the audit timestamp task complete.
- Prepared next task: `database/07_add_integrity_constraints.sql` adds required fields, nonblank checks, uniqueness, numeric range checks, and planting date order validation.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md, database/07_add_integrity_constraints.sql
- Next: Run the constraints SQL in Supabase SQL Editor and confirm success.

### 2026-11-06 00:16
- Done: Created the project folder FMidterm_Project in xampp\htdocs (empty).
- Done: Installed OpenAI Codex (v0.166.0). It first ran from C:\Windows\system32 by mistake.
- Fixed: Learned to start Codex from the project folder using `cd /d C:\xampp\htdocs\FMidterm_Project` then `codex`.
- Done: Created PROJECT_TRACKER.md, PROJECT_TASKS.md and AGENTS.md to keep progress between sessions.
- Files changed: PROJECT_TRACKER.md, PROJECT_TASKS.md, AGENTS.md (new)
- Next: Run Codex inside the project folder and complete Task 1 in PROJECT_TASKS.md.

