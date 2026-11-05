-- Add lookup tables and foreign-key columns for the remaining fixed-value fields.
-- Existing text columns are preserved so the migration can be checked before cleanup.

BEGIN;

CREATE TABLE planting_statuses (
    status_id SMALLINT PRIMARY KEY,
    status_name VARCHAR(30) NOT NULL UNIQUE
);

INSERT INTO planting_statuses (status_id, status_name) VALUES
    (1, 'Harvested'),
    (2, 'Growing');

CREATE TABLE harvest_quality_grades (
    quality_grade_id SMALLINT PRIMARY KEY,
    quality_grade_name VARCHAR(30) NOT NULL UNIQUE
);

INSERT INTO harvest_quality_grades (quality_grade_id, quality_grade_name) VALUES
    (1, 'Grade A'),
    (2, 'Grade B'),
    (3, 'Grade C');

CREATE TABLE irrigation_types (
    irrigation_type_id SMALLINT PRIMARY KEY,
    irrigation_type_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO irrigation_types (irrigation_type_id, irrigation_type_name) VALUES
    (1, 'Rainfed'),
    (2, 'Irrigated'),
    (3, 'Drip Irrigation');

CREATE TABLE soil_types (
    soil_type_id SMALLINT PRIMARY KEY,
    soil_type_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO soil_types (soil_type_id, soil_type_name) VALUES
    (1, 'Loam'),
    (2, 'Clay Loam'),
    (3, 'Sandy Loam'),
    (4, 'Clay');

CREATE TABLE crop_categories (
    crop_category_id SMALLINT PRIMARY KEY,
    crop_category_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO crop_categories (crop_category_id, crop_category_name) VALUES
    (1, 'Cereal'),
    (2, 'Vegetable'),
    (3, 'Fruit'),
    (4, 'Legume'),
    (5, 'Root Crop');

CREATE TABLE recommended_seasons (
    recommended_season_id SMALLINT PRIMARY KEY,
    recommended_season_name VARCHAR(30) NOT NULL UNIQUE
);

INSERT INTO recommended_seasons (recommended_season_id, recommended_season_name) VALUES
    (1, 'Wet Season'),
    (2, 'Dry Season'),
    (3, 'Year Round');

ALTER TABLE planting ADD COLUMN status_id SMALLINT;
ALTER TABLE harvest ADD COLUMN quality_grade_id SMALLINT;
ALTER TABLE farm ADD COLUMN irrigation_type_id SMALLINT;
ALTER TABLE farm ADD COLUMN soil_type_id SMALLINT;
ALTER TABLE crop ADD COLUMN crop_category_id SMALLINT;
ALTER TABLE crop ADD COLUMN recommended_season_id SMALLINT;

UPDATE planting AS p
SET status_id = l.status_id
FROM planting_statuses AS l
WHERE p.status = l.status_name;

UPDATE harvest AS h
SET quality_grade_id = l.quality_grade_id
FROM harvest_quality_grades AS l
WHERE h.quality_grade = l.quality_grade_name;

UPDATE farm AS f
SET irrigation_type_id = l.irrigation_type_id
FROM irrigation_types AS l
WHERE f.irrigation_type = l.irrigation_type_name;

UPDATE farm AS f
SET soil_type_id = l.soil_type_id
FROM soil_types AS l
WHERE f.soil_type = l.soil_type_name;

UPDATE crop AS c
SET crop_category_id = l.crop_category_id
FROM crop_categories AS l
WHERE c.crop_category = l.crop_category_name;

UPDATE crop AS c
SET recommended_season_id = l.recommended_season_id
FROM recommended_seasons AS l
WHERE c.recommended_season = l.recommended_season_name;

ALTER TABLE planting
    ADD CONSTRAINT planting_status_id_fkey
    FOREIGN KEY (status_id) REFERENCES planting_statuses (status_id);

ALTER TABLE harvest
    ADD CONSTRAINT harvest_quality_grade_id_fkey
    FOREIGN KEY (quality_grade_id) REFERENCES harvest_quality_grades (quality_grade_id);

ALTER TABLE farm
    ADD CONSTRAINT farm_irrigation_type_id_fkey
    FOREIGN KEY (irrigation_type_id) REFERENCES irrigation_types (irrigation_type_id),
    ADD CONSTRAINT farm_soil_type_id_fkey
    FOREIGN KEY (soil_type_id) REFERENCES soil_types (soil_type_id);

ALTER TABLE crop
    ADD CONSTRAINT crop_category_id_fkey
    FOREIGN KEY (crop_category_id) REFERENCES crop_categories (crop_category_id),
    ADD CONSTRAINT crop_recommended_season_id_fkey
    FOREIGN KEY (recommended_season_id) REFERENCES recommended_seasons (recommended_season_id);

COMMIT;
