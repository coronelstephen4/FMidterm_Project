-- Removes the Demo prefix from the generated farmer and farm sample labels.
-- Only the known sample address, farm name, and location formats are changed.
BEGIN;

UPDATE public.farmer
SET address = regexp_replace(address, '^AIS[[:space:]]+Demo[[:space:]]+', 'AIS ', 'i')
WHERE address ~* '^AIS[[:space:]]+Demo[[:space:]]+Address[[:space:]]+[0-9]+,[[:space:]]*Cebu$';

UPDATE public.farm
SET farm_name = regexp_replace(farm_name, '^Demo[[:space:]]+', '', 'i'),
    location = regexp_replace(location, '^Demo[[:space:]]+', '', 'i')
WHERE farm_name ~* '^Demo[[:space:]]+Farm[[:space:]]+[0-9]+[[:space:]]+-'
  AND location ~* '^Demo[[:space:]]+Farm[[:space:]]+Location[[:space:]]+[0-9]+,[[:space:]]*Cebu$';

COMMIT;
