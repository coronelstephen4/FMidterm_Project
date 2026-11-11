-- Apply the user's three supplied address values repeatedly to the remaining
-- AIS placeholder addresses, ordered by farmer_id descending as displayed.
-- Run in Supabase SQL Editor, then review the returned rows.
BEGIN;

WITH address_cycle AS (
    SELECT
        farmer_id,
        CASE ((ROW_NUMBER() OVER (ORDER BY farmer_id DESC) - 1) % 3)
            WHEN 0 THEN 'Cogon, Poblacion, Liloan, Cebu City'
            WHEN 1 THEN 'Cansaga, Consolacion, Cebu City'
            ELSE 'Maguikay, Mandaue, Cebu City'
        END AS new_address
    FROM public.farmer
    WHERE address LIKE 'AIS Address %, Cebu'
)
UPDATE public.farmer AS farmer
SET address = address_cycle.new_address
FROM address_cycle
WHERE farmer.farmer_id = address_cycle.farmer_id;

COMMIT;

SELECT farmer_id, first_name, last_name, address
FROM public.farmer
ORDER BY farmer_id;
