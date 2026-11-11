-- Match the website's duplicate checks in Supabase so direct/concurrent writes
-- cannot bypass the application checks. Existing records are not changed or
-- deleted. A duplicate update is rejected only when its identifying fields
-- change to a value already used by another row.

BEGIN;

CREATE OR REPLACE FUNCTION public.reject_duplicate_farmer()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'UPDATE' THEN
        IF ROW(NEW.first_name, NEW.last_name, NEW.contact_number, NEW.address)
           IS NOT DISTINCT FROM
           ROW(OLD.first_name, OLD.last_name, OLD.contact_number, OLD.address) THEN
            RETURN NEW;
        END IF;
    END IF;

    PERFORM pg_advisory_xact_lock(881001);

    IF EXISTS (
        SELECT 1
        FROM public.farmer AS other
        WHERE other.farmer_id <> COALESCE(NEW.farmer_id, -1)
          AND (
              (regexp_replace(BTRIM(other.contact_number), '\D', '', 'g') =
               regexp_replace(BTRIM(NEW.contact_number), '\D', '', 'g')
               AND LOWER(REGEXP_REPLACE(BTRIM(other.address), '\s+', ' ', 'g')) =
                   LOWER(REGEXP_REPLACE(BTRIM(NEW.address), '\s+', ' ', 'g')))
              OR
              (LOWER(BTRIM(other.first_name)) = LOWER(BTRIM(NEW.first_name))
               AND LOWER(BTRIM(other.last_name)) = LOWER(BTRIM(NEW.last_name))
               AND LOWER(REGEXP_REPLACE(BTRIM(other.address), '\s+', ' ', 'g')) =
                   LOWER(REGEXP_REPLACE(BTRIM(NEW.address), '\s+', ' ', 'g')))
          )
    ) THEN
        RAISE EXCEPTION 'A farmer with matching contact/address or name/address already exists.'
            USING ERRCODE = '23505', CONSTRAINT = 'farmer_duplicate_guard';
    END IF;

    RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION public.reject_duplicate_farm()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'UPDATE' THEN
        IF ROW(NEW.farmer_id, NEW.farm_name) IS NOT DISTINCT FROM
           ROW(OLD.farmer_id, OLD.farm_name) THEN
            RETURN NEW;
        END IF;
    END IF;

    PERFORM pg_advisory_xact_lock(881002);

    IF EXISTS (
        SELECT 1 FROM public.farm AS other
        WHERE other.farm_id <> COALESCE(NEW.farm_id, -1)
          AND other.farmer_id = NEW.farmer_id
          AND LOWER(BTRIM(other.farm_name)) = LOWER(BTRIM(NEW.farm_name))
    ) THEN
        RAISE EXCEPTION 'This farmer already has a farm with that name.'
            USING ERRCODE = '23505', CONSTRAINT = 'farm_duplicate_guard';
    END IF;

    RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION public.reject_duplicate_crop()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'UPDATE' THEN
        IF ROW(NEW.crop_name, NEW.variety) IS NOT DISTINCT FROM
           ROW(OLD.crop_name, OLD.variety) THEN
            RETURN NEW;
        END IF;
    END IF;

    PERFORM pg_advisory_xact_lock(881003);

    IF EXISTS (
        SELECT 1 FROM public.crop AS other
        WHERE other.crop_id <> COALESCE(NEW.crop_id, -1)
          AND LOWER(BTRIM(other.crop_name)) = LOWER(BTRIM(NEW.crop_name))
          AND LOWER(BTRIM(other.variety)) = LOWER(BTRIM(NEW.variety))
    ) THEN
        RAISE EXCEPTION 'This crop and variety already exist.'
            USING ERRCODE = '23505', CONSTRAINT = 'crop_duplicate_guard';
    END IF;

    RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION public.reject_duplicate_planting()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'UPDATE' THEN
        IF ROW(NEW.farm_id, NEW.crop_id, NEW.planting_date, NEW.area_planted,
               NEW.seed_quantity, NEW.expected_harvest_date, NEW.status_id)
           IS NOT DISTINCT FROM
           ROW(OLD.farm_id, OLD.crop_id, OLD.planting_date, OLD.area_planted,
               OLD.seed_quantity, OLD.expected_harvest_date, OLD.status_id) THEN
            RETURN NEW;
        END IF;
    END IF;

    PERFORM pg_advisory_xact_lock(881004);

    IF EXISTS (
        SELECT 1 FROM public.planting AS other
        WHERE other.planting_id <> COALESCE(NEW.planting_id, -1)
          AND ROW(other.farm_id, other.crop_id, other.planting_date,
                  other.area_planted, other.seed_quantity,
                  other.expected_harvest_date, other.status_id)
              IS NOT DISTINCT FROM
              ROW(NEW.farm_id, NEW.crop_id, NEW.planting_date,
                  NEW.area_planted, NEW.seed_quantity,
                  NEW.expected_harvest_date, NEW.status_id)
    ) THEN
        RAISE EXCEPTION 'An identical planting record already exists.'
            USING ERRCODE = '23505', CONSTRAINT = 'planting_duplicate_guard';
    END IF;

    RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION public.reject_duplicate_harvest()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'UPDATE' THEN
        IF ROW(NEW.planting_id, NEW.harvest_date, NEW.quantity_kg,
               NEW.selling_price_per_kg, NEW.quality_grade_id, NEW.remark_id)
           IS NOT DISTINCT FROM
           ROW(OLD.planting_id, OLD.harvest_date, OLD.quantity_kg,
               OLD.selling_price_per_kg, OLD.quality_grade_id, OLD.remark_id) THEN
            RETURN NEW;
        END IF;
    END IF;

    PERFORM pg_advisory_xact_lock(881005);

    IF EXISTS (
        SELECT 1 FROM public.harvest AS other
        WHERE other.harvest_id <> COALESCE(NEW.harvest_id, -1)
          AND ROW(other.planting_id, other.harvest_date, other.quantity_kg,
                  other.selling_price_per_kg, other.quality_grade_id, other.remark_id)
              IS NOT DISTINCT FROM
              ROW(NEW.planting_id, NEW.harvest_date, NEW.quantity_kg,
                  NEW.selling_price_per_kg, NEW.quality_grade_id, NEW.remark_id)
    ) THEN
        RAISE EXCEPTION 'An identical harvest record already exists.'
            USING ERRCODE = '23505', CONSTRAINT = 'harvest_duplicate_guard';
    END IF;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS farmer_duplicate_guard ON public.farmer;
CREATE TRIGGER farmer_duplicate_guard
BEFORE INSERT OR UPDATE OF first_name, last_name, contact_number, address
ON public.farmer
FOR EACH ROW EXECUTE FUNCTION public.reject_duplicate_farmer();

DROP TRIGGER IF EXISTS farm_duplicate_guard ON public.farm;
CREATE TRIGGER farm_duplicate_guard
BEFORE INSERT OR UPDATE OF farmer_id, farm_name
ON public.farm
FOR EACH ROW EXECUTE FUNCTION public.reject_duplicate_farm();

DROP TRIGGER IF EXISTS crop_duplicate_guard ON public.crop;
CREATE TRIGGER crop_duplicate_guard
BEFORE INSERT OR UPDATE OF crop_name, variety
ON public.crop
FOR EACH ROW EXECUTE FUNCTION public.reject_duplicate_crop();

DROP TRIGGER IF EXISTS planting_duplicate_guard ON public.planting;
CREATE TRIGGER planting_duplicate_guard
BEFORE INSERT OR UPDATE OF farm_id, crop_id, planting_date, area_planted,
    seed_quantity, expected_harvest_date, status_id
ON public.planting
FOR EACH ROW EXECUTE FUNCTION public.reject_duplicate_planting();

DROP TRIGGER IF EXISTS harvest_duplicate_guard ON public.harvest;
CREATE TRIGGER harvest_duplicate_guard
BEFORE INSERT OR UPDATE OF planting_id, harvest_date, quantity_kg,
    selling_price_per_kg, quality_grade_id, remark_id
ON public.harvest
FOR EACH ROW EXECUTE FUNCTION public.reject_duplicate_harvest();

COMMIT;

-- Verify that the five duplicate guards were installed.
SELECT DISTINCT event_object_table AS table_name, trigger_name
FROM information_schema.triggers
WHERE trigger_schema = 'public'
  AND trigger_name IN (
      'farmer_duplicate_guard', 'farm_duplicate_guard', 'crop_duplicate_guard',
      'planting_duplicate_guard', 'harvest_duplicate_guard'
  )
ORDER BY event_object_table;
