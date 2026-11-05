-- WARNING: historical one-time migration. It drops harvest.remarks after mapping.
-- The user confirmed this migration was already applied in Supabase. Do not rerun it.

BEGIN;

CREATE TABLE harvest_remarks (
    remark_id INTEGER PRIMARY KEY,
    remark_description VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO harvest_remarks (remark_id, remark_description) VALUES
    (1, 'Good Harvest'),
    (2, 'Acceptable Quality'),
    (3, 'Low Yield'),
    (4, 'Poor Quality'),
    (5, 'Damaged Harvest');

ALTER TABLE harvest ADD COLUMN remark_id INTEGER;

UPDATE harvest
SET remark_id = CASE LOWER(BTRIM(remarks))
    WHEN 'high quality harvest' THEN 1
    WHEN 'good harvest' THEN 1
    WHEN 'acceptable quality' THEN 2
    ELSE NULL
END;

DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM harvest
        WHERE remarks IS NOT NULL AND remark_id IS NULL
    ) THEN
        RAISE EXCEPTION 'Unmapped harvest.remarks values remain; do not drop the old column.';
    END IF;
END;
$$;

ALTER TABLE harvest
    ADD CONSTRAINT harvest_remark_id_fkey
    FOREIGN KEY (remark_id) REFERENCES harvest_remarks (remark_id);

ALTER TABLE harvest DROP COLUMN remarks;

COMMIT;
