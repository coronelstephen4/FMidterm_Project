-- Enforce required values and practical data ranges on the AIS tables.
-- Existing legacy text columns for fixed values are left in place for now.

BEGIN;

ALTER TABLE public.farmer
    ALTER COLUMN first_name SET NOT NULL,
    ALTER COLUMN last_name SET NOT NULL,
    ALTER COLUMN contact_number SET NOT NULL,
    ALTER COLUMN address SET NOT NULL,
    ALTER COLUMN date_registered SET NOT NULL,
    ADD CONSTRAINT farmer_first_name_not_blank CHECK (BTRIM(first_name) <> ''),
    ADD CONSTRAINT farmer_last_name_not_blank CHECK (BTRIM(last_name) <> ''),
    ADD CONSTRAINT farmer_contact_not_blank CHECK (BTRIM(contact_number) <> ''),
    ADD CONSTRAINT farmer_address_not_blank CHECK (BTRIM(address) <> '');

ALTER TABLE public.farm
    ALTER COLUMN farmer_id SET NOT NULL,
    ALTER COLUMN farm_name SET NOT NULL,
    ALTER COLUMN location SET NOT NULL,
    ALTER COLUMN area_hectares SET NOT NULL,
    ALTER COLUMN irrigation_type_id SET NOT NULL,
    ALTER COLUMN soil_type_id SET NOT NULL,
    ADD CONSTRAINT farm_name_not_blank CHECK (BTRIM(farm_name) <> ''),
    ADD CONSTRAINT farm_location_not_blank CHECK (BTRIM(location) <> ''),
    ADD CONSTRAINT farm_area_positive CHECK (area_hectares > 0),
    ADD CONSTRAINT farm_farmer_name_unique UNIQUE (farmer_id, farm_name);

ALTER TABLE public.crop
    ALTER COLUMN crop_name SET NOT NULL,
    ALTER COLUMN variety SET NOT NULL,
    ALTER COLUMN crop_category_id SET NOT NULL,
    ALTER COLUMN growing_period_days SET NOT NULL,
    ALTER COLUMN recommended_season_id SET NOT NULL,
    ADD CONSTRAINT crop_name_not_blank CHECK (BTRIM(crop_name) <> ''),
    ADD CONSTRAINT crop_variety_not_blank CHECK (BTRIM(variety) <> ''),
    ADD CONSTRAINT crop_growing_period_positive CHECK (growing_period_days > 0),
    ADD CONSTRAINT crop_name_variety_unique UNIQUE (crop_name, variety);

ALTER TABLE public.planting
    ALTER COLUMN farm_id SET NOT NULL,
    ALTER COLUMN crop_id SET NOT NULL,
    ALTER COLUMN planting_date SET NOT NULL,
    ALTER COLUMN area_planted SET NOT NULL,
    ALTER COLUMN seed_quantity SET NOT NULL,
    ALTER COLUMN expected_harvest_date SET NOT NULL,
    ALTER COLUMN status_id SET NOT NULL,
    ADD CONSTRAINT planting_area_positive CHECK (area_planted > 0),
    ADD CONSTRAINT planting_seed_quantity_positive CHECK (seed_quantity > 0),
    ADD CONSTRAINT planting_expected_date_after_planting CHECK (expected_harvest_date > planting_date);

ALTER TABLE public.harvest
    ALTER COLUMN planting_id SET NOT NULL,
    ALTER COLUMN harvest_date SET NOT NULL,
    ALTER COLUMN quantity_kg SET NOT NULL,
    ALTER COLUMN selling_price_per_kg SET NOT NULL,
    ALTER COLUMN quality_grade_id SET NOT NULL,
    ALTER COLUMN remark_id SET NOT NULL,
    ADD CONSTRAINT harvest_quantity_positive CHECK (quantity_kg > 0),
    ADD CONSTRAINT harvest_price_nonnegative CHECK (selling_price_per_kg >= 0);

COMMIT;
