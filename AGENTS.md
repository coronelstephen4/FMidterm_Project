# Instructions for the AI assistant

You are pair-programming with a university student on the AP3 Midterm Project: an Agriculture Information System (AIS) at Cebu Technological University. Write code the way a strong student who learned PHP PDO and Tailwind in class would: short, clean, readable. Do not over-engineer.

## Read first, every session
1. PROJECT_TRACKER.md: current status, decisions, open questions, progress log
2. PROJECT_TASKS.md: the checklist
3. PROJECT_REQUIREMENTS.md: what must be built (the original is ap3_project.docx; the docx wins on conflict)
4. CODING_STANDARDS.md: mandatory coding style. Read it before writing any code.

Continue from "Next step" in the tracker.

## Tracking rules (always)
- Work on one task or one module at a time, then stop and report what was done.
- After each finished task:
  - Tick the item in PROJECT_TASKS.md (`[x]`, or `[~]` if partly done). Add new tasks if you discover them.
  - Add a new entry at the TOP of the Progress Log in PROJECT_TRACKER.md: date and time, what was done, files changed, what is next.
  - Update "Currently working on" and "Next step" in PROJECT_TRACKER.md.
- Record every decision or open question in the tracker.
- Before the session ends or context runs low, make sure the log is up to date.
- Keep log entries short and specific.

## Stack and hard limits
- Native PHP with OOP model classes (constructor receives PDO), prepared statements only.
- Database: Supabase PostgreSQL through PDO `pgsql`. Local server: XAMPP Apache at http://localhost/FMidterm_Project/.
- Tailwind CSS via CDN, inline Heroicons SVG.
- NO Composer or packages, NO frameworks (Laravel, Symfony, Vue, React), NO ORM, NO design-pattern layers, NO CLI scripts inside the web app, NO plain-text passwords.
- Escape all output with `htmlspecialchars()`. Cast inputs as in CODING_STANDARDS.md section 5.
- Hash passwords with `password_hash()`, check with `password_verify()`.

## Folder layout
Follow CODING_STANDARDS.md section 1 inside this folder: config/, models/, includes/, farmers/, farms/, crops/, plantings/, harvests/, reports/, auth/, index.php. Also use a `database/` folder for SQL scripts.

## Credentials
- Never write real database credentials into code that gets shared or committed.
- config/Database.php reads them from config/db_credentials.php (kept private). Provide config/db_credentials.example.php with placeholders.
- Never show credentials, connection strings or raw database errors to the user in the browser. Show a friendly message instead.

## Database changes
- You cannot reach Supabase directly. Write SQL to `database/NN_short_description.sql` (e.g. `database/01_harvest_remarks.sql`) and tell the student to run it in the Supabase SQL Editor.
- Mark a database task done only after the student confirms it ran.
- Say clearly before any script that drops columns or deletes data, and keep a safe order (add new column, copy data, then drop the old one).

## Every module must have
Model class, list page (search, filter, sortable columns, pagination, clear headings), create, edit, view, delete with confirmation (Admin only), server-side validation, success and error messages, JOINs where related data is shown, and dropdowns fed from lookup tables where the requirements say so.

## Roles
- Admin: everything, including delete.
- Staff: view only; only Admin can create, edit, or delete records.
