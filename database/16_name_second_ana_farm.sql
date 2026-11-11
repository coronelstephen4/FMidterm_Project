-- Give Ana Alonzo's second farm a distinct name that meets the required farm_name field.
BEGIN;

UPDATE public.farm
SET farm_name = 'Ana Alonzo''s Farm'
WHERE farm_id = 7
  AND farmer_id = 8
  AND farm_name = 'Farm 01 - Ana Alonzo'
  AND NOT EXISTS (
      SELECT 1
      FROM public.farm AS other_farm
      WHERE other_farm.farmer_id = 8
        AND other_farm.farm_name = 'Ana Alonzo''s Farm'
        AND other_farm.farm_id <> 7
  );

COMMIT;

SELECT farm_id, farmer_id, farm_name
FROM public.farm
WHERE farm_id IN (7, 22)
ORDER BY farm_id;
