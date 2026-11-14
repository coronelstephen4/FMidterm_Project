-- Track the account that creates or last updates each business record.
-- Existing rows keep NULL because their original user cannot be determined.

BEGIN;

ALTER TABLE public.farmer
    ADD COLUMN IF NOT EXISTS created_by INTEGER,
    ADD COLUMN IF NOT EXISTS updated_by INTEGER;

ALTER TABLE public.farm
    ADD COLUMN IF NOT EXISTS created_by INTEGER,
    ADD COLUMN IF NOT EXISTS updated_by INTEGER;

ALTER TABLE public.crop
    ADD COLUMN IF NOT EXISTS created_by INTEGER,
    ADD COLUMN IF NOT EXISTS updated_by INTEGER;

ALTER TABLE public.planting
    ADD COLUMN IF NOT EXISTS created_by INTEGER,
    ADD COLUMN IF NOT EXISTS updated_by INTEGER;

ALTER TABLE public.harvest
    ADD COLUMN IF NOT EXISTS created_by INTEGER,
    ADD COLUMN IF NOT EXISTS updated_by INTEGER;

DO $$
DECLARE
    table_name TEXT;
    column_name TEXT;
    constraint_name TEXT;
BEGIN
    FOREACH table_name IN ARRAY ARRAY['farmer', 'farm', 'crop', 'planting', 'harvest'] LOOP
        FOREACH column_name IN ARRAY ARRAY['created_by', 'updated_by'] LOOP
            constraint_name := table_name || '_' || column_name || '_fkey';

            IF NOT EXISTS (
                SELECT 1
                FROM pg_constraint
                WHERE conname = constraint_name
                  AND conrelid = ('public.' || table_name)::regclass
            ) THEN
                EXECUTE format(
                    'ALTER TABLE public.%I ADD CONSTRAINT %I FOREIGN KEY (%I) REFERENCES public.users (user_id) ON DELETE RESTRICT',
                    table_name,
                    constraint_name,
                    column_name
                );
            END IF;
        END LOOP;
    END LOOP;
END $$;

CREATE INDEX IF NOT EXISTS farmer_created_by_idx ON public.farmer (created_by);
CREATE INDEX IF NOT EXISTS farmer_updated_by_idx ON public.farmer (updated_by);
CREATE INDEX IF NOT EXISTS farm_created_by_idx ON public.farm (created_by);
CREATE INDEX IF NOT EXISTS farm_updated_by_idx ON public.farm (updated_by);
CREATE INDEX IF NOT EXISTS crop_created_by_idx ON public.crop (created_by);
CREATE INDEX IF NOT EXISTS crop_updated_by_idx ON public.crop (updated_by);
CREATE INDEX IF NOT EXISTS planting_created_by_idx ON public.planting (created_by);
CREATE INDEX IF NOT EXISTS planting_updated_by_idx ON public.planting (updated_by);
CREATE INDEX IF NOT EXISTS harvest_created_by_idx ON public.harvest (created_by);
CREATE INDEX IF NOT EXISTS harvest_updated_by_idx ON public.harvest (updated_by);

COMMIT;
