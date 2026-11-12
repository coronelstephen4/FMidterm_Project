-- Replace only generated Farm Location NN, Cebu placeholders with the
-- user-provided real location names. Locations are distributed in farm_id
-- order, cycling evenly through the six approved values.
-- This updates farm.location only. Review the returned farm IDs and locations.

BEGIN;

WITH location_pool(location_order, replacement_location) AS (
    VALUES
        (1, 'Cogon, Compostela, Cebu City'),
        (2, 'Banhigan, Badian, Cebu City'),
        (3, 'Cansaga, Consolacion, Cebu City'),
        (4, 'Cogon, Poblacion, Liloan, Cebu'),
        (5, 'Talisay, Cebu City'),
        (6, 'Farm 13 - Liloan')
), ranked_targets AS (
    SELECT farm_id,
           ROW_NUMBER() OVER (ORDER BY farm_id) - 1 AS row_index
    FROM public.farm
    WHERE location ~* '^Farm Location [0-9]+,[[:space:]]*Cebu$'
), replacement_map AS (
    SELECT target.farm_id, location.replacement_location
    FROM ranked_targets AS target
    INNER JOIN location_pool AS location
        ON location.location_order = (target.row_index % 6) + 1
), updated_farms AS (
    UPDATE public.farm AS farm
    SET location = replacement_map.replacement_location,
        updated_at = CURRENT_TIMESTAMP
    FROM replacement_map
    WHERE farm.farm_id = replacement_map.farm_id
    RETURNING farm.farm_id, farm.farm_name, farm.location
)
SELECT farm_id, farm_name, location
FROM updated_farms
ORDER BY farm_id;

COMMIT;
