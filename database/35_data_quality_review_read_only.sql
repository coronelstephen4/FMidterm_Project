-- Read-only data-quality review for the AIS live records.
-- This file contains SELECT statements only. Review results and correct any
-- records through the application; no changes are proposed here.

-- a) Finds likely contradictory harvest grade/remark combinations, such as
-- Grade A with a poor/damaged remark or Grade C with a good/excellent remark.
SELECT h.harvest_id,
       q.quality_grade_name,
       r.remark_description,
       h.quantity_kg,
       h.selling_price_per_kg,
       h.harvest_date
FROM public.harvest AS h
JOIN public.harvest_quality_grades AS q
  ON q.quality_grade_id = h.quality_grade_id
JOIN public.harvest_remarks AS r
  ON r.remark_id = h.remark_id
WHERE (
        lower(btrim(q.quality_grade_name)) ~ '(^|[^a-z])a([^a-z]|$)|excellent|very good'
        AND lower(r.remark_description) ~ 'poor|damaged'
      )
   OR (
        lower(btrim(q.quality_grade_name)) ~ '(^|[^a-z])c([^a-z]|$)|poor|low'
        AND lower(r.remark_description) ~ 'good|excellent|high quality'
      )
ORDER BY h.harvest_id;

-- b1) Groups address spellings after standardizing comma spacing, outer
-- whitespace, repeated spaces, and case. Multiple original values in one
-- group indicate formatting variants that should be reviewed in the app.
SELECT lower(regexp_replace(
           regexp_replace(btrim(address), '\s*,\s*', ', ', 'g'),
           '\s+', ' ', 'g'
       )) AS normalized_address,
       count(*) AS farmer_count,
       array_agg(farmer_id ORDER BY farmer_id) AS farmer_ids,
       array_agg(DISTINCT address ORDER BY address) AS address_variants
FROM public.farmer
GROUP BY lower(regexp_replace(
             regexp_replace(btrim(address), '\s*,\s*', ', ', 'g'),
             '\s+', ' ', 'g'
         ))
HAVING count(DISTINCT address) > 1
ORDER BY normalized_address;

-- b2) Lists all distinct address values to support manual review for spelling
-- differences between barangay/city names that formatting normalization cannot
-- safely infer as equivalent.
SELECT address, array_agg(farmer_id ORDER BY farmer_id) AS farmer_ids
FROM public.farmer
GROUP BY address
ORDER BY lower(address), address;

-- b3) Flags individual address-format anomalies even when no matching variant
-- exists: no space after a comma, repeated internal whitespace, or trailing
-- whitespace. Similar barangay/city spellings still need human review.
SELECT farmer_id, first_name, last_name, address
FROM public.farmer
WHERE address ~ ',[^[:space:]]'
   OR address ~ '[[:space:]]{2,}'
   OR address ~ '[[:space:]]$'
ORDER BY lower(address), farmer_id;

-- c) Finds farm names beginning with a lowercase letter and locations matching
-- common placeholder patterns (Farm ..., Verified..., placeholder, or test).
SELECT farm_id, farm_name, location, farmer_id
FROM public.farm
WHERE farm_name ~ '^[[:lower:]]'
   OR lower(location) LIKE 'farm %'
   OR lower(location) LIKE 'verified%'
   OR lower(location) LIKE '%placeholder%'
   OR lower(location) LIKE '%test%'
ORDER BY farm_id;

-- d) Shows farmers whose contact is not in +E.164 format and/or who have no
-- farm. Both flags are returned so records needing either review are visible.
SELECT f.farmer_id,
       f.first_name,
       f.last_name,
       f.contact_number,
       count(fa.farm_id) AS farm_count,
       (f.contact_number !~ '^\+[1-9][0-9]{7,14}$') AS invalid_e164_contact,
       (count(fa.farm_id) = 0) AS has_no_farm
FROM public.farmer AS f
LEFT JOIN public.farm AS fa
  ON fa.farmer_id = f.farmer_id
GROUP BY f.farmer_id, f.first_name, f.last_name, f.contact_number
HAVING f.contact_number !~ '^\+[1-9][0-9]{7,14}$'
    OR count(fa.farm_id) = 0
ORDER BY f.farmer_id;

-- e1) Lists plantings dated 2026-11-11 or later with their farm and crop
-- variety names, to help identify possible manual test entries.
SELECT p.planting_id,
       p.planting_date,
       fa.farm_id,
       fa.farm_name,
       cv.variety_id,
       c.crop_name,
       cv.variety_name,
       p.area_planted,
       p.seed_quantity,
       p.expected_harvest_date,
       p.status_id
FROM public.planting AS p
JOIN public.farm AS fa ON fa.farm_id = p.farm_id
JOIN public.crop_variety AS cv ON cv.variety_id = p.variety_id
JOIN public.crop AS c ON c.crop_id = cv.crop_id
WHERE p.planting_date >= DATE '2026-11-11'
ORDER BY p.planting_date, p.planting_id;

-- e2) Lists harvests dated 2026-11-11 or later with the source farm and crop
-- variety names, to help identify possible manual test entries.
SELECT h.harvest_id,
       h.harvest_date,
       p.planting_id,
       fa.farm_id,
       fa.farm_name,
       c.crop_name,
       cv.variety_name,
       h.quantity_kg,
       h.selling_price_per_kg
FROM public.harvest AS h
JOIN public.planting AS p ON p.planting_id = h.planting_id
JOIN public.farm AS fa ON fa.farm_id = p.farm_id
JOIN public.crop_variety AS cv ON cv.variety_id = p.variety_id
JOIN public.crop AS c ON c.crop_id = cv.crop_id
WHERE h.harvest_date >= DATE '2026-11-11'
ORDER BY h.harvest_date, h.harvest_id;
