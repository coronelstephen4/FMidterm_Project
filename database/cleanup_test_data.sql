-- REVIEW-ONLY historical cleanup candidate script for demo/test data.
-- Cleanup was done through the app, not by running this SQL file.
-- The latest counts below are the verified read-only snapshot at 2026-11-15 05:21.
-- No live database changes were made by preparing or updating this file.
-- The original seeded batch is the shared created_at timestamp
-- 2026-11-05 19:45:54+00 (15 farmers, 15 farms, 10 crops, 10 varieties,
-- 30 plantings, 20 harvests); preserve those rows.
-- Review every timestamp group and NEEDS DECISION row before proceeding.
-- This file ends in ROLLBACK by default. Change only that final ROLLBACK to
-- COMMIT after reviewing the before/after counts and approving every ID.

BEGIN;

-- Timestamp groups observed in each table, for audit/review.
SELECT 'farmer' AS table_name, date_trunc('second', created_at) AS created_group,
       count(*) AS row_count, array_agg(farmer_id ORDER BY farmer_id) AS ids
FROM public.farmer GROUP BY date_trunc('second', created_at)
UNION ALL
SELECT 'farm', date_trunc('second', created_at), count(*), array_agg(farm_id ORDER BY farm_id)
FROM public.farm GROUP BY date_trunc('second', created_at)
UNION ALL
SELECT 'crop', date_trunc('second', created_at), count(*), array_agg(crop_id ORDER BY crop_id)
FROM public.crop GROUP BY date_trunc('second', created_at)
UNION ALL
SELECT 'crop_variety', date_trunc('second', created_at), count(*), array_agg(variety_id ORDER BY variety_id)
FROM public.crop_variety GROUP BY date_trunc('second', created_at)
UNION ALL
SELECT 'planting', date_trunc('second', created_at), count(*), array_agg(planting_id ORDER BY planting_id)
FROM public.planting GROUP BY date_trunc('second', created_at)
UNION ALL
SELECT 'harvest', date_trunc('second', created_at), count(*), array_agg(harvest_id ORDER BY harvest_id)
FROM public.harvest GROUP BY date_trunc('second', created_at)
ORDER BY table_name, created_group;

-- Row counts before any proposed deletes. At the verified 2026-11-15 05:21
-- snapshot, expected counts are farmer 18, farm 19, crop 10, crop_variety 10,
-- planting 31, and harvest 22. Farmers without a farm: IDs 25 and 38.
SELECT 'farmer' AS table_name, count(*) AS rows_before FROM public.farmer;
SELECT 'farm' AS table_name, count(*) AS rows_before FROM public.farm;
SELECT 'crop' AS table_name, count(*) AS rows_before FROM public.crop;
SELECT 'crop_variety' AS table_name, count(*) AS rows_before FROM public.crop_variety;
SELECT 'planting' AS table_name, count(*) AS rows_before FROM public.planting;
SELECT 'harvest' AS table_name, count(*) AS rows_before FROM public.harvest;

-- NEEDS DECISION: original seeded farmers 8-22 have invalid legacy phone
-- formats but own original farms/records. Preserve them; edit contacts in app.
SELECT f.farmer_id, f.first_name, f.last_name, f.contact_number,
       (SELECT count(*) FROM public.farm fa WHERE fa.farmer_id = f.farmer_id) AS farm_count
FROM public.farmer f
WHERE f.farmer_id IN (8,9,10,11,12,13,14,15,16,17,18,19,20,21,22)
ORDER BY f.farmer_id;

-- NEEDS DECISION: plausible later farmer entry; no linked farm. Timestamp alone
-- is not enough evidence to classify Sherwin Juntong as junk.
SELECT farmer_id, first_name, last_name, contact_number, address, created_at
FROM public.farmer WHERE farmer_id IN (25);

-- CANDIDATES FOR MY DECISION: repeated Stephen/Stephens and related test-like
-- farmer entries. These are intentionally NOT included in the DELETE below.
-- Review names, contacts, addresses, timestamps, and dependent farm counts.
SELECT f.farmer_id, f.first_name, f.last_name, f.contact_number, f.address,
       f.created_at,
       (SELECT count(*) FROM public.farm fa WHERE fa.farmer_id=f.farmer_id) AS farm_count
FROM public.farmer f
WHERE f.farmer_id IN (24,27,28,29,30,31,32,33,35,45,50)
ORDER BY f.farmer_id;

-- NEEDS DECISION: later farms with plausible names/content and no known test
-- child branch. Keep until confirmed: Ana Alonzo (farm 22), Ben Bautista (24).
SELECT farm_id, farmer_id, farm_name, location, area_hectares, created_at
FROM public.farm WHERE farm_id IN (22,24);

-- NEEDS DECISION: these farms belong to the original created_at batch and have
-- original plantings/harvests, but their current location text looks like a
-- placeholder. Preserve them and verify/correct the location in the app.
SELECT farm_id, farmer_id, farm_name, location, created_at
FROM public.farm WHERE farm_id IN (12,13,18);

-- NEEDS DECISION: extra harvests on original seeded plantings. They were added
-- after the seed batch, but could be real observations; preserve pending review.
SELECT harvest_id, planting_id, harvest_date, quantity_kg,
       selling_price_per_kg, created_at
FROM public.harvest WHERE harvest_id IN (22,23);

-- Exact candidate rows and relationships before deletion.
SELECT f.farmer_id, f.first_name, f.last_name, f.contact_number, f.address,
       (SELECT count(*) FROM public.farm fa WHERE fa.farmer_id=f.farmer_id) AS farm_count
FROM public.farmer f
WHERE f.farmer_id IN (36,37,38,39,40,43)
ORDER BY f.farmer_id;

SELECT fa.farm_id, fa.farmer_id, fa.farm_name, fa.location,
       (SELECT count(*) FROM public.planting p WHERE p.farm_id=fa.farm_id) AS planting_count
FROM public.farm fa WHERE fa.farm_id IN (23,27,28,29,30,31,33,34,35)
ORDER BY fa.farm_id;

SELECT p.planting_id, p.farm_id, p.variety_id, p.planting_date,
       (SELECT count(*) FROM public.harvest h WHERE h.planting_id=p.planting_id) AS harvest_count
FROM public.planting p WHERE p.planting_id IN (33,34,35)
ORDER BY p.planting_id;

-- Delete child rows first. Every ID is explicit and has a reason.
DELETE FROM public.harvest WHERE harvest_id IN (
    24, /* test harvest for Manga/Pinoy planting 33 on fake Kamiya Sama farm */
    25  /* test harvest for Manga/Pinoy planting 35 on fake Kamiya Sama farm */
);

DELETE FROM public.planting WHERE planting_id IN (
    33, /* test planting on fake Kamiya Sama farm using demo Manga/Pinoy variety */
    34, /* later manual test planting on non-original Santiago's Farm; created Nov 11 */
    35  /* test planting on fake Kamiya Sama farm using demo Manga/Pinoy variety */
);

DELETE FROM public.farm WHERE farm_id IN (
    23, /* fake test name Chizuru desu; no planting children */
    27, /* later Santiago's Farm test entry; its test planting 34 is removed above */
    28, /* fake Kamiya Sama farm; test plantings 33/35 and harvests 24/25 removed above */
    29, /* fake Kamiya Sama1 farm; no planting children */
    30, /* fake Kami Sama farm; no planting children */
    31, /* Samantha Farm test entry linked to misspelled test farmer 32 */
    33, /* fake Kamiya Sama farm; no planting children */
    34, /* fake Kamiya Sama farm; no planting children */
    35  /* fake Kamiya Sama farm; no planting children */
);

DELETE FROM public.farmer WHERE farmer_id IN (
    36, /* screenshot-confirmed demo row; invalid contact text, no farm */
    37, /* screenshot-confirmed demo farmer Reynald Senina; no farm */
    38, /* screenshot-confirmed demo farmer Ira Hana Juntong; no farm */
    39, /* screenshot-confirmed demo farmer Santiago Coronel; no farm */
    40, /* screenshot-confirmed demo farmer Sunshine Arias; no farm */
    43  /* screenshot-confirmed demo farmer Adam Garcia; no farm */
);

DELETE FROM public.crop_variety WHERE variety_id IN (
    14, /* test variety Apple belonging to fake crop Manga */
    15, /* test variety Indian belonging to fake crop Manga */
    16, /* test variety Pinoy belonging to fake crop Manga; plantings 33/35 removed */
    17  /* fake Dragon/Fruit variety with unrealistic 25-day growing period */
);

DELETE FROM public.crop WHERE crop_id IN (
    14, /* fake/misspelled crop Manga; all three varieties and test dependents removed */
    17  /* fake crop Dragon; its unrealistic Fruit variety removed above */
);

-- Verified row counts after the cleanup performed through the app (snapshot
-- 2026-11-15 05:21): farmer 18, farm 19, crop 10, crop_variety 10,
-- planting 31, harvest 22. These are not projections from running this
-- historical candidate script. The final ROLLBACK keeps it review-only.
SELECT 'farmer' AS table_name, count(*) AS rows_after FROM public.farmer;
SELECT 'farm' AS table_name, count(*) AS rows_after FROM public.farm;
SELECT 'crop' AS table_name, count(*) AS rows_after FROM public.crop;
SELECT 'crop_variety' AS table_name, count(*) AS rows_after FROM public.crop_variety;
SELECT 'planting' AS table_name, count(*) AS rows_after FROM public.planting;
SELECT 'harvest' AS table_name, count(*) AS rows_after FROM public.harvest;

-- SAFETY DEFAULT: leave ROLLBACK unless all IDs and row counts are approved.
-- Change this final ROLLBACK to COMMIT only after reviewing the results above.
ROLLBACK;
