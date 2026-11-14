-- Review-only checks for AIS form validation. This file has NOT been run.
-- First run only the SELECT statements below and review all returned rows.
-- If any rows appear, do not run the constraint section until they are resolved.

-- Farmer first-name format/length
SELECT farmer_id, first_name FROM public.farmer
WHERE first_name !~ '^[[:alpha:] .''-]{2,50}$';

-- Farmer last-name format/length
SELECT farmer_id, last_name FROM public.farmer
WHERE last_name !~ '^[[:alpha:] .''-]{2,50}$';

-- Farmer contacts must be legacy Philippine 09 values or E.164 (+country code)
SELECT farmer_id, contact_number FROM public.farmer
WHERE contact_number !~ '^09[0-9]{9}$'
  AND contact_number !~ '^\+[1-9][0-9]{7,14}$';

-- Farmer address length
SELECT farmer_id, address FROM public.farmer
WHERE char_length(btrim(address)) < 5 OR char_length(address) > 150;

-- Registration date cannot be in the future (application-only rule)
SELECT farmer_id, date_registered FROM public.farmer
WHERE date_registered > CURRENT_DATE;

-- Farm name length
SELECT farm_id, farm_name FROM public.farm
WHERE char_length(btrim(farm_name)) < 2 OR char_length(farm_name) > 100;

-- Farm location length
SELECT farm_id, location FROM public.farm
WHERE char_length(btrim(location)) < 2 OR char_length(location) > 150;

-- Farm area range and two-decimal precision
SELECT farm_id, area_hectares FROM public.farm
WHERE area_hectares <= 0 OR area_hectares > 10000 OR area_hectares <> round(area_hectares, 2);

-- Crop name format/length
SELECT crop_id, crop_name FROM public.crop
WHERE crop_name !~ '^[[:alpha:] -]{2,80}$';

-- Crop variety name length
SELECT variety_id, variety_name FROM public.crop_variety
WHERE char_length(btrim(variety_name)) < 1 OR char_length(variety_name) > 80;

-- Crop variety growing period range
SELECT variety_id, growing_period_days FROM public.crop_variety
WHERE growing_period_days < 1 OR growing_period_days > 730;

-- Planting expected harvest date must be on or after planting date
SELECT planting_id, planting_date, expected_harvest_date FROM public.planting
WHERE expected_harvest_date < planting_date;

-- Planted area must be positive, within farm area, and use at most two decimals
SELECT p.planting_id, p.farm_id, p.area_planted, f.area_hectares
FROM public.planting p
JOIN public.farm f ON f.farm_id = p.farm_id
WHERE p.area_planted <= 0 OR p.area_planted <> round(p.area_planted, 2)
   OR p.area_planted > f.area_hectares;

-- Seed quantity must be positive with at most two decimal places
SELECT planting_id, seed_quantity FROM public.planting
WHERE seed_quantity <= 0 OR seed_quantity <> round(seed_quantity, 2);

-- Harvest date must not precede the associated planting date (application-only cross-table rule)
SELECT h.harvest_id, h.planting_id, h.harvest_date, p.planting_date
FROM public.harvest h
JOIN public.planting p ON p.planting_id = h.planting_id
WHERE h.harvest_date < p.planting_date;

-- Harvest quantities/prices and two-decimal precision
SELECT harvest_id, quantity_kg, selling_price_per_kg FROM public.harvest
WHERE quantity_kg <= 0 OR selling_price_per_kg < 0
   OR quantity_kg <> round(quantity_kg, 2)
   OR selling_price_per_kg <> round(selling_price_per_kg, 2);

-- Constraint section: run only after reviewing the SELECT results.
-- PostgreSQL CHECK constraints cannot safely express current-date or cross-table rules;
-- those remain validated by the application above.

ALTER TABLE public.farmer ADD CONSTRAINT farmer_first_name_format CHECK (first_name ~ '^[[:alpha:] .''-]{2,50}$') NOT VALID;
ALTER TABLE public.farmer VALIDATE CONSTRAINT farmer_first_name_format;

ALTER TABLE public.farmer ADD CONSTRAINT farmer_last_name_format CHECK (last_name ~ '^[[:alpha:] .''-]{2,50}$') NOT VALID;
ALTER TABLE public.farmer VALIDATE CONSTRAINT farmer_last_name_format;

-- Deliberately do not add the contact-format CHECK yet. Existing farmer rows
-- fail the pre-check. Even a NOT VALID CHECK applies to updated rows, so adding
-- it now would block unrelated edits to legacy records. PHP validates and stores
-- new/changed numbers in E.164 format; unchanged legacy values remain editable.

ALTER TABLE public.farmer ADD CONSTRAINT farmer_address_length CHECK (char_length(btrim(address)) BETWEEN 5 AND 150) NOT VALID;
ALTER TABLE public.farmer VALIDATE CONSTRAINT farmer_address_length;

ALTER TABLE public.farm ADD CONSTRAINT farm_name_length CHECK (char_length(btrim(farm_name)) BETWEEN 2 AND 100) NOT VALID;
ALTER TABLE public.farm VALIDATE CONSTRAINT farm_name_length;

ALTER TABLE public.farm ADD CONSTRAINT farm_location_length CHECK (char_length(btrim(location)) BETWEEN 2 AND 150) NOT VALID;
ALTER TABLE public.farm VALIDATE CONSTRAINT farm_location_length;

ALTER TABLE public.farm ADD CONSTRAINT farm_area_range CHECK (area_hectares > 0 AND area_hectares <= 10000 AND area_hectares = round(area_hectares, 2)) NOT VALID;
ALTER TABLE public.farm VALIDATE CONSTRAINT farm_area_range;

ALTER TABLE public.crop ADD CONSTRAINT crop_name_format CHECK (crop_name ~ '^[[:alpha:] -]{2,80}$') NOT VALID;
ALTER TABLE public.crop VALIDATE CONSTRAINT crop_name_format;

ALTER TABLE public.crop_variety ADD CONSTRAINT crop_variety_name_length CHECK (char_length(btrim(variety_name)) BETWEEN 1 AND 80) NOT VALID;
ALTER TABLE public.crop_variety VALIDATE CONSTRAINT crop_variety_name_length;

ALTER TABLE public.crop_variety ADD CONSTRAINT crop_variety_growing_period_range CHECK (growing_period_days BETWEEN 1 AND 730) NOT VALID;
ALTER TABLE public.crop_variety VALIDATE CONSTRAINT crop_variety_growing_period_range;

-- Migration 07 currently requires strictly later. Replace that check to allow the requested same-day date.
ALTER TABLE public.planting DROP CONSTRAINT IF EXISTS planting_expected_date_after_planting;
ALTER TABLE public.planting ADD CONSTRAINT planting_expected_date_on_or_after_planting CHECK (expected_harvest_date >= planting_date) NOT VALID;
ALTER TABLE public.planting VALIDATE CONSTRAINT planting_expected_date_on_or_after_planting;

ALTER TABLE public.planting ADD CONSTRAINT planting_area_positive_precision CHECK (area_planted > 0 AND area_planted = round(area_planted, 2)) NOT VALID;
ALTER TABLE public.planting VALIDATE CONSTRAINT planting_area_positive_precision;

ALTER TABLE public.planting ADD CONSTRAINT planting_seed_quantity_precision CHECK (seed_quantity > 0 AND seed_quantity = round(seed_quantity, 2)) NOT VALID;
ALTER TABLE public.planting VALIDATE CONSTRAINT planting_seed_quantity_precision;

ALTER TABLE public.harvest ADD CONSTRAINT harvest_amount_precision CHECK (quantity_kg > 0 AND selling_price_per_kg >= 0 AND quantity_kg = round(quantity_kg, 2) AND selling_price_per_kg = round(selling_price_per_kg, 2)) NOT VALID;
ALTER TABLE public.harvest VALIDATE CONSTRAINT harvest_amount_precision;

-- Existing foreign keys already enforce valid lookup/parent IDs.
-- The contact format CHECK remains deferred until legacy contact values are verified.
