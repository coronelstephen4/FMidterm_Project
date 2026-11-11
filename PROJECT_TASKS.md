# Project Task Checklist: FMidterm_Project

Mark `[x]` when finished. Add new tasks as they come up.
Status key: `[ ]` not started, `[~]` in progress, `[x]` done
Work top to bottom. Do one task or one module at a time.

## Phase 0: Setup
- [x] Create project folder in xampp\htdocs
- [x] Install Codex and open it
- [x] Create PROJECT_TRACKER.md, PROJECT_TASKS.md and AGENTS.md
- [x] Add `ap3 project.docx` and `CODING_STANDARDS.md` to the project folder
- [x] Write PROJECT_REQUIREMENTS.md (plain-text copy of the docx)
- [x] Start Codex from the project folder (not system32) and confirm it follows AGENTS.md
- [x] Enable `pdo_pgsql` and `pgsql` in XAMPP php.ini, restart Apache, confirm with phpinfo()
- [x] Initialize a local Git repository on the `main` branch
- [x] Create a GitHub repository, connect it as `origin`, and push the initial commit (user screenshot confirms `main` uploaded successfully)

## Phase 1: Database (Supabase)
- [x] Confirm the 5 core tables exist in Supabase
- [x] Create `harvest_remarks` lookup table with the 5 values
- [x] Add `harvest.remark_id` FK, move old remarks text into it, remove old `remarks` column
- [x] Add lookup tables and FK columns for other fixed-value fields (migration ran successfully in Supabase; mapping check remains below)
- [x] Verify existing fixed-value text values copied to their lookup IDs (query returned 0 populated rows and 0 unmapped rows for all six fields)
- [x] Confirm demonstration data in the five core tables (Supabase screenshots show 15 farmers, 15 farms, 10 crops, 30 plantings, and 20 harvests)
- [x] Create `users` table and add one admin and one staff account (Supabase SQL Editor reported success)
- [x] Add `created_at` and `updated_at` to the main tables (Supabase SQL Editor reported success)
- [x] Add NOT NULL, UNIQUE and CHECK constraints (Supabase SQL Editor reported success)
- [x] Check DOCX data requirement: demonstrate with records stored in the actual Supabase database; it does not explicitly prohibit sample/demo rows, so no deletion is required solely because records are synthetic
- [x] Save every SQL script in the `database/` folder (includes archival `01_harvest_remarks_migration.sql`; do not rerun it)
- [x] Run `database/08_normalize_lookup_columns.sql` in Supabase after reviewing its label-to-ID guards (user confirmed Supabase success; script safely skipped already-removed columns)
- [x] Run `database/09_seed_demo_data.sql` in Supabase to add fictional demo farmers, farms, crops, plantings, and harvests (user screenshot shows 15 farmers, 15 farms, 10 crops, 30 plantings, and 20 harvests)
- [x] Run `database/10_remove_demo_labels_from_sample_records.sql` in Supabase to remove the Demo word from sample farmer/farm labels (user screenshot confirms success; verification queries returned 0 old labels)
- [x] Recheck the three previously reported placeholder clues in the current Supabase data; none were found, so leave migration 11 unapplied
- [x] Run a read-only inventory of all five business tables and compare records/relationships with archived demo seed clues; uncertain additional records identified and preserved
- [x] Apply the user's repeating Cogon/Cansaga/Maguikay address sequence to all 15 remaining AIS placeholder farmer addresses in Supabase and verify the results
- [x] Capitalize the first character of text values across the five main modules and linked lookup labels in Supabase; verification found no lowercase initials
- [x] Mirror the website's duplicate checks in Supabase with `database/18_prevent_duplicate_records.sql`; user screenshot confirms the five triggers were installed (information_schema displays one row per event)
- [x] Remove generated numbered farm-name prefixes from all 16 affected farms; assign farm_id 7 a distinct owner-based name to satisfy the per-farmer uniqueness constraint
- [x] Verify exact duplicate farmer cleanup in Supabase: the exact-identity audit returned no duplicate pairs, and no farms remain assigned to candidate IDs 23–26. The original script reported a temporary-table error in its post-commit output; migration 19 is corrected locally for future runs.

## Phase 2: Config and layout
- [x] config/Database.php (PDO pgsql, credentials read from a private file, not hard-coded)
- [x] config/db_credentials.example.php and .gitignore entry for the real credentials file
- [x] includes/header.php (Tailwind CDN, navbar with links to all modules, success/error message area)
- [x] includes/footer.php
- [x] includes/auth_check.php (redirect to login if no session; role helper)
- [x] Test the database connection from a simple test page, then delete the test page (connection succeeded; temporary page removed)

## Phase 3: Authentication
- [x] models/User.php
- [x] auth/login.php (Tailwind form, password_verify, session with user_id and role)
- [x] auth/logout.php
- [x] Show a logout link in shared navigation for signed-in users
- [x] Hide protected module navigation from signed-out visitors
- [x] Role rules: Admin can view, create, edit and delete; Staff can view only (write routes and controls are Admin-only)

## Phase 4: Modules (do Farmers first and use it as the template for the rest)
Each module needs: model, list page with search, filter, sorting and pagination, create, edit, view, delete with confirmation (Admin only), validation, success/error messages.
- [x] Farmers: models/Farmer.php, farmers/ pages, filter by address
- [x] Farms: models/Farm.php, farms/ pages, show farmer name via JOIN, filter by irrigation type
- [x] Crops: models/Crop.php, crops/ pages, filter by crop category
- [x] Plantings: models/Planting.php, plantings/ pages, JOIN farm + farmer + crop, filter by status, check expected_harvest_date is after planting_date
- [x] Harvests: models/Harvest.php, models/Lookup.php, harvests/ pages, remarks dropdown from lookup table, filter by quality grade, check harvest_date is not before planting_date
- [x] Block or handle deletes that would break foreign keys (e.g. farmer with farms)

## Phase 5: Dashboard
- [x] index.php summary cards: farmers, farms, crops, plantings, harvests (from the database)
- [x] Recent plantings and recent harvests tables
- [x] Plantings by status (chart or table)
- [x] Total harvested quantity
- [x] Links from the dashboard to every module

## Phase 6: Reports (all with filters, e.g. date range)
- [x] Farmers and their farms
- [x] Crops planted per farm
- [x] Plantings by status
- [x] Harvest records
- [x] Total harvested quantity
- [x] Estimated harvest value (quantity_kg x selling_price_per_kg)

## Phase 7: Quality pass
- [x] Validation on every form (required, numeric ranges, valid dates, foreign keys exist)
- [x] Friendly error messages; no credentials or technical errors shown to users
- [x] Add persistent light/night mode switch beside the role badge
- [x] Improve night-mode contrast for green dashboard links, totals, and status counts
- [x] Keep table row text readable when hovered in night mode
- [x] Keep the original white badge with green and lime logo colors in night mode
- [x] Keep Apply buttons clearly visible in night mode
- [x] Keep Clear button text visible on hover in night mode
- [x] Capitalize farmer, farm, and crop text inputs on create/edit forms at display and save time; keep numeric/date fields and controlled dropdowns unchanged
- [x] Show a duplicate-record error on create/edit for repeated farmer, farm, crop, planting, or harvest details; keep existing duplicate farmer rows for review
- [x] Flag farmer repeats by normalized contact plus address or matching full name plus address; add a read-only duplicate audit for all five core tables
- [x] Add a shared three-line Settings menu with appearance control and Log out
- [x] Expand the shared desktop layout to match the user's marked content width while retaining responsive padding
- [~] Responsive check: student to capture on physical iOS and Android phones, simulate tablet at 768 CSS px in browser emulation, and check desktop at 1440 CSS px; simulated tablet must be labeled accurately
- [x] Check there are no broken links or dead buttons (94 static local link/form targets resolve; protected route smoke checks passed)
- [x] Use crop-specific navigation icons for Crops, Plantings, and Harvests
- [x] Use a farmer-specific icon in the Farmers navigation item
- [x] Show the active Light/Night label and matching sun/moon icon in the theme control
- [x] Add an agriculture logo mark and styled system name to the shared header
- [x] Clean dashboard layout by removing shortcut buttons duplicated in the main navigation
- [x] Remove the optional Settings page because it is not required by the DOCX

## Phase 8: Documentation
- [x] Title, description, objectives, functional requirements
- [x] ERD
- [x] Document explicit UNF, 1NF, 2NF, and 3NF table structures; migration 08 was confirmed successful in Supabase
- [x] Create and embed a visual UNF-to-3NF normalization diagram in the project documentation
- [x] Data dictionary
- [x] Table relationships and constraints
- [x] Add guideline coverage, complete data dictionary, role permissions, operating workflow, and a truthful test/evidence status report
- [x] Complete requirement coverage matrix, full data dictionary, record workflow, and truthful test-status summary based on the source DOCX
- [x] Create an editable Word report aligned to `ap3 project.docx`, with a tabular normalization progression starting from the five specified core tables and a complete 13-relation inventory
- [x] Replace the superseded infographic-only report's invented wide UNF relation with an accurate explanation of the five separate source relations; clarify that `users` is separate authentication support
- [x] Keep actual screenshots, device captures, and unverified live-data outcomes marked as pending in the report
- [x] Create `submission_materials/` folders for screenshot and final test evidence
- [ ] Screenshots of all modules
- [x] Testing results
- [x] User guide

## Phase 9: Final testing and submission
- [x] Keep local project guidance, documentation, and the source DOCX discoverable to workspace file mentions
- [ ] Test CRUD, search, filter, sort, dropdowns, joins, login, dashboard and reports after migration 08
- [x] Test invalid input and error cases (module checks and integer overflow boundary check completed)
- [ ] Confirm the app runs against Supabase and passes the required workflows with the final approved dataset; seeded counts alone do not verify record authenticity
- [ ] Rehearse the demo
- [ ] Final cleanup and submission
- [x] Publish project guidance, documentation, source DOCX, and system updates to GitHub in commit `a77763f`; private credentials and screenshots remain excluded
