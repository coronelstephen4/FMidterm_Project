-- Removes duplicate text labels after checking any labels that still exist.
-- This drops six columns, not table rows. Review the mapping checks first.
-- Safe to rerun when some or all legacy label columns have already been removed.

BEGIN;

DO $$
DECLARE
    has_mismatch BOOLEAN;
BEGIN
    -- Check each legacy column independently. Dynamic SQL avoids referencing
    -- a column that was already removed by an earlier partial/manual run.
    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'farm' AND column_name = 'soil_type'
    ) THEN
        EXECUTE $sql$
            SELECT EXISTS (
                SELECT 1
                FROM public.farm f
                LEFT JOIN public.soil_types st ON st.soil_type_id = f.soil_type_id
                WHERE f.soil_type_id IS NULL
                   OR f.soil_type IS DISTINCT FROM st.soil_type_name
            )
        $sql$ INTO has_mismatch;
        IF has_mismatch THEN
            RAISE EXCEPTION 'Farm soil labels do not match their IDs. Resolve the mismatches before running this migration.';
        END IF;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'farm' AND column_name = 'irrigation_type'
    ) THEN
        EXECUTE $sql$
            SELECT EXISTS (
                SELECT 1
                FROM public.farm f
                LEFT JOIN public.irrigation_types it ON it.irrigation_type_id = f.irrigation_type_id
                WHERE f.irrigation_type_id IS NULL
                   OR f.irrigation_type IS DISTINCT FROM it.irrigation_type_name
            )
        $sql$ INTO has_mismatch;
        IF has_mismatch THEN
            RAISE EXCEPTION 'Farm irrigation labels do not match their IDs. Resolve the mismatches before running this migration.';
        END IF;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'crop' AND column_name = 'crop_category'
    ) THEN
        EXECUTE $sql$
            SELECT EXISTS (
                SELECT 1
                FROM public.crop c
                LEFT JOIN public.crop_categories cc ON cc.crop_category_id = c.crop_category_id
                WHERE c.crop_category_id IS NULL
                   OR c.crop_category IS DISTINCT FROM cc.crop_category_name
            )
        $sql$ INTO has_mismatch;
        IF has_mismatch THEN
            RAISE EXCEPTION 'Crop category labels do not match their IDs. Resolve the mismatches before running this migration.';
        END IF;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'crop' AND column_name = 'recommended_season'
    ) THEN
        EXECUTE $sql$
            SELECT EXISTS (
                SELECT 1
                FROM public.crop c
                LEFT JOIN public.recommended_seasons rs ON rs.recommended_season_id = c.recommended_season_id
                WHERE c.recommended_season_id IS NULL
                   OR c.recommended_season IS DISTINCT FROM rs.recommended_season_name
            )
        $sql$ INTO has_mismatch;
        IF has_mismatch THEN
            RAISE EXCEPTION 'Crop season labels do not match their IDs. Resolve the mismatches before running this migration.';
        END IF;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'planting' AND column_name = 'status'
    ) THEN
        EXECUTE $sql$
            SELECT EXISTS (
                SELECT 1
                FROM public.planting p
                LEFT JOIN public.planting_statuses ps ON ps.status_id = p.status_id
                WHERE p.status_id IS NULL
                   OR p.status IS DISTINCT FROM ps.status_name
            )
        $sql$ INTO has_mismatch;
        IF has_mismatch THEN
            RAISE EXCEPTION 'Planting status labels do not match their IDs. Resolve the mismatches before running this migration.';
        END IF;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'harvest' AND column_name = 'quality_grade'
    ) THEN
        EXECUTE $sql$
            SELECT EXISTS (
                SELECT 1
                FROM public.harvest h
                LEFT JOIN public.harvest_quality_grades q ON q.quality_grade_id = h.quality_grade_id
                WHERE h.quality_grade_id IS NULL
                   OR h.quality_grade IS DISTINCT FROM q.quality_grade_name
            )
        $sql$ INTO has_mismatch;
        IF has_mismatch THEN
            RAISE EXCEPTION 'Harvest quality labels do not match their IDs. Resolve the mismatches before running this migration.';
        END IF;
    END IF;
END;
$$;

ALTER TABLE public.farm
    DROP COLUMN IF EXISTS soil_type,
    DROP COLUMN IF EXISTS irrigation_type;

ALTER TABLE public.crop
    DROP COLUMN IF EXISTS crop_category,
    DROP COLUMN IF EXISTS recommended_season;

ALTER TABLE public.planting
    DROP COLUMN IF EXISTS status;

ALTER TABLE public.harvest
    DROP COLUMN IF EXISTS quality_grade;

COMMIT;
