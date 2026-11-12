-- Read-only audit of the 13 required Agriculture Information System relations.
-- Run this in the Supabase SQL Editor and review every row before claiming
-- that all tables contain data. This script does not insert, update, or delete.

SELECT 'farmer' AS table_name, COUNT(*)::BIGINT AS row_count,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END AS data_status
FROM public.farmer
UNION ALL
SELECT 'farm', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.farm
UNION ALL
SELECT 'crop', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.crop
UNION ALL
SELECT 'planting', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.planting
UNION ALL
SELECT 'harvest', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.harvest
UNION ALL
SELECT 'soil_types', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.soil_types
UNION ALL
SELECT 'irrigation_types', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.irrigation_types
UNION ALL
SELECT 'crop_categories', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.crop_categories
UNION ALL
SELECT 'recommended_seasons', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.recommended_seasons
UNION ALL
SELECT 'planting_statuses', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.planting_statuses
UNION ALL
SELECT 'harvest_quality_grades', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.harvest_quality_grades
UNION ALL
SELECT 'harvest_remarks', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.harvest_remarks
UNION ALL
SELECT 'users', COUNT(*)::BIGINT,
       CASE WHEN COUNT(*) > 0 THEN 'HAS DATA' ELSE 'EMPTY - ACTION REQUIRED' END
FROM public.users
ORDER BY table_name;
