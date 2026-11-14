-- Read-only audit of ERD primary keys, unique rules, indexes, and triggers.
-- Run in Supabase SQL Editor to inspect the live public schema.
-- This file contains SELECT statements only and makes no database changes.

WITH expected_tables(table_name) AS (
    VALUES
        ('farmer'),
        ('farm'),
        ('crop'),
        ('crop_variety'),
        ('planting'),
        ('harvest'),
        ('users'),
        ('roles'),
        ('irrigation_types'),
        ('soil_types'),
        ('crop_categories'),
        ('recommended_seasons'),
        ('planting_statuses'),
        ('harvest_quality_grades'),
        ('harvest_remarks')
)
SELECT
    e.table_name,
    COALESCE(pk.primary_key, '(missing)') AS primary_key,
    COALESCE(uniques.unique_constraints, '(none)') AS unique_constraints,
    COALESCE(indexes.unique_indexes, '(none)') AS unique_indexes
FROM expected_tables AS e
LEFT JOIN pg_class AS tbl
    ON tbl.relname = e.table_name
LEFT JOIN pg_namespace AS ns
    ON ns.oid = tbl.relnamespace AND ns.nspname = 'public'
LEFT JOIN LATERAL (
    SELECT string_agg(pg_get_constraintdef(c.oid), '; ' ORDER BY c.conname) AS primary_key
    FROM pg_constraint AS c
    WHERE c.conrelid = tbl.oid AND c.contype = 'p'
) AS pk ON TRUE
LEFT JOIN LATERAL (
    SELECT string_agg(c.conname || ': ' || pg_get_constraintdef(c.oid), '; ' ORDER BY c.conname)
        AS unique_constraints
    FROM pg_constraint AS c
    WHERE c.conrelid = tbl.oid AND c.contype = 'u'
) AS uniques ON TRUE
LEFT JOIN LATERAL (
    SELECT string_agg(i.indexname || ': ' || i.indexdef, '; ' ORDER BY i.indexname) AS unique_indexes
    FROM pg_indexes AS i
    WHERE i.schemaname = 'public'
      AND i.tablename = e.table_name
      AND i.indexdef ILIKE 'CREATE UNIQUE INDEX%'
) AS indexes ON TRUE
WHERE ns.nspname = 'public' OR tbl.oid IS NULL
ORDER BY e.table_name;

-- List every user-defined trigger on the 15 audited tables, including its
-- function body, so duplicate guards can be distinguished from timestamp or
-- other maintenance triggers.
SELECT
    c.relname AS table_name,
    t.tgname AS trigger_name,
    CASE t.tgenabled
        WHEN 'O' THEN 'enabled'
        WHEN 'D' THEN 'disabled'
        WHEN 'R' THEN 'replica'
        WHEN 'A' THEN 'always'
    END AS trigger_status,
    p.proname AS function_name,
    pg_get_triggerdef(t.oid) AS trigger_definition,
    pg_get_functiondef(p.oid) AS function_definition
FROM pg_trigger AS t
JOIN pg_class AS c ON c.oid = t.tgrelid
JOIN pg_namespace AS n ON n.oid = c.relnamespace
JOIN pg_proc AS p ON p.oid = t.tgfoid
WHERE n.nspname = 'public'
  AND c.relname IN (
      'farmer', 'farm', 'crop', 'crop_variety', 'planting', 'harvest', 'users', 'roles',
      'irrigation_types', 'soil_types', 'crop_categories', 'recommended_seasons',
      'planting_statuses', 'harvest_quality_grades', 'harvest_remarks'
  )
  AND NOT t.tgisinternal
ORDER BY c.relname, t.tgname;
