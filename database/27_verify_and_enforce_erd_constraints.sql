-- Verify and enforce the constraints identified while reviewing
-- Final ERD_constraints.png. This script does not delete or update rows.
-- It stops before DDL if the current data conflicts with the rules.

BEGIN;

DO $preflight$
BEGIN
    IF EXISTS (
        SELECT 1 FROM public.farm
        GROUP BY farmer_id, farm_name
        HAVING count(*) > 1
    ) THEN
        RAISE EXCEPTION 'Duplicate farm names for the same farmer exist. Review data before adding the farm unique constraint.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM public.roles
        WHERE role_name NOT IN ('admin', 'staff')
           OR btrim(role_name) = ''
    ) THEN
        RAISE EXCEPTION 'Unexpected role names exist. Review roles before adding the allowed-role check.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM public.crop
        GROUP BY lower(btrim(crop_name))
        HAVING count(*) > 1
    ) THEN
        RAISE EXCEPTION 'Duplicate normalized crop names exist. Review crop rows before enforcing uniqueness.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM public.crop_variety
        GROUP BY crop_id, lower(btrim(variety_name))
        HAVING count(*) > 1
    ) THEN
        RAISE EXCEPTION 'Duplicate normalized variety names within a crop exist. Review crop_variety rows before enforcing uniqueness.';
    END IF;
END;
$preflight$;

DO $add_farm_unique$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conrelid = 'public.farm'::regclass
          AND conname = 'farm_farmer_name_unique'
          AND contype = 'u'
    ) THEN
        ALTER TABLE public.farm
            ADD CONSTRAINT farm_farmer_name_unique UNIQUE (farmer_id, farm_name);
    END IF;
END;
$add_farm_unique$;

DO $add_role_check$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conrelid = 'public.roles'::regclass
          AND conname = 'roles_allowed_names'
          AND contype = 'c'
    ) THEN
        ALTER TABLE public.roles
            ADD CONSTRAINT roles_allowed_names
            CHECK (role_name IN ('admin', 'staff'));
    END IF;
END;
$add_role_check$;

-- These are unique expression indexes so case and surrounding spaces cannot
-- bypass the crop and per-crop variety duplicate rules.
CREATE UNIQUE INDEX IF NOT EXISTS crop_name_normalized_unique
    ON public.crop (lower(btrim(crop_name)));

CREATE UNIQUE INDEX IF NOT EXISTS crop_variety_name_normalized_unique
    ON public.crop_variety (crop_id, lower(btrim(variety_name)));

COMMIT;

-- Read-only verification result. All four rows should report present = true.
SELECT 'farm_farmer_name_unique' AS expected_object,
       EXISTS (
           SELECT 1 FROM pg_constraint
           WHERE conrelid = 'public.farm'::regclass
             AND conname = 'farm_farmer_name_unique'
             AND contype = 'u'
       ) AS present
UNION ALL
SELECT 'roles_allowed_names',
       EXISTS (
           SELECT 1 FROM pg_constraint
           WHERE conrelid = 'public.roles'::regclass
             AND conname = 'roles_allowed_names'
             AND contype = 'c'
       )
UNION ALL
SELECT 'crop_name_normalized_unique',
       EXISTS (
           SELECT 1 FROM pg_index i
           JOIN pg_class idx ON idx.oid = i.indexrelid
           WHERE i.indrelid = 'public.crop'::regclass
             AND idx.relname = 'crop_name_normalized_unique'
             AND i.indisunique
       )
UNION ALL
SELECT 'crop_variety_name_normalized_unique',
       EXISTS (
           SELECT 1 FROM pg_index i
           JOIN pg_class idx ON idx.oid = i.indexrelid
           WHERE i.indrelid = 'public.crop_variety'::regclass
             AND idx.relname = 'crop_variety_name_normalized_unique'
             AND i.indisunique
       );
