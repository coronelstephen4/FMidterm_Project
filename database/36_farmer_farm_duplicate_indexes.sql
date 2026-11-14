-- Review-only unique indexes for the selected Farmer and Farm duplicate rules.
-- Back up the database first and review every pre-check result before applying.
-- Farmer duplicates are the same normalized first name, last name, and address.
-- contact_number is intentionally excluded; sharing a contact does not block a save.

-- Read-only pre-check: duplicate Farmer identities under the current PHP rule.
SELECT lower(btrim(first_name)) AS normalized_first_name,
       lower(btrim(last_name)) AS normalized_last_name,
       lower(regexp_replace(btrim(address), '\s+', ' ', 'g')) AS normalized_address,
       array_agg(farmer_id ORDER BY farmer_id) AS farmer_ids,
       count(*) AS duplicate_count
FROM public.farmer
GROUP BY lower(btrim(first_name)),
         lower(btrim(last_name)),
         lower(regexp_replace(btrim(address), '\s+', ' ', 'g'))
HAVING count(*) > 1
ORDER BY farmer_ids;

-- Read-only pre-check: duplicate Farm identities under the current PHP rule.
SELECT farmer_id,
       lower(btrim(farm_name)) AS normalized_farm_name,
       lower(regexp_replace(btrim(location), '\s+', ' ', 'g')) AS normalized_location,
       area_hectares,
       irrigation_type_id,
       soil_type_id,
       array_agg(farm_id ORDER BY farm_id) AS farm_ids,
       count(*) AS duplicate_count
FROM public.farm
GROUP BY farmer_id,
         lower(btrim(farm_name)),
         lower(regexp_replace(btrim(location), '\s+', ' ', 'g')),
         area_hectares,
         irrigation_type_id,
         soil_type_id
HAVING count(*) > 1
ORDER BY farm_ids;

BEGIN;

CREATE UNIQUE INDEX IF NOT EXISTS farmer_identity_uq
    ON public.farmer (
        lower(btrim(first_name)),
        lower(btrim(last_name)),
        lower(regexp_replace(btrim(address), '\s+', ' ', 'g'))
    );

CREATE UNIQUE INDEX IF NOT EXISTS farm_identity_uq
    ON public.farm (
        farmer_id,
        lower(btrim(farm_name)),
        lower(regexp_replace(btrim(location), '\s+', ' ', 'g')),
        area_hectares,
        irrigation_type_id,
        soil_type_id
    );

-- Review the pre-checks and back up before changing this to COMMIT.
ROLLBACK;
