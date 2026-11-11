-- Remove generated "Farm <number> -" labels while keeping the name that follows.
-- Farm locations and all related records are left unchanged.
BEGIN;

WITH candidates AS (
    SELECT
        farm_id,
        farmer_id,
        BTRIM(REGEXP_REPLACE(farm_name, '^Farm[[:space:]]+[0-9]+[[:space:]]*-[[:space:]]*', '')) AS new_name
    FROM public.farm
    WHERE farm_name ~ '^Farm[[:space:]]+[0-9]+[[:space:]]*-[[:space:]]*'
), safe_names AS (
    SELECT candidate.*
    FROM candidates AS candidate
    WHERE NOT EXISTS (
        SELECT 1
        FROM public.farm AS existing
        WHERE existing.farmer_id = candidate.farmer_id
          AND existing.farm_name = candidate.new_name
          AND existing.farm_id <> candidate.farm_id
    )
      AND NOT EXISTS (
        SELECT 1
        FROM candidates AS newer
        WHERE newer.farmer_id = candidate.farmer_id
          AND newer.new_name = candidate.new_name
          AND newer.farm_id > candidate.farm_id
    )
)
UPDATE public.farm AS farm
SET farm_name = safe_names.new_name
FROM safe_names
WHERE farm.farm_id = safe_names.farm_id;

COMMIT;

SELECT farm_id, farm_name, location
FROM public.farm
ORDER BY farm_id DESC;

SELECT farm_id, farmer_id, farm_name
FROM public.farm
WHERE farm_name ~ '^Farm[[:space:]]+[0-9]+[[:space:]]*-[[:space:]]*'
ORDER BY farm_id;
