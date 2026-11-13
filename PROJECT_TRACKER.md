# Project Tracker: FMidterm_Project

**Project:** Agriculture Information System (AIS), AP3 Midterm, Cebu Technological University
**Location:** C:\xampp\htdocs\FMidterm_Project (run at http://localhost/FMidterm_Project/)
**Stack:** Native PHP (OOP models + PDO), Supabase PostgreSQL, Tailwind CSS via CDN, XAMPP Apache. No frameworks, no Composer.
**Purpose:** Track progress so work can continue even if the AI runs out of tokens or the session ends.

## Key Files
- AGENTS.md: instructions Codex reads automatically
- PROJECT_REQUIREMENTS.md: what must be built (plain-text copy of `ap3 project.docx`)
- CODING_STANDARDS.md: the teacher's coding style (must be followed)
- PROJECT_TASKS.md: task checklist
- `ap3 project.docx`: original guidelines (the DOCX wins if anything conflicts)

## Current Status

- **Phase:** 9 Final testing and submission
- **Last updated:** 2026-11-14
- **Currently working on:** Verifying the `roles` lookup integration after the user's Supabase migration and GitHub push.
- **Next step:** Test Admin and Staff login against `users.role_id`, then refresh the editable Word/PDF ERD and data dictionary.

## Progress Log

Newest entry at the top. Format:
`[Date Time] What was done | Files changed | What is next`

### 2026-11-14 01:26
- User committed and pushed 19 files to `main` in commit `fd69bb5` (`Add normalized roles lookup and validation updates`). Verification with `git status --short` shows only `Reference ERD.jfif` and `SYSTEM_DOCUMENTATION.pdf` remain untracked; neither was included in the push.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Test Admin and Staff login against `users.role_id`, then refresh the editable Word/PDF ERD and data dictionary.

### 2026-11-14 01:17
- Prepared the `roles` lookup and `users.role_id` migration, updated the User model to join the role label, and updated the Markdown ERDs and data dictionaries. The source DOCX requires two roles but does not prescribe a separate role table; this is a normalized implementation extension matching the supplied reference ERD.
- The user's Supabase screenshot confirms migration 23 ran: the foreign-key, copied-role, and old-column-removal checks all return true. No GitHub changes were made.
- The migration copies existing `users.role` values and verifies every account before dropping the old text column in one transaction. RLS is enabled on `roles`.
- XAMPP PHP syntax passed for all 52 PHP files; local migration-order/security checks and `git diff --check` passed. Live Admin/Staff login tests and refreshed Word/PDF exports remain pending.
- Files changed: database/23_role_lookup_table.sql, database/21_audit_all_relation_row_counts.sql, models/User.php, PROJECT_DOCUMENTATION.md, FINAL_SYSTEM_DOCUMENTATION.md, PROJECT_REQUIREMENTS.md, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Test Admin and Staff login, update the Word/PDF exports, then teach the manual GitHub commit/push.

### 2026-11-13 23:03
- Audited all five modules' create/edit validation. Added friendly handling for PostgreSQL duplicate conflicts across all modules and blocked future farmer registration dates.
- XAMPP PHP lint passed for all 52 PHP files; duplicate SQLSTATE test passed for 23505 and 23503; `git diff --check` passed. No live database/browser workflow test was run.
- Files changed: includes/validation_helpers.php, 10 module create/edit pages, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Retry the Farm form once and confirm duplicate validation is clear; then continue Farm location confirmation and pending authenticated workflow checks.

### 2026-11-13 22:52
- Added server-side and date-picker validation to reject harvest dates after today on create and edit; past dates must still be on or after the planting date.
- XAMPP PHP syntax checks passed for both pages; a local date-boundary check confirmed yesterday passes and tomorrow is rejected. Live browser/database testing remains pending.
- Files changed: harvests/create.php, harvests/edit.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Refresh the Farms page to confirm Farm IDs 13 and 18; continue authenticated print-preview and end-to-end workflow checks.

### 2026-11-12 22:56
- User's Supabase screenshot confirms the location update succeeded and returned 15 farms. All 15 former generated location labels now cycle across the six user-provided names; farm ID 13 is Cogon, Compostela, Cebu City.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md; Supabase farm.location data updated by the user-run SQL script
- Next: Refresh the AIS Farms page and visually confirm the saved locations.
### 2026-11-12 23:04
- Reviewed the pending worktree and confirmed `origin` points to the AIS GitHub repository. The private credentials file is ignored. Excluding the untracked classmate reference PDF from publication; preparing the AIS documentation, report updates, and SQL audit/update scripts for push.
- Files changed: PROJECT_TRACKER.md; no GitHub changes yet
- Next: Commit the reviewed AIS files and push to `origin/main`.
### 2026-11-12 23:05
- Published AIS report updates, final system documentation, and SQL audit/location scripts to GitHub. Commit `1bda119` pushed to `origin/main`; private credentials and the classmate reference PDF were excluded.
- Files changed: 15 AIS files in commit `1bda119`, including report code, documentation, tracker/tasks, and SQL scripts
- Next: Refresh the Farms page and verify Farm IDs 13 and 18; continue pending authenticated print-preview and end-to-end workflow checks.
### 2026-11-12 22:53
- Prepared a transactional SQL update for only locations matching the generated “Farm Location NN, Cebu” pattern. It cycles equally by farm_id through the six real location strings from the user's screenshot and returns updated farm IDs/names/locations for review.
- Files changed: database/22_replace_generated_farm_locations.sql, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: User runs the script in Supabase SQL Editor and confirms the returned rows; then verify the Farm list and mark the data task complete.
### 2026-11-12 22:47
- Traced Farm ID 13's edit flow. `farms/edit.php` passes the submitted location to `Farm::update()`, which persists `location`; the only code generating “Farm Location NN, Cebu” is the archived demo seed script. No runtime PHP reset was found. Waiting for verified replacement location(s).
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Prepare a targeted SQL update only after the user confirms the actual location and whether other placeholders should be changed.
### 2026-11-12 22:40
- Reviewed the user's Supabase SQL Editor screenshot. All 13 relations have positive row counts: crop 14, crop_categories 5, farm 21, farmer 24, harvest 23, harvest_quality_grades 3, harvest_remarks 5, irrigation_types 3, planting 32, planting_statuses 2, recommended_seasons 3, soil_types 4, users 2. Marked the populated-table audit complete.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Retain the read-only audit SQL for future checks; investigate individual-column completeness separately if needed.
### 2026-11-12 22:28
- Added a read-only SQL audit that returns row counts and a HAS DATA/EMPTY status for all 13 required relations. Marked the data check in progress; existing seed scripts are not proof of current contents.
- Files changed: database/21_audit_all_relation_row_counts.sql, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Run the audit in Supabase SQL Editor; only confirm after all 13 counts are greater than zero.
### 2026-11-12 22:26
- Reviewed the user's updated Farmers and Farms screenshot and audited output columns and joins across all six reports. Required report fields use NOT NULL source columns and complete joins; only planting-status and quality-grade summaries intentionally retain categories with zero counts.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Keep incomplete farmer/farm relationships out of the joined report; address any row-level issue the user finds in another report.
### 2026-11-12 22:17
- Read the AP3 DOCX requirements and audited all six report queries. Changed Farmers and Farms to use required farm and lookup joins, and changed planting-status lookup to an inner join because its FK is NOT NULL. Removed display fallbacks for those fields. PHP syntax checks passed; no database records were modified.
- Files changed: models/Report.php, reports/farmers_farms.php, reports/crops_per_farm.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Refresh Farmers and Farms and verify all displayed values against the live result; keep farmers without farms in the Farmers module.
### 2026-11-12 22:08
- Ran PHP syntax checks on the shared header and all report PHP files; all passed. Checked the local report URL, which correctly redirected to login without an authenticated session. Headless Chrome and Edge could not create a PDF because their GPU processes failed, so visual preview verification remains pending.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Verify print previews in an authenticated browser session.
### 2026-11-12 22:03
- Set wide detail reports to landscape and switched print tables to automatic column sizing with 8 pt text; summary reports retain portrait orientation.
- Files changed: includes/header.php, four detail reports, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Confirm the landscape previews are readable and include every matching row.
### 2026-11-12 22:00
- Changed the shared print page box to zero margins with explicit 10 mm content padding, alongside temporarily clearing the document title, to suppress Chrome's repeated title and URL headers while retaining printable margins.
- Files changed: includes/header.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Reopen a report print preview and confirm the URL/title header is gone while the report heading and full rows remain readable.
### 2026-11-12 21:54
- Removed the redundant “Report” suffix from all six report document titles so browser tabs and browser-generated print headers use concise report names.
- Files changed: six report pages, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Reopen a report print preview and confirm the concise title appears.
### 2026-11-12 21:46
- Updated Print report on all six reports to open a print view containing every row matching the active filters and sort order. Date filters carry through on both summary reports; on-screen pagination remains at 10 rows.
- Files changed: includes/report_actions.php, includes/header.php, six report pages, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Confirm each report's complete result set appears in the print preview and check portrait PDF output.
### 2026-11-12 21:42
- Removed the Print all results link, unpaginated query mode, and automatic print-on-load behavior. Current-page report printing remains available.
- Files changed: includes/report_actions.php, includes/header.php, four report pages, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Confirm current-page report printing produces upright portrait output.
### 2026-11-12 21:26
- Added Print all results to Farmers and Farms, Crops Planted per Farm, Plantings by Status, and Harvest Records. It preserves active filters and sort order, loads all matching rows in a new tab, then opens the print dialog. Print CSS repeats table headers across pages.
- Files changed: includes/report_actions.php, includes/header.php, four report pages, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Verify the all-results flow and printed pagination in the browser.
### 2026-11-12 21:18
- After the user restored Microsoft Print to PDF, set AIS report print CSS to A4 portrait with compact, wrapping table columns so the report and restored printer use portrait output.
- Files changed: includes/header.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: In Chrome Print, set More settings > Layout to Portrait, regenerate a report PDF, and confirm the text is upright.
### 2026-11-12 21:13
- Removed Microsoft Print to PDF from the Windows printer list at the user’s request after its output continued rotating the report. Chrome’s built-in Save as PDF destination remains available.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md; Windows printer entry removed
- Next: Reopen Chrome’s print dialog, select Save as PDF, and confirm the generated page is upright.
### 2026-11-12 21:10
- The regenerated PDF remained sideways after changing Microsoft Print to PDF default orientation. Restored its original Portrait preference. Evidence shows the Chrome preview is landscape while the exported PDF is portrait with sideways content; the remaining setting is Chrome More settings > Layout, which is controlled by the print dialog.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md; Windows printer preference restored
- Next: Set Chrome print Layout to Portrait for readable portrait output, or use Landscape to match the current report preview, then confirm the saved PDF.
### 2026-11-12 21:06
- Diagnosed the rotation mismatch: the report preview was landscape but Microsoft Print to PDF defaulted to Portrait. With user approval, changed that printer default to Landscape and restored the app print layout to A4 landscape with readable table sizing.
- Files changed: includes/header.php, PROJECT_TASKS.md, PROJECT_TRACKER.md; Windows Microsoft Print to PDF preference updated
- Next: Generate a fresh PDF and confirm its page and text are landscape/upright.
### 2026-11-12 21:02
- Reviewed the latest print preview and saved PDF screenshots. Chrome preview is still landscape while the saved PDF page is portrait with its content sideways. The destination remains Microsoft Print to PDF; the system-level layout setting is not visible because More settings is collapsed. App CSS changes alone have not resolved it.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: In the print dialog, expand More settings and set Layout to Portrait before printing to Microsoft Print to PDF; verify the new PDF orientation.
### 2026-11-12 20:53
- Adjusted report printing to A4 portrait with fixed-width, compact, wrapping table columns to prevent Microsoft Print to PDF from rotating landscape report pages. Screen layout is unchanged.
- Files changed: includes/header.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Reprint the report to Microsoft Print to PDF and confirm the saved page stays upright.
### 2026-11-12 20:41
- Set print pages to A4 landscape, enforced horizontal text orientation, and aligned report table cells for consistent print output.
- Files changed: includes/header.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Reopen Chrome print preview and save with the built-in Save as PDF destination to confirm page orientation.
### 2026-11-12 20:38
- Updated print CSS so report text prints in a dark, readable color even when Night mode is active. Table rows and headers also receive print-safe text colors.
- Files changed: includes/header.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Check the PDF preview again with Print report.
### 2026-11-12 19:13
- Clarified that “Save as Docs” means a Google destination in Chrome Print. Removed the separate Word-compatible .doc download button because the app cannot add entries to Chrome’s print destination list. Print report remains beside All reports on each report page.
- Files changed: includes/report_actions.php, includes/header.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: User can install Chrome’s Save to Google Drive extension to expose that destination; continue remaining Phase 9 workflow tests and screenshots.
### 2026-11-12 19:10
- Moved Print report and Save as Docs out of the global menu and into the toolbar next to All reports on each report page. The three-line menu now remains for appearance and logout.
- Files changed: includes/header.php, includes/report_actions.php, six reports, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Verify report printing and .doc downloads in the browser; continue Phase 9 workflows and screenshots.
### 2026-11-12 19:03
- Expanded the printer action from report pages to every signed-in page. It now appears as Print page in the shared three-line Settings menu; Save as Docs remains available on report pages.
- Files changed: includes/header.php, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Resume Phase 9 workflow testing and screenshot evidence.
### 2026-11-12 19:01
- Verified the local XAMPP PDO connection using the private configuration and a read-only SELECT 1/current database/schema query. The connection succeeded and returned postgres/public. The earlier failure was from the sandboxed network check; no credential values were displayed, and no database data was changed.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Continue Phase 9 workflow tests and screenshot evidence using the local system.
### 2026-11-12 18:58
- Added Print report (printer icon) and Save as Docs actions to the three-line Settings menu on all six report pages. Print uses the existing landscape print layout. Save as Docs downloads the current filtered report view as a Word-compatible .doc file that opens in Word/WPS; it is not a .docx package.
- Chose .doc because XAMPP PHP has no ZIP extension and the user did not select a format after the clarification prompt. No extra packages were added.
- Files changed: includes/header.php, six reports, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Verify both menu actions in the browser after restoring the local database connection, then continue Phase 9 workflows and screenshots.
### 2026-11-12 18:12
- Moved the report print action into the three-line Settings menu and added a printer icon; it appears on all six report pages. Asked which downloadable document format “Save as Docs” should use because native PHP has no ZIP extension for generating DOCX packages.
- Files changed: includes/header.php, six reports, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Finish the document export after the user chooses .docx or compatible .doc; then return to Phase 9 workflow verification.
### 2026-11-12 18:10
- Added print-specific styling for all six reports.
- Files changed: includes/header.php, includes/print_button.php, six reports, PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Verify the print flow in a browser when the XAMPP database connection is available; continue remaining Phase 9 workflow tests and screenshots.
### 2026-11-12 18:05
- Reviewed the original AP3 DOCX and current Supabase schema. All 13 expected relations are present; the source requires created_at and updated_at where appropriate and does not require deleted_at.
- Local PHP syntax scan passed and the signed-out dashboard route redirected to login. Local PDO could not connect; the private config host differs from the AIS project, and a connection attempt using the AIS host in memory also failed. No credentials were displayed and no application code or database data was changed.
- Files changed: PROJECT_TASKS.md, PROJECT_TRACKER.md
- Next: Verify the private connection settings/network, then complete live workflow tests, screenshots, demo rehearsal and submission cleanup.
### 2026-11-12 04:35
- Reviewed the implemented core and lookup relations against the DOCX 3NF requirement. The current normalized lookup design already meets the documented requirement; repeated farmer phone numbers do not violate 3NF, and the guideline does not require a unique-phone constraint.
- Clarified the functional-dependency and phone-number rationale in the project documentation. No schema migration or Supabase data change is needed for 3NF.
- Files changed: `PROJECT_DOCUMENTATION.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Complete outstanding Phase 9 workflows and capture final screenshots.

### 2026-11-12 04:22
- Aligned the remaining Phase 9 database test item with the DOCX: verify workflows against the current Supabase records; replacing demo rows is not required solely because they are synthetic.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Complete end-to-end workflow testing, capture module/device screenshots, rehearse the demo, and finish submission cleanup.

### 2026-11-12 04:05
- Re-read the source DOCX and confirmed its data wording: the system must connect to Supabase and demonstrate features using actual database records. It does not explicitly prohibit synthetic sample/demo records or require their deletion.
- Decision: Follow the DOCX as requested. Keep current seeded and unverified records; do not remove or replace them solely because they are synthetic. The prior cleanup recommendation is not a DOCX requirement. No Supabase data was changed.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Continue remaining Phase 9 testing, screenshots, demo rehearsal, and final cleanup.

### 2026-11-12 03:50
- Received the completed Supabase read-only audit. It confirms seed matches: farmers 8–22, farms 7–21, crops 4–13, plantings 3–32, and likely seeded harvests 2–21. The three older placeholder clues (`farm -14`, `AIS`, `FARM-LILOAN`) are absent from current data; migration 11 should remain unused.
- Preserved uncertain records: farmers 24, 25, 27–31; farms 22–24 and 27; crop 14 (`Manga` / `Apple`); harvests 22–23. Four uncertain farms have no plantings but belong to seeded farmers 8, 9, and 12. Harvests 22–23 depend on seeded plantings 30 and 32, so a demo cleanup needs an explicit decision about these links.
- No data was modified. Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Obtain verified replacement data or confirm a specific cleanup scope that resolves the dependent uncertain records before preparing any destructive SQL.

### 2026-11-12 03:40
- User shared all five read-only inventories. Current counts are 22 farmers, 19 farms, 11 crops, 30 plantings, and 22 harvests. Visible farmer/farm rows and planting sequences match the archived demo seed patterns; crop ID 14 (`Mango` / `Apple`) is absent from the seed script; harvest count is two above the 20 seeded rows. The extra farmer, farm, crop, and harvest records are not all attributable from the screenshots.
- No Supabase rows were changed. Do not delete unverified records. Need verified real replacement data or the user's authorization to remove only the identifiable seeded records.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Confirm replacement dataset or removal scope, then prepare a reviewed migration that preserves unverified records.

### 2026-11-12 03:39
- User shared the bottom of the Crops result. Crop ID 14 is `Mango` / `Apple` (30 growing days, Fruit, Wet Season), which is not among the 10 entries in the archived seed script. Its origin is unverified; no data was changed.
- Files changed: `PROJECT_TRACKER.md`
- Next: Review the Plantings inventory, then seek source confirmation for crop ID 14 and verified replacement records or removal authorization before data cleanup.

### 2026-11-12 03:35
- User ran the Crops inventory query; Supabase returned 11 rows. The visible crop names and varieties match the archived seed script, which defines 10 crops, so the remaining row needs review before classifying all crop records.
- Files changed: `PROJECT_TRACKER.md`
- Next: Inspect the bottom of the crop result to identify the extra row, then review Plantings. Do not change Supabase data without verified replacements or removal authorization.

### 2026-11-12 03:31
- User ran the Farms inventory query; Supabase returned 19 rows. Visible sequential `Farm Location NN, Cebu` values and 0.35-hectare increments match the archived generated demo data. No data was changed.
- Files changed: `PROJECT_TRACKER.md`
- Next: Review the Crops and Plantings inventory results, then request verified replacement data or authorization before any deletion.

### 2026-11-12 03:29
- User ran the Farmers inventory query; Supabase returned 22 rows. The visible records (Ana Alonzo onward) use sequential demo-style contact numbers and registration dates matching the archived seed data. This supports that those visible entries are synthetic; it does not authorize changing or deleting them.
- Files changed: `PROJECT_TRACKER.md`
- Next: Review the Farms inventory, then Crops and Plantings. Obtain verified replacement data or user authorization before preparing destructive cleanup.

### 2026-11-12 03:24
- User ran the inventory SQL in Supabase. The visible result is the Harvests query with 22 rows; its farmer, farm, and crop values match the demo-style data in the archived seed script. No data was changed.
- Supabase displayed only the last result set from the multi-query script. Farmers, Farms, Crops, and Plantings still need to be run and reviewed individually before deciding how to replace or remove records.
- Files changed: `PROJECT_TRACKER.md`
- Next: Run the Farmers, Farms, Crops, and Plantings SELECT statements separately and share their results; keep all data migrations on hold.

### 2026-11-12 03:17
- Prepared `database/20_review_current_records.sql`, a read-only inventory of all five business tables with their linked farmer/farm/crop and lookup values. It does not label records synthetic or modify data; the user must compare results with verified real records.
- No Supabase database connector is available in this session, so live rows cannot be inspected here. The inventory is ready for the user to run one SELECT at a time in Supabase SQL Editor.
- Files changed: `database/20_review_current_records.sql`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Run the inventory queries and provide verified real-record corrections, especially for `farm -14`, `AIS`, and `FARM-LILOAN`, or authorize removal of confirmed synthetic rows. Keep migration 11 on hold.

### 2026-11-12 03:13
- Fixed `database/19_merge_exact_duplicate_farmers.sql`: it now outputs the merge map and drops its temporary table before `COMMIT`, avoiding a post-commit reference that can fail when Supabase uses another connection. The final duplicate audit does not depend on the temporary table.
- Supabase read-only checks returned no exact-duplicate farmer pairs and no farms owned by IDs 23–26. The earlier script error was on its post-commit temporary-table output, so its data transaction had already committed; the current audits confirm no duplicate cleanup remains for these candidates.
- Files changed: `database/19_merge_exact_duplicate_farmers.sql`, `PROJECT_TRACKER.md`
- Next: Review synthetic records and obtain verified real-record mappings or authorization to remove them.

### 2026-11-12 02:23
- Prepared `database/19_merge_exact_duplicate_farmers.sql` after the user authorized fixing the duplicate records. It merges only rows with the same normalized first/last name, contact number, and address; retains the lowest farmer ID; reassigns dependent farms before deleting duplicate farmer rows; and aborts if farm-name uniqueness would be violated. It leaves differently named rows that merely share a phone/address untouched.
- This cleanup is destructive for exact duplicate farmer rows and remains unapplied. The project instructions require the user to run it in Supabase SQL Editor; mark complete only after the user confirms the output. No live data has been changed by this workspace.
- Files changed: `database/19_merge_exact_duplicate_farmers.sql`, `PROJECT_DOCUMENTATION.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: User runs the script and shares its merge-map and remaining-candidate output; then verify the cleanup and linked farms.

### 2026-11-12 02:20
- User screenshot confirms `database/18_prevent_duplicate_records.sql` ran in Supabase and installed duplicate guards for Farmer, Farm, Crop, Planting, and Harvest. The screenshot shows 10 event rows because each trigger covers INSERT and UPDATE; adjusted the verification query to group those into five table/trigger rows on future runs.
- The migration adds database-side checks only. It did not update or delete existing records; duplicate farmer candidates remain for review.
- Files changed: `database/18_prevent_duplicate_records.sql`, `PROJECT_DOCUMENTATION.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Run the read-only candidate audit and review matching farmer records and their linked farms before any cleanup.

### 2026-11-12 02:15
- Compared website model duplicate rules with current migration history. Past data capitalization/address/farm-label migrations are already recorded as user-confirmed Supabase updates. Prepared migration 18 to mirror duplicate prevention across all five core tables, including serialization for concurrent inserts; it does not update or delete existing data.
- Supabase tools are not available in this runtime and the project instructions require the student to run SQL in Supabase SQL Editor. Migration 18 is pending; do not mark the database change complete until the user confirms success and verifies five triggers.
- Files changed: `database/18_prevent_duplicate_records.sql`, `PROJECT_DOCUMENTATION.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: User runs migration 18 and shares the result; then run the read-only duplicate audit and review candidates.

### 2026-11-12 02:04
- Re-read the normalization and documentation requirements in `ap3 project.docx`. Corrected the analysis to start from its five separate core tables; removed the unsupported claim that the guideline defines one wide UNF table with repeating groups. Documented why 1NF and 2NF do not require structural changes for the listed atomic fields and single-column keys.
- Rebuilt the report as an editable Word document with a normalization comparison table and all 13 implemented relations explicitly listed (12 operational + separate `users` authentication support). Replaced the inconsistent SVG summary. Kept device screenshots and pending database/data checks as student work. Updated the Markdown reference and superseded-report note. No Supabase schema or data was changed.
- Validation: Word package XML parses; six actual Word tables found; normalization and 13-relation inventory markers present; package root relationship resolves to `word/document.xml`; old synthetic `AGRICULTURE_UNF` label absent.
- Files changed: `PROJECT_DOCUMENTATION.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`, `.gitignore`, `normalization_unf_to_3nf.svg`, `submission_materials/README.md`, `submission_materials/build_corrected_report.ps1`, `submission_materials/AIS_Final_Project_Report_Corrected.docx`
- Next: Review the corrected report in Word/WPS for visual page breaks, then complete the outstanding data audit and student-owned screenshot evidence.

### 2026-11-12 01:33
- Fixed the Word package root relationship that caused WPS to report the report as corrupted. Created a separate repaired copy because WPS had the original file open and locked it. All XML parts parse; root office-document relationship resolves; confirmed 12 pages and 12 embedded infographic PNGs.
- Checked Google Docs setup: no document session is connected, and the Google Drive connector is disabled by the workspace administrator and unavailable for installation here. The plugin request was ineligible; no online document was created.
- Files changed: `.gitignore`, `PROJECT_TRACKER.md`, `submission_materials/README.md`, `submission_materials/build_final_report.ps1`, `submission_materials/AIS_Final_Project_Report_Repaired.docx`
- Next: Open the repaired copy. To create or edit the Google Doc directly, the workspace administrator must enable the Google Drive connector and the user must connect the intended account.

### 2026-11-12 01:20
- Created a twelve-page visual Word report based on the original `ap3 project.docx`, with infographic pages covering architecture, workflow, ERD, normalization, requirement coverage, integrity, data dictionary, user guide, and remaining evidence. Added a local regeneration script and kept device screenshots/final data checks visibly pending.
- Validation: DOCX ZIP package and XML parsed successfully; confirmed 12 pages and 12 embedded PNGs. No connected Google Docs session was available, so the deliverable is a local `.docx` ready to open or import.
- Files changed: `.gitignore`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`, `submission_materials/README.md`, `submission_materials/AIS_Final_Project_Report.docx`, `submission_materials/build_final_report.ps1`, `submission_materials/report_visuals/pages/*.png`
- Next: Student inserts actual system and iOS/Android screenshots, adds the emulated tablet and desktop views, then completes the final populated-data and duplicate-identity review.

### 2026-11-12 01:03
- Clarified responsive evidence instructions: use the available physical iOS and Android phones, emulate a 768 CSS-pixel tablet viewport and label it simulated, and capture a 1440 CSS-pixel desktop view.
- Files changed: `PROJECT_DOCUMENTATION.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`, `submission_materials/screenshots/README.md`
- Next: Student captures final application and responsive evidence; run the database duplicate audit before any data cleanup.

### 2026-11-12 00:59
- Found the source guideline in the project root as `ap3 project.docx` (space instead of underscore); it is tracked by Git and was not missing. Corrected project references that used the wrong underscore spelling, and re-read the source DOCX.
- Completed the report against the source: added a guideline coverage table, complete data dictionary, role/workflow guide, and a dated test-status matrix; corrected stale credential, responsive evidence, and screenshot statements. Added a final-test worksheet and aligned screenshot instructions with the user's real-data requirement. Final screenshot/device evidence remains for the student.
- Files changed: `AGENTS.md`, `PROJECT_REQUIREMENTS.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`, `PROJECT_DOCUMENTATION.md`, `submission_materials/README.md`, `submission_materials/screenshots/README.md`, `submission_materials/test-results/README.md`
- Next: Run the duplicate audit and confirm real data/provenance; student captures approved screenshots and responsive evidence.

### 2026-11-11 21:21
- Broadened farmer create/edit duplicate detection to flag matching normalized contact plus address or matching full name plus address; added a read-only duplicate candidate audit across Farmer, Farm, Crop, Planting, and Harvest. Clarified that 3NF prevents dependency redundancy but does not merge duplicate entity rows; existing data was not changed.
- Test: XAMPP PHP syntax checks passed for the changed model and create/edit pages; `git diff --check` passed. A live Supabase behavior test remains unavailable under the project database-access instruction.
- Files changed: `models/Farmer.php`, `farmers/create.php`, `farmers/edit.php`, `database/17_audit_possible_duplicates.sql`, `PROJECT_DOCUMENTATION.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Run the audit SQL in Supabase and confirm the candidate records and linked farms before cleanup.

### 2026-11-11 20:53
- Created a visual four-stage normalization diagram and embedded it in the documentation, showing the UNF/1NF repeating and atomic structures, the 2NF entities, and the implemented 3NF main and lookup tables.
- Files changed: `normalization_unf_to_3nf.svg`, `PROJECT_DOCUMENTATION.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Continue final review of duplicate farmer records; the diagram is ready to view in a browser or the project documentation.

### 2026-11-11 21:01
- Clarified the UNF diagram and documentation to explicitly show that the wide starting record combines all five core modules named by the DOCX. The DOCX does not require exactly 15 tables; the current 3NF business schema has 5 core and 7 lookup tables, plus the separate users table.
- Files changed: `normalization_unf_to_3nf.svg`, `PROJECT_DOCUMENTATION.md`, `PROJECT_TRACKER.md`
- Next: Continue duplicate-record review and final submission checks.

### 2026-11-11 20:34
- Audited exact duplicate business records in Supabase: duplicate farmer pairs are IDs 23/24 and 25/26; no exact duplicates were found in Farms, Crops, Plantings, or Harvests. Added duplicate checks to all five modules' create/edit paths and verified PHP syntax plus model checks against Supabase. Existing farmer duplicates were not deleted.
- Files changed: five models, Farmers/Farms/Crops/Plantings/Harvests create and edit pages, `PROJECT_DOCUMENTATION.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Decide whether to merge or retain the existing duplicate farmer pairs; do not delete or reassign related records without the user's choice.

### 2026-11-11 20:22
- Read the normalization requirement in `ap3 project.docx`: the database must be normalized through 3NF and the UNF, 1NF, 2NF, and 3NF structures must be documented. Expanded `PROJECT_DOCUMENTATION.md` with explicit relation structures and the lookup-label transitive dependency removed in 3NF.
- Files changed: `PROJECT_DOCUMENTATION.md`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Continue final data review; distinguish the normalized schema from synthetic data that still needs verification or replacement.

### 2026-11-11 20:16
- Removed the remaining numbered prefix from farm_id 7 using the distinct owner-based name `Ana Alonzo's Farm`; verified it is unique beside farm_id 22. All 16 generated numbered prefixes are now removed. The DOCX requires a farm name but specifies no naming format or actual name.
- Files changed: `database/16_name_second_ana_farm.sql`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Refresh the Farms page to confirm the prefix-free names; keep migration 11 on hold.

### 2026-11-11 20:10
- Removed the generated `Farm <number> -` prefix from 15 farm names. Kept farm_id 7 unchanged because removing its prefix would duplicate farm_id 22's `Ana Alonzo` name for the same farmer; corrected farm_id 27's possessive to `Santiago's Farm` and fixed the input helper to preserve apostrophes.
- Files changed: `database/14_remove_numbered_farm_name_prefixes.sql`, `database/15_fix_possessive_farm_name.sql`, `includes/text_helpers.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Get a distinct name for farm_id 7, then refresh the Farms page; keep migration 11 on hold.

### 2026-11-11 19:57
- Added title-case handling to Farmer, Farm, and Crop create/edit text inputs. Inputs show capitalization while typing and are normalized on submit; uppercase acronyms remain intact. Planting and Harvest use dates, numbers, and controlled lookup dropdowns rather than free-text fields.
- Files changed: `includes/text_helpers.php`, Farmer/Farm/Crop create and edit pages, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Check the create/edit forms and confirm typed lowercase words display and save with capitalized initials.

### 2026-11-11 19:51
- Capitalized the first character of text values in farmer, farm, and crop records and linked lookup labels used by the five modules. Preserved the remaining characters; verification found no lowercase initials. The DOCX does not set casing rules, and this follows the user's display-format request.
- Files changed: `database/13_capitalize_main_table_text.sql`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Refresh the Farmers page and other module lists to confirm capitalization; keep migration 11 on hold.

### 2026-11-11 19:40
- Replaced all 15 remaining `AIS Address` placeholders with the user's three supplied addresses repeated in descending farmer ID order. Verified all 19 farmer rows in Supabase; the four existing Stephen/Cansaga rows were retained.
- Files changed: `database/12_standardize_cogon_address.sql`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Refresh the Farmers page to confirm the updated addresses display; migration 11 remains on hold.

### 2026-11-11 19:35
- Connected with the XAMPP PHP runtime and updated the remaining exact-match row after finding its address had an extra comma. Verified both Stephen Coronel rows now show `Cogon, Poblacion, Liloan, Cebu City`.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Refresh the Farmers page to confirm the displayed values; keep migration 11 on hold.

### 2026-11-11 19:25
- Attempted the requested Supabase update through the local PHP configuration, but the workspace could not establish/confirm the database connection. No successful update was reported.
- Files changed: `PROJECT_TRACKER.md`
- Next: Run migration 12 in Supabase SQL Editor and confirm the returned farmer rows; keep migration 11 on hold.

### 2026-11-11 19:00
- User provided three proposed address formats (Cogon/Poblacion/Liloan, Cansaga/Consolacion, and Maguikay/Mandaue). These appear to replace, rather than delete, the placeholder records; migration 11 is on hold and no database change was made.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Confirm the address-to-record mapping and whether the placeholder farmer rows should be kept or deleted before preparing SQL.

### 2026-11-11 18:53
- User authorized deleting the placeholder farmer records. Prepared migration 11 to remove matching farmer rows and dependent farms, plantings, and harvests in foreign-key-safe order; crop and lookup records are preserved.
- Files changed: `database/11_delete_placeholder_farmer_records.sql`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Run migration 11 in Supabase SQL Editor and confirm success before marking deletion complete.

### 2026-11-11 18:24
- User identified three non-human farmer first-name values (`farm -14`, `AIS`, `FARM-LILOAN`). They do not appear in the local seed SQL, so no replacement names or database update can be verified yet.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Get the actual first and last names for those records; do not substitute invented human-sounding names.

### 2026-11-11 18:04
- The user clarified that all stored data must be real. Cancelled the unrun proposed fictional farm rename and restored seed script 09's previous generated name; no Supabase change was made.
- Files changed: `database/09_seed_demo_data.sql`, removed unrun `database/11_rename_sample_farm.sql`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Obtain verified real records or the user's authorization to remove synthetic sample rows before preparing a data migration.

### 2026-11-11 06:12
- Pushed the source, database scripts, project guidance, documentation, and source DOCX to `origin/main` in commit `a77763f`; private credentials and screenshots remained excluded.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Continue final responsive and populated-database review.

### 2026-11-11 06:10
- Prepared the requested GitHub publish: project source, SQL scripts, guidance, documentation, and source DOCX will be included; private credentials, screenshots, and the temporary Word lock file stay excluded.
- Files changed: `.gitignore`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Commit the reviewed project files and push them to `origin/main`.

### 2026-11-11 03:35
- Fixed Night-mode hover backgrounds for Clear and similar links so the background stays dark and text remains visible.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Refresh the Crops page in Night mode and hover over Clear to confirm the label stays readable; continue final checks.

### 2026-11-11 00:02
- User ran migration 10 in Supabase; SQL Editor reported success and both verification queries returned 0 rows with old Demo labels.
- Files changed: `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Refresh the Farms and Harvests pages to confirm the updated labels in the application.

### 2026-11-10 23:54
- Prepared migration 10 to remove the Demo prefix from generated sample farmer addresses, farm names, and farm locations; updated seed script 09 so reruns use the new labels and avoid duplicates.
- Files changed: `database/09_seed_demo_data.sql`, `database/10_remove_demo_labels_from_sample_records.sql`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Run migration 10 in Supabase SQL Editor and confirm success before marking the database update complete.

### 2026-11-10 23:48
- Added a three-line Settings dropdown to the shared navigation, moved the appearance switch and Log out into it, and made its surfaces readable in Night mode.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Open the Settings menu on desktop and mobile, confirm theme switching and Log out are easy to find, then continue responsive review and final checks.

### 2026-11-10 23:40
- Expanded the shared header and page content width from 1280px to 1680px on large screens, keeping the existing responsive padding. The DOCX requires readable tables and desktop/tablet/mobile support but specifies no fixed width.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Review the Harvests page against the marked edges at desktop size and confirm tablet/mobile spacing.

### 2026-11-10 23:36
- Changed the shared Night-mode Apply buttons to emerald with a darker hover shade so they stand out against the filter panels.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Refresh the Harvests page in Night mode to confirm the Apply button contrast, then continue responsive review and final checks.

### 2026-11-10 23:35
- Kept the Agriculture logo badge white with its original green and lime strokes in Night mode, matching the Light-mode logo.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Refresh the shared header in Night mode to confirm the logo matches Light mode, then continue responsive review and final checks.

### 2026-11-10 23:26
- Fixed the night-mode table hover style so hovered data rows keep a dark slate background and readable light text.
- Files changed: `includes/header.php`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Refresh the Farmers page in Night mode and confirm the hovered row is readable; continue final responsive and populated-database checks.

### 2026-11-10 23:22
- Removed the local project guidance/documentation files and source DOCX from `.gitignore` so workspace file mentions can find them; they remain untracked and local unless explicitly added.
- Files changed: `.gitignore`, `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Continue final responsive review, screenshots, and populated-database checks.

## Decisions and Open Questions

Decided:
- Database credentials go in a private file (config/db_credentials.php) that is kept out of shared or public code. config/Database.php reads from it. Reason: the docx says credentials must not be exposed in public source code, while the teacher's Database.php example hard-codes them.
- Tailwind CSS via CDN (from CODING_STANDARDS.md; the docx allows any CSS framework).
- Shared page messages use one-time `$_SESSION['success']` and `$_SESSION['error']` values, escaped and cleared by `includes/header.php`.
- Role permissions follow the user's clarification: Admin can create, edit, and delete; Staff is view-only.
- The shared Settings menu contains the appearance preference and Log out; account editing, user management, and database controls are outside the DOCX requirements and are not presented as settings.
- The project is connected to `https://github.com/coronelstephen4/FMidterm_Project.git`; the initial commit has been pushed to `main`. `.gitignore` excludes `config/db_credentials.php`.
- Project guidance and documentation remain unignored so the workspace mention picker can find them; they are included in the user's requested GitHub update.

Open (ask the student or teacher, then record the answer here):
- All project data must be real. The currently seeded farmer/farm/crop/planting/harvest rows are synthetic; obtain verified replacement records or ask whether to remove them and leave the tables ready for real entries. Do not invent replacement records or names.
- Confirm whether these proposed addresses map in order to the `farm-14`, `AIS`, and `FARM-LILOAN` entries, and whether those records should be updated rather than deleted.
- Sample data has harvest dates after today (harvest_id 5 on 2026-11-20, harvest_id 15 on 2026-12-20). Decide whether future harvest dates are allowed.

Known risks:
- XAMPP's PHP needs the `pdo_pgsql` and `pgsql` extensions enabled in php.ini (remove the `;` in front of them) and Apache restarted.
- The database password was visible in a screenshot; rotate it before deployment and update the ignored local credentials file.
- Migration 08 is now confirmed successful in Supabase. Its first attempt failed because `farm.soil_type` was already absent; the corrected script skipped missing legacy columns, checked the remaining mappings, and completed successfully.

Waiting for:
- Refresh the Farmers page and visually confirm the standardized addresses.
- Keep migration 11 on hold until the intended action for the placeholder rows is clear.
- Obtain verified real records or user authorization to remove the remaining synthetic sample business records; no real replacement data was supplied yet.
- Run final CRUD/report checks against the now-normalized Supabase schema and seeded demo data.
- Capture application screenshots and visually confirm 390px, 768px, and 1440px layouts; headless Chrome/Edge could not launch due GPU-process failures.

Decision:
- Add lookup tables for all other fixed-value fields whose options are listed in PROJECT_REQUIREMENTS.md: planting status, harvest quality grade, farm irrigation type and soil type, crop category, and recommended season.
- Migration 08 checks each remaining lookup ID/label mapping before dropping duplicate legacy text labels; it skips columns already removed and does not delete business rows.
- Updated farm, crop, planting, and harvest write models store lookup IDs only. Run migration 08 before using create/edit forms for those entities.
- The user has clarified that all stored business records must be real. The old seed script contains synthetic records and must not be rerun; no fictional replacement farm names should be created.

### 2026-11-10
- Removed project instructions, requirements, documentation, tracker/checklist, DOCX, and submission README files from GitHub in commit `1230b04`. Their local copies remain and `.gitignore` now keeps them local-only.
- Files changed: `.gitignore` (pushed); local-only `PROJECT_TASKS.md`, `PROJECT_TRACKER.md`
- Next: Continue final Supabase verification and capture final screenshots locally after responsive checks.

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
- Done: Read `ap3 project.docx` (requirements and the five table designs) and `CODING_STANDARDS.md`.
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

