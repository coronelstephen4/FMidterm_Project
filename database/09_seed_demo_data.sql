-- Adds fictional sample records to an empty database.
-- This script is additive and designed to avoid duplicate sample rows on reruns.
-- Run only after lookup migration 08 has completed.

BEGIN;

INSERT INTO public.farmer (first_name, last_name, contact_number, address, date_registered)
SELECT d.first_name, d.last_name, d.contact_number, d.address, d.date_registered
FROM (VALUES
    ('Ana', 'Alonzo', '09990001001', 'AIS Address 01, Cebu', DATE '2024-01-08'),
    ('Ben', 'Bautista', '09990001002', 'AIS Address 02, Cebu', DATE '2024-01-12'),
    ('Carla', 'Cruz', '09990001003', 'AIS Address 03, Cebu', DATE '2024-01-16'),
    ('Daniel', 'Dela Vega', '09990001004', 'AIS Address 04, Cebu', DATE '2024-01-20'),
    ('Elena', 'Enriquez', '09990001005', 'AIS Address 05, Cebu', DATE '2024-01-24'),
    ('Felix', 'Flores', '09990001006', 'AIS Address 06, Cebu', DATE '2024-01-28'),
    ('Grace', 'Garcia', '09990001007', 'AIS Address 07, Cebu', DATE '2024-02-01'),
    ('Hector', 'Hernandez', '09990001008', 'AIS Address 08, Cebu', DATE '2024-02-05'),
    ('Iris', 'Ignacio', '09990001009', 'AIS Address 09, Cebu', DATE '2024-02-09'),
    ('Jose', 'Jimenez', '09990001010', 'AIS Address 10, Cebu', DATE '2024-02-13'),
    ('Karla', 'Luna', '09990001011', 'AIS Address 11, Cebu', DATE '2024-02-17'),
    ('Luis', 'Mendoza', '09990001012', 'AIS Address 12, Cebu', DATE '2024-02-21'),
    ('Maya', 'Navarro', '09990001013', 'AIS Address 13, Cebu', DATE '2024-02-25'),
    ('Noel', 'Ortega', '09990001014', 'AIS Address 14, Cebu', DATE '2024-03-01'),
    ('Olivia', 'Perez', '09990001015', 'AIS Address 15, Cebu', DATE '2024-03-05')
) AS d(first_name, last_name, contact_number, address, date_registered)
WHERE NOT EXISTS (
    SELECT 1 FROM public.farmer f
    WHERE f.contact_number = d.contact_number
       OR (f.first_name = d.first_name AND f.last_name = d.last_name AND f.address = d.address)
);

WITH demo_farmers AS (
    SELECT f.farmer_id, f.first_name, f.last_name,
           ROW_NUMBER() OVER (ORDER BY f.contact_number)::INTEGER AS demo_number
    FROM public.farmer f
    WHERE f.address LIKE 'AIS Address %'
)
INSERT INTO public.farm (farmer_id, farm_name, location, area_hectares, soil_type_id, irrigation_type_id)
SELECT df.farmer_id,
       'Farm ' || LPAD(df.demo_number::TEXT, 2, '0') || ' - ' || df.first_name || ' ' || df.last_name,
       'Farm Location ' || LPAD(df.demo_number::TEXT, 2, '0') || ', Cebu',
       (1.25 + df.demo_number * 0.35)::NUMERIC(8,2),
       ((df.demo_number - 1) % 4 + 1)::SMALLINT,
       ((df.demo_number - 1) % 3 + 1)::SMALLINT
FROM demo_farmers df
WHERE NOT EXISTS (
    SELECT 1 FROM public.farm existing
    WHERE existing.farmer_id = df.farmer_id
      AND existing.farm_name = 'Farm ' || LPAD(df.demo_number::TEXT, 2, '0') || ' - ' || df.first_name || ' ' || df.last_name
);

INSERT INTO public.crop (crop_name, variety, crop_category_id, growing_period_days, recommended_season_id)
SELECT d.crop_name, d.variety, d.category_id, d.growing_period_days, d.season_id
FROM (VALUES
    ('Rice', 'PSB Rc 18', 1, 120, 1),
    ('Corn', 'Yellow Hybrid', 1, 100, 1),
    ('Tomato', 'Diamante Max', 2, 75, 1),
    ('Eggplant', 'Casino', 2, 85, 1),
    ('Banana', 'Lakatan', 3, 300, 3),
    ('Mango', 'Carabao', 3, 150, 2),
    ('Peanut', 'BPI Pn 9', 4, 100, 1),
    ('Cassava', 'Golden Yellow', 5, 270, 3),
    ('Sweet Potato', 'NSIC Sp 30', 5, 110, 1),
    ('Watermelon', 'Sugar Baby', 3, 90, 1)
) AS d(crop_name, variety, category_id, growing_period_days, season_id)
WHERE NOT EXISTS (
    SELECT 1 FROM public.crop c
    WHERE c.crop_name = d.crop_name AND c.variety = d.variety
);

WITH demo_farms AS (
    SELECT f.farm_id,
           ROW_NUMBER() OVER (ORDER BY fm.contact_number)::INTEGER AS demo_number
    FROM public.farm f
    INNER JOIN public.farmer fm ON fm.farmer_id = f.farmer_id
    WHERE fm.address LIKE 'AIS Address %'
), planting_values AS (
    SELECT n AS demo_number,
           ((n - 1) % 15 + 1)::INTEGER AS farm_number,
           ((n - 1) % 10 + 1)::INTEGER AS crop_number,
           CASE WHEN n <= 20
                THEN DATE '2025-01-01' + ((n - 1) * 12)
                ELSE DATE '2026-08-01' + ((n - 21) * 5)
           END AS planting_date,
           CASE WHEN n <= 20 THEN 1 ELSE 2 END::SMALLINT AS status_id
    FROM generate_series(1, 30) AS series(n)
), demo_crops AS (
    SELECT c.crop_id, c.crop_name, c.variety,
           ROW_NUMBER() OVER (ORDER BY c.crop_name, c.variety)::INTEGER AS crop_number
    FROM public.crop c
    WHERE (c.crop_name, c.variety) IN (
        ('Rice', 'PSB Rc 18'), ('Corn', 'Yellow Hybrid'), ('Tomato', 'Diamante Max'),
        ('Eggplant', 'Casino'), ('Banana', 'Lakatan'), ('Mango', 'Carabao'),
        ('Peanut', 'BPI Pn 9'), ('Cassava', 'Golden Yellow'),
        ('Sweet Potato', 'NSIC Sp 30'), ('Watermelon', 'Sugar Baby')
    )
), ranked_farms AS (
    SELECT f.farm_id,
           ROW_NUMBER() OVER (ORDER BY fm.contact_number)::INTEGER AS farm_number
    FROM public.farm f
    INNER JOIN public.farmer fm ON fm.farmer_id = f.farmer_id
    WHERE fm.address LIKE 'AIS Address %'
)
INSERT INTO public.planting (
    farm_id, crop_id, planting_date, area_planted, seed_quantity,
    expected_harvest_date, status_id
)
SELECT f.farm_id, c.crop_id, v.planting_date,
       (0.25 + ((v.demo_number - 1) % 10) * 0.10)::NUMERIC(8,2),
       (12.00 + v.demo_number)::NUMERIC(8,2),
       v.planting_date + CASE WHEN v.demo_number <= 20 THEN 120 ELSE 110 END,
       v.status_id
FROM planting_values v
INNER JOIN ranked_farms f ON f.farm_number = v.farm_number
INNER JOIN demo_crops c ON c.crop_number = v.crop_number
WHERE NOT EXISTS (
    SELECT 1 FROM public.planting p
    WHERE p.farm_id = f.farm_id
      AND p.crop_id = c.crop_id
      AND p.planting_date = v.planting_date
);

WITH demo_plantings AS (
    SELECT p.planting_id, p.expected_harvest_date,
           ROW_NUMBER() OVER (ORDER BY p.planting_date, p.planting_id)::INTEGER AS demo_number
    FROM public.planting p
    INNER JOIN public.farm f ON f.farm_id = p.farm_id
    INNER JOIN public.farmer fm ON fm.farmer_id = f.farmer_id
    WHERE fm.address LIKE 'AIS Address %'
      AND p.status_id = 1
), harvest_values AS (
    SELECT planting_id, expected_harvest_date, demo_number
    FROM demo_plantings
    WHERE demo_number <= 20
)
INSERT INTO public.harvest (
    planting_id, harvest_date, quantity_kg, selling_price_per_kg, quality_grade_id, remark_id
)
SELECT v.planting_id, v.expected_harvest_date,
       (55.00 + v.demo_number * 7.50)::NUMERIC(10,2),
       (28.00 + v.demo_number * 1.25)::NUMERIC(10,2),
       ((v.demo_number - 1) % 3 + 1)::SMALLINT,
       ((v.demo_number - 1) % 5 + 1)::INTEGER
FROM harvest_values v
WHERE NOT EXISTS (
    SELECT 1 FROM public.harvest h
    WHERE h.planting_id = v.planting_id
      AND h.harvest_date = v.expected_harvest_date
);

COMMIT;

SELECT 'farmer' AS table_name, COUNT(*) AS sample_rows FROM public.farmer WHERE address LIKE 'AIS Address %'
UNION ALL
SELECT 'farm', COUNT(*) FROM public.farm f INNER JOIN public.farmer fm ON fm.farmer_id = f.farmer_id WHERE fm.address LIKE 'AIS Address %'
UNION ALL
SELECT 'crop', COUNT(*) FROM public.crop WHERE (crop_name, variety) IN (
    ('Rice', 'PSB Rc 18'), ('Corn', 'Yellow Hybrid'), ('Tomato', 'Diamante Max'),
    ('Eggplant', 'Casino'), ('Banana', 'Lakatan'), ('Mango', 'Carabao'),
    ('Peanut', 'BPI Pn 9'), ('Cassava', 'Golden Yellow'),
    ('Sweet Potato', 'NSIC Sp 30'), ('Watermelon', 'Sugar Baby')
)
UNION ALL
SELECT 'planting', COUNT(*) FROM public.planting p INNER JOIN public.farm f ON f.farm_id = p.farm_id INNER JOIN public.farmer fm ON fm.farmer_id = f.farmer_id WHERE fm.address LIKE 'AIS Address %'
UNION ALL
SELECT 'harvest', COUNT(*) FROM public.harvest h INNER JOIN public.planting p ON p.planting_id = h.planting_id INNER JOIN public.farm f ON f.farm_id = p.farm_id INNER JOIN public.farmer fm ON fm.farmer_id = f.farmer_id WHERE fm.address LIKE 'AIS Address %';
