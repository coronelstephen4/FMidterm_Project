# Final Test Evidence

Create one dated test report after checking the application against the final Supabase data. Record the environment, role, steps, expected result, actual result, and pass/fail for each test. Do not include database passwords, private contact details, or other secrets.

Copy this template into a dated file such as `2026-11-12-final-tests.md`:

```markdown
# AIS Final Test Run

- Date and time:
- Application URL:
- Database: Supabase PostgreSQL (do not include connection details)
- Dataset verified as approved for presentation: Yes / No
- Tester:

| Area | Role | Steps / input | Expected result | Actual result | Pass / fail | Notes |
|---|---|---|---|---|---|---|
| Login and logout | | | | | | |
| Admin permissions | | | | | | |
| Staff view-only permissions | | | | | | |
| Farmer CRUD and duplicate warning | | | | | | |
| Farm CRUD, lookup dropdowns, and farmer join | | | | | | |
| Crop CRUD and lookup dropdowns | | | | | | |
| Planting CRUD, joins, lookup dropdown, and date validation | | | | | | |
| Harvest CRUD, joins, lookup dropdowns, and date validation | | | | | | |
| Search, filters, sorting, and pagination | | | | | | |
| Dashboard totals and recent records | | | | | | |
| Reports and date filters | | | | | | |
| Delete confirmation and foreign-key protection | | | | | | |
| Invalid inputs and friendly errors | | | | | | |
| Links and buttons | | | | | | |

## Defects found and follow-up

-
```

The earlier local checks are summarized in `PROJECT_DOCUMENTATION.md`; verification against the final populated Supabase data remains outstanding.
