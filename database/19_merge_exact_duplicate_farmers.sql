-- Safely merge exact farmer duplicates: same normalized first name, last name,
-- contact number, and address. Keep the lowest farmer_id in each group.
-- Farms are reassigned before duplicate farmer rows are removed.
-- Rows that only share a contact/address but have different names are NOT merged.
-- If reassigning farms would collide on (farmer_id, farm_name), the script aborts.

BEGIN;

DROP TABLE IF EXISTS pg_temp.farmer_merge_map;

CREATE TEMP TABLE farmer_merge_map ON COMMIT PRESERVE ROWS AS
WITH normalized AS (
    SELECT farmer_id,
           LOWER(BTRIM(first_name)) AS normalized_first_name,
           LOWER(BTRIM(last_name)) AS normalized_last_name,
           REGEXP_REPLACE(BTRIM(contact_number), '\D', '', 'g') AS normalized_contact,
           LOWER(REGEXP_REPLACE(BTRIM(address), '\s+', ' ', 'g')) AS normalized_address
    FROM public.farmer
), ranked AS (
    SELECT farmer_id,
           FIRST_VALUE(farmer_id) OVER (
               PARTITION BY normalized_first_name, normalized_last_name,
                            normalized_contact, normalized_address
               ORDER BY farmer_id
           ) AS keeper_farmer_id,
           ROW_NUMBER() OVER (
               PARTITION BY normalized_first_name, normalized_last_name,
                            normalized_contact, normalized_address
               ORDER BY farmer_id
           ) AS duplicate_rank,
           normalized_contact
    FROM normalized
)
SELECT farmer_id AS duplicate_farmer_id,
       keeper_farmer_id
FROM ranked
WHERE duplicate_rank > 1
  AND normalized_contact <> '';

-- Stop before changing anything if a duplicate farmer's farm would conflict
-- with a farm already owned by the keeper. The transaction then rolls back.
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM farmer_merge_map AS merge_map
        JOIN public.farm AS duplicate_farm
          ON duplicate_farm.farmer_id = merge_map.duplicate_farmer_id
        JOIN public.farm AS keeper_farm
          ON keeper_farm.farmer_id = merge_map.keeper_farmer_id
         AND LOWER(BTRIM(keeper_farm.farm_name)) =
             LOWER(BTRIM(duplicate_farm.farm_name))
    ) THEN
        RAISE EXCEPTION
            'Farmer merge stopped: duplicate owners have colliding farm names. Review these farms before merging.';
    END IF;
END;
$$;

-- Preserve all dependent farms and their plantings/harvests under the keeper.
UPDATE public.farm AS farm
SET farmer_id = merge_map.keeper_farmer_id
FROM farmer_merge_map AS merge_map
WHERE farm.farmer_id = merge_map.duplicate_farmer_id;

DELETE FROM public.farmer AS farmer
USING farmer_merge_map AS merge_map
WHERE farmer.farmer_id = merge_map.duplicate_farmer_id;

COMMIT;

-- Show each deleted duplicate's surviving farmer ID and the remaining
-- duplicate candidates by the same identity rule.
SELECT duplicate_farmer_id, keeper_farmer_id
FROM farmer_merge_map
ORDER BY duplicate_farmer_id;

SELECT a.farmer_id AS first_id,
       b.farmer_id AS possible_duplicate_id,
       a.first_name, a.last_name, a.contact_number, a.address
FROM public.farmer AS a
JOIN public.farmer AS b
  ON b.farmer_id > a.farmer_id
 AND LOWER(BTRIM(a.first_name)) = LOWER(BTRIM(b.first_name))
 AND LOWER(BTRIM(a.last_name)) = LOWER(BTRIM(b.last_name))
 AND REGEXP_REPLACE(BTRIM(a.contact_number), '\D', '', 'g') =
     REGEXP_REPLACE(BTRIM(b.contact_number), '\D', '', 'g')
 AND LOWER(REGEXP_REPLACE(BTRIM(a.address), '\s+', ' ', 'g')) =
     LOWER(REGEXP_REPLACE(BTRIM(b.address), '\s+', ' ', 'g'))
ORDER BY a.farmer_id, b.farmer_id;

DROP TABLE farmer_merge_map;
