-- Farmer duplicate rule: first_name + last_name + address must match after
-- trimming, lowercasing, collapsing whitespace, and removing whitespace around commas.
-- Contact number is deliberately not part of this rule.
-- Review this SELECT first. It lists existing pairs matching the new rule.
-- No existing farmer rows are modified or deleted by this script.

SELECT
    a.farmer_id AS farmer_id_1,
    a.first_name AS first_name_1,
    a.last_name AS last_name_1,
    a.address AS address_1,
    b.farmer_id AS farmer_id_2,
    b.first_name AS first_name_2,
    b.last_name AS last_name_2,
    b.address AS address_2
FROM public.farmer AS a
JOIN public.farmer AS b ON a.farmer_id < b.farmer_id
WHERE LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(a.first_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
    = LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(b.first_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
  AND LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(a.last_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
    = LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(b.last_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
  AND LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(a.address), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
    = LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(b.address), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
ORDER BY a.farmer_id, b.farmer_id;

-- Install the revised guard after reviewing the SELECT output above.
BEGIN;

CREATE OR REPLACE FUNCTION public.reject_duplicate_farmer()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'UPDATE'
       AND ROW(
            LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(NEW.first_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g')),
            LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(NEW.last_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g')),
            LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(NEW.address), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
       ) IS NOT DISTINCT FROM ROW(
            LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(OLD.first_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g')),
            LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(OLD.last_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g')),
            LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(OLD.address), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
       ) THEN
        RETURN NEW;
    END IF;

    PERFORM pg_advisory_xact_lock(881001);

    IF EXISTS (
        SELECT 1
        FROM public.farmer AS other
        WHERE other.farmer_id <> COALESCE(NEW.farmer_id, -1)
          AND LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(other.first_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
              = LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(NEW.first_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
          AND LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(other.last_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
              = LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(NEW.last_name), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
          AND LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(other.address), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
              = LOWER(REGEXP_REPLACE(REGEXP_REPLACE(BTRIM(NEW.address), '[[:space:]]*,[[:space:]]*', ',', 'g'), '[[:space:]]+', ' ', 'g'))
    ) THEN
        RAISE EXCEPTION 'A farmer with the same name and address already exists.'
            USING ERRCODE = '23505', CONSTRAINT = 'farmer_duplicate_guard';
    END IF;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS farmer_duplicate_guard ON public.farmer;
CREATE TRIGGER farmer_duplicate_guard
BEFORE INSERT OR UPDATE OF first_name, last_name, address
ON public.farmer
FOR EACH ROW EXECUTE FUNCTION public.reject_duplicate_farmer();

COMMIT;
