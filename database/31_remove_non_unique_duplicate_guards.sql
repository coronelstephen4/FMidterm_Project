-- Review before running. This script is not applied automatically.
-- It removes only guards/constraints that reject repeated non-unique business data.
-- It does not change or delete table rows. Keep the crop-name guard and the
-- case-insensitive crop/variety unique indexes that are part of the ERD.
-- After applying, do not rerun historical database/18, database/27, or
-- database/30 scripts; those recreate guards or constraints removed here.

BEGIN;

DROP TRIGGER IF EXISTS farmer_duplicate_guard ON public.farmer;
DROP TRIGGER IF EXISTS farm_duplicate_guard ON public.farm;
DROP TRIGGER IF EXISTS planting_duplicate_guard ON public.planting;
DROP TRIGGER IF EXISTS harvest_duplicate_guard ON public.harvest;

DROP FUNCTION IF EXISTS public.reject_duplicate_farmer();
DROP FUNCTION IF EXISTS public.reject_duplicate_farm();
DROP FUNCTION IF EXISTS public.reject_duplicate_planting();
DROP FUNCTION IF EXISTS public.reject_duplicate_harvest();

-- These historical UNIQUE constraints are not in the current ERD.
ALTER TABLE public.farm
    DROP CONSTRAINT IF EXISTS farm_farmer_name_unique;
ALTER TABLE public.crop
    DROP CONSTRAINT IF EXISTS crop_name_variety_unique;

COMMIT;

-- Read-only verification: the four non-unique guards and two old constraints
-- should be absent; crop_duplicate_guard and the declared unique indexes remain.
SELECT 'farmer_duplicate_guard' AS object_name,
       EXISTS (SELECT 1 FROM information_schema.triggers
               WHERE trigger_schema = 'public' AND trigger_name = 'farmer_duplicate_guard') AS present
UNION ALL
SELECT 'farm_duplicate_guard',
       EXISTS (SELECT 1 FROM information_schema.triggers
               WHERE trigger_schema = 'public' AND trigger_name = 'farm_duplicate_guard')
UNION ALL
SELECT 'planting_duplicate_guard',
       EXISTS (SELECT 1 FROM information_schema.triggers
               WHERE trigger_schema = 'public' AND trigger_name = 'planting_duplicate_guard')
UNION ALL
SELECT 'harvest_duplicate_guard',
       EXISTS (SELECT 1 FROM information_schema.triggers
               WHERE trigger_schema = 'public' AND trigger_name = 'harvest_duplicate_guard')
UNION ALL
SELECT 'crop_duplicate_guard',
       EXISTS (SELECT 1 FROM information_schema.triggers
               WHERE trigger_schema = 'public' AND trigger_name = 'crop_duplicate_guard')
UNION ALL
SELECT 'farm_farmer_name_unique',
       EXISTS (SELECT 1 FROM pg_constraint
               WHERE conrelid = 'public.farm'::regclass AND conname = 'farm_farmer_name_unique')
UNION ALL
SELECT 'crop_name_variety_unique',
       EXISTS (SELECT 1 FROM pg_constraint
               WHERE conrelid = 'public.crop'::regclass AND conname = 'crop_name_variety_unique')
UNION ALL
SELECT 'crop_name_normalized_unique',
       EXISTS (SELECT 1 FROM pg_class idx
               JOIN pg_index i ON i.indexrelid = idx.oid
               WHERE i.indrelid = 'public.crop'::regclass
                 AND idx.relname = 'crop_name_normalized_unique' AND i.indisunique)
UNION ALL
SELECT 'crop_variety_name_normalized_unique',
       EXISTS (SELECT 1 FROM pg_class idx
               JOIN pg_index i ON i.indexrelid = idx.oid
               WHERE i.indrelid = 'public.crop_variety'::regclass
                 AND idx.relname = 'crop_variety_name_normalized_unique' AND i.indisunique);
