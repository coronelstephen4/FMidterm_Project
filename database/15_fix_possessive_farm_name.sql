-- Correct the possessive capitalization on the explicitly matching farm name.
BEGIN;

UPDATE public.farm
SET farm_name = 'Santiago''s Farm'
WHERE farm_id = 27
  AND farm_name = 'Santiago''S Farm';

COMMIT;

SELECT farm_id, farm_name
FROM public.farm
WHERE farm_id = 27;
