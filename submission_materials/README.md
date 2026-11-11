# Submission Materials

This folder holds the evidence for the final project review without moving application source files or the main report.

## Word report

- `AIS_Final_Project_Report_Corrected.docx` is the current editable report. Use this file for review; the earlier infographic-only reports are superseded because they did not present the normalization steps and complete table inventory clearly enough.
- The report starts from the five separate core tables in `ap3 project.docx`. It does not invent a single wide UNF relation. The normalization progression and the final 13-relation inventory are editable Word tables.
- The count is 12 operational relations (five core plus seven lookup tables) and one separate `users` authentication-support relation. The report distinguishes the authentication table from the agriculture normalization sequence.
- `build_corrected_report.ps1` regenerates the corrected report locally. The report keeps actual screenshots, responsive-device evidence, and remaining live-data checks marked for the student; it does not fabricate them.
- `PROJECT_DOCUMENTATION.md` is the detailed project reference and data dictionary. The report and reference were revised together.
- `normalization_unf_to_3nf.svg` is the matching summary diagram; it also begins with the five assignment tables and no longer depicts a synthetic wide UNF record.

The report is a local Word file. No Google Docs session was used to create or edit an online document.

## Folders

- `screenshots/` contains application and responsive-layout evidence. Its README identifies the remaining capture checklist.
- `test-results/` contains the dated final test report. Its README provides a report template.

The project report, requirements, source assignment, and SQL scripts remain in their existing project locations. Never include database credentials or unverified private information in submission evidence.
