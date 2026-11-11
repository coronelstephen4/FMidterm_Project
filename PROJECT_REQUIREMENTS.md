# Project Requirements: Agriculture Information System (AIS)

Source: [`ap3 project.docx`](ap3%20project.docx) (AP3 Midterm Project, Cebu Technological University). This is a plain-text copy so AI tools can read it. If anything here conflicts with the DOCX, the DOCX wins.

**Goal:** Design and develop a working Agriculture Information System that demonstrates database normalization, relational design, secure data management, CRUD, reporting and dashboard visualization.
**Database:** Supabase PostgreSQL. **Application:** web-based information system.

## Tables (current design from the docx)

All primary keys are `integer` IDENTITY.

### farmer
| Column | Type |
|---|---|
| farmer_id (PK) | integer |
| first_name | varchar(50) |
| last_name | varchar(50) |
| contact_number | varchar(20) |
| address | varchar(150) |
| date_registered | date |

### farm
| Column | Type |
|---|---|
| farm_id (PK) | integer |
| farmer_id (FK -> farmer) | integer |
| farm_name | varchar(100) |
| location | varchar(150) |
| area_hectares | numeric(8,2) |
| soil_type | varchar(50) |
| irrigation_type | varchar(50) |

### crop
| Column | Type |
|---|---|
| crop_id (PK) | integer |
| crop_name | varchar(80) |
| variety | varchar(80) |
| crop_category | varchar(50) |
| growing_period_days | integer |
| recommended_season | varchar(30) |

### planting
| Column | Type |
|---|---|
| planting_id (PK) | integer |
| farm_id (FK -> farm) | integer |
| crop_id (FK -> crop) | integer |
| planting_date | date |
| area_planted | numeric(8,2) |
| seed_quantity | numeric(8,2) |
| expected_harvest_date | date |
| status | varchar(30) |

### harvest
| Column | Type |
|---|---|
| harvest_id (PK) | integer |
| planting_id (FK -> planting) | integer |
| harvest_date | date |
| quantity_kg | numeric(10,2) |
| selling_price_per_kg | numeric(10,2) |
| quality_grade | varchar(30) |
| remarks | varchar(200) |

### Relationships
- FARMER 1:M FARM
- FARM 1:M PLANTING
- CROP 1:M PLANTING
- PLANTING 1:M HARVEST

### Lookup table required by the docx: harvest_remarks
| remark_id | remark_description |
|---|---|
| 1 | Good Harvest |
| 2 | Acceptable Quality |
| 3 | Low Yield |
| 4 | Poor Quality |
| 5 | Damaged Harvest |

The dropdown shows `remark_description`; the harvest record stores the numeric `remark_id`.

### Sample data seen in the docx screenshots
- crop: 10 rows (Rice, Corn, Tomato, Eggplant, Banana, Mango, Peanut, Cassava, Sweet Potato, Watermelon)
- farmer: 15 rows, farm: 15 rows (one farm per farmer), planting: 28 or more rows, harvest: 20 or more rows
- Values in use: planting.status = Harvested / Growing; harvest.quality_grade = Grade A / B / C; farm.irrigation_type = Rainfed / Irrigated / Drip Irrigation; farm.soil_type = Loam / Clay Loam / Sandy Loam / Clay; crop_category = Cereal / Vegetable / Fruit / Legume / Root Crop; recommended_season = Wet Season / Dry Season / Year Round
- harvest.remarks currently holds free text: High quality harvest / Good harvest / Acceptable quality

## Requirements

1. **Normalization.** Normalize to 3NF. Atomic values, no repeating groups, no partial or transitive dependencies. Identify PKs, FKs and relationships. Submit UNF, 1NF, 2NF and 3NF structures in the documentation.
2. **Supabase.** All tables, keys, constraints and relationships live in Supabase PostgreSQL. The app reads from and writes to it. Credentials must be stored securely and not exposed in public source code.
3. **Controlled dropdowns.** Fields with predefined values use `<select>`, not free text. The user sees a label; the database stores the numeric ID/code. Labels live in a separate lookup table linked by a foreign key (example: harvest_remarks).
4. **Working system with CRUD** for Farmers, Farms, Crops, Planting Records and Harvest Records. Relationships implemented with PK/FK. Related data shown with JOINs (e.g. planting shows its farm and crop). Everything connects to the real Supabase database.
5. **Dashboard** as the main page after login.
   - Summary cards: total farmers, farms, crops, planting records, harvest records.
   - Also show: recent plantings, recent harvests, plantings by status, total harvested quantity.
   - Charts, cards or tables as useful. All values come from the database (no hard-coding).
   - Navigation from the dashboard to every module.
6. **Authentication and access control.** Login page. Only authorized users reach protected pages. At least two roles (Administrator and Staff) with some functions restricted by role. Passwords never stored as plain text.
7. **Input validation** before saving. Required fields not empty. Numeric fields valid with min/max. Valid dates and logical date relationships. Foreign keys must point to existing records. Validate in the app and, where appropriate, with database constraints.
8. **Search, filter, sort.** Search in each major module. Filters such as farmers by address, farms by irrigation type, crops by category, plantings by status, harvests by quality grade. Sorting on useful columns.
9. **Reports** (from database queries, filterable by date range or other criteria):
   - Farmers and their farms
   - Crops planted per farm
   - Planting records by status
   - Harvest records
   - Total harvested quantity
   - Estimated harvest value (quantity x selling price)
10. **Data tables and pagination.** Organized tables with clear headings, pagination for large lists, View/Edit/Delete actions, readable on common screen sizes.
11. **Confirmation before deletion.** Confirm before permanent delete. Handle records referenced by other tables (prevent or manage FK violations).
12. **Responsive, organized UI** for desktop, tablet and mobile. Consistent navigation, forms, buttons, labels, spacing and tables. A CSS framework is allowed.
13. **Messages.** Clear success messages after create, update and delete. Understandable validation/error messages. Never show credentials, connection strings or unnecessary technical errors to users.
14. **Audit information.** `created_at` and `updated_at` on important tables. Recording the responsible user for changes is encouraged.
15. **Constraints and integrity.** PRIMARY KEY, FOREIGN KEY, NOT NULL, UNIQUE and CHECK constraints where appropriate. Referential integrity across all tables including lookup tables.
16. **Documentation** must include: title and description, objectives, functional requirements, ERD, normalization process (UNF to 3NF), data dictionary, table relationships and constraints, screenshots, testing results, user guide.
17. **Testing.** Test all modules: CRUD, searches, filters, dropdowns, joins, authentication, dashboard, reports. Test invalid input and error cases. No broken links, missing pages or dead buttons.
18. **Deployment and readiness.** Database hosted in Supabase. App connects successfully. Use environment variables or another secure configuration for credentials. Demonstrable with actual records. Ready for demo and evaluation.

## Required deliverables
- Working AIS source code
- Supabase database with tables, constraints, relationships and lookup data
- ERD and normalized design (UNF/1NF/2NF/3NF)
- Project documentation
- Screenshots of all major working modules
- Test results
- Final deployed or demonstrable system

## Initial gaps and current status
- `harvest.remarks` was migrated to `harvest_remarks` and `harvest.remark_id`.
- Lookup tables and foreign-key IDs were added for the other controlled values.
- `public.users`, audit timestamps, and integrity constraints were added.
- The guarded `database/08_normalize_lookup_columns.sql` migration was confirmed successful in Supabase after it was updated to handle legacy columns already removed.
- The user's Supabase screenshots confirm demo rows are present: 15 farmers, 15 farms, 10 crops, 30 plantings, and 20 harvests.
