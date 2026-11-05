-- Read-only check for the five core tables and whether sample data is present.
SELECT 'farmer' AS table_name, COUNT(*) AS row_count FROM farmer
UNION ALL
SELECT 'farm', COUNT(*) FROM farm
UNION ALL
SELECT 'crop', COUNT(*) FROM crop
UNION ALL
SELECT 'planting', COUNT(*) FROM planting
UNION ALL
SELECT 'harvest', COUNT(*) FROM harvest
ORDER BY table_name;
