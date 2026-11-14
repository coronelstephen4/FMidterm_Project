-- Remove the optional creator/editor fields from the five agriculture tables.
-- This permanently deletes existing created_by and updated_by values.
-- Keep this change separate from the already-applied migration 24 for history.

BEGIN;

ALTER TABLE public.farmer
    DROP CONSTRAINT IF EXISTS farmer_created_by_fkey,
    DROP CONSTRAINT IF EXISTS farmer_updated_by_fkey,
    DROP COLUMN IF EXISTS created_by,
    DROP COLUMN IF EXISTS updated_by;

ALTER TABLE public.farm
    DROP CONSTRAINT IF EXISTS farm_created_by_fkey,
    DROP CONSTRAINT IF EXISTS farm_updated_by_fkey,
    DROP COLUMN IF EXISTS created_by,
    DROP COLUMN IF EXISTS updated_by;

ALTER TABLE public.crop
    DROP CONSTRAINT IF EXISTS crop_created_by_fkey,
    DROP CONSTRAINT IF EXISTS crop_updated_by_fkey,
    DROP COLUMN IF EXISTS created_by,
    DROP COLUMN IF EXISTS updated_by;

ALTER TABLE public.planting
    DROP CONSTRAINT IF EXISTS planting_created_by_fkey,
    DROP CONSTRAINT IF EXISTS planting_updated_by_fkey,
    DROP COLUMN IF EXISTS created_by,
    DROP COLUMN IF EXISTS updated_by;

ALTER TABLE public.harvest
    DROP CONSTRAINT IF EXISTS harvest_created_by_fkey,
    DROP CONSTRAINT IF EXISTS harvest_updated_by_fkey,
    DROP COLUMN IF EXISTS created_by,
    DROP COLUMN IF EXISTS updated_by;

COMMIT;

-- Confirm the optional fields are gone and required timestamps/role link remain.
SELECT
    NOT EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = 'public'
          AND table_name IN ('farmer', 'farm', 'crop', 'planting', 'harvest')
          AND column_name IN ('created_by', 'updated_by')
    ) AS user_audit_columns_removed,
    (
        SELECT COUNT(*) = 10
        FROM information_schema.columns
        WHERE table_schema = 'public'
          AND table_name IN ('farmer', 'farm', 'crop', 'planting', 'harvest')
          AND column_name IN ('created_at', 'updated_at')
    ) AS timestamps_retained,
    EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conrelid = 'public.users'::regclass
          AND conname = 'users_role_id_fkey'
          AND contype = 'f'
    ) AS users_role_relationship_retained;
