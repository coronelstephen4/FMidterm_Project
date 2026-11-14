-- Read-only audit of primary-key type and generation for all 15 AIS tables.
-- Run in Supabase SQL Editor to inspect the live schema. This file is SELECT-only.

WITH expected_tables(table_name) AS (
    VALUES
        ('farmer'), ('farm'), ('crop'), ('crop_variety'), ('planting'),
        ('harvest'), ('users'), ('roles'), ('irrigation_types'), ('soil_types'),
        ('crop_categories'), ('recommended_seasons'), ('planting_statuses'),
        ('harvest_quality_grades'), ('harvest_remarks')
)
SELECT
    e.table_name,
    COALESCE(a.attname, '(primary key missing)') AS primary_key_column,
    CASE WHEN a.attname IS NULL THEN '(unknown)'
         ELSE format_type(a.atttypid, a.atttypmod)
    END AS data_type,
    COALESCE(col.is_identity, 'NO') AS is_identity,
    col.identity_generation,
    col.column_default
FROM expected_tables AS e
LEFT JOIN pg_namespace AS ns
    ON ns.nspname = 'public'
LEFT JOIN pg_class AS tbl
    ON tbl.relnamespace = ns.oid
   AND tbl.relname = e.table_name
LEFT JOIN pg_constraint AS pk
    ON pk.conrelid = tbl.oid
   AND pk.contype = 'p'
LEFT JOIN LATERAL unnest(pk.conkey) WITH ORDINALITY AS key_column(attnum, key_order)
    ON TRUE
LEFT JOIN pg_attribute AS a
    ON a.attrelid = tbl.oid
   AND a.attnum = key_column.attnum
LEFT JOIN information_schema.columns AS col
    ON col.table_schema = 'public'
   AND col.table_name = e.table_name
   AND col.column_name = a.attname
ORDER BY e.table_name, key_column.key_order;
