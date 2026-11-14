<?php
function duplicateDatabaseMessage(Throwable $error): ?string {
    if (!($error instanceof PDOException)
        || ($error->errorInfo[0] ?? $error->getCode()) !== '23505') {
        return null;
    }

    $databaseMessage = (string) ($error->errorInfo[2] ?? '') . ' ' . $error->getMessage();
    $normalizedDatabaseMessage = strtolower($databaseMessage);

    // The legacy Farmer trigger raises SQLSTATE 23505 with a custom message,
    // but PDO may omit its constraint name from errorInfo.
    if (str_contains($normalizedDatabaseMessage, 'matching contact/address or name/address')) {
        return 'A farmer with the same contact number and address, or the same first name, last name, and address, is already registered.';
    }

    if (str_contains($normalizedDatabaseMessage, 'farmer with the same name and address already exists')) {
        return 'A farmer with the same first name, last name, and address is already registered.';
    }

    if (str_contains($normalizedDatabaseMessage, 'this crop name already exists')) {
        return 'This crop name is already registered.';
    }

    if (str_contains($normalizedDatabaseMessage, 'this crop and variety already exist')) {
        return 'This variety name is already registered for this crop.';
    }

    $constraint = '';
    if (preg_match('/(?:constraint|index) "([^"]+)"/i', $databaseMessage, $matches)) {
        $constraint = strtolower($matches[1]);
    }

    $messages = [
        'farmer_pkey' => 'This farmer ID is already in use.',
        'farm_pkey' => 'This farm ID is already in use.',
        'crop_pkey' => 'This crop ID is already in use.',
        'crop_variety_pkey' => 'This crop variety ID is already in use.',
        'planting_pkey' => 'This planting ID is already in use.',
        'harvest_pkey' => 'This harvest ID is already in use.',
        'users_pkey' => 'This user ID is already in use.',
        'roles_pkey' => 'This role ID is already in use.',
        'planting_statuses_pkey' => 'This planting status ID is already in use.',
        'harvest_quality_grades_pkey' => 'This quality grade ID is already in use.',
        'irrigation_types_pkey' => 'This irrigation type ID is already in use.',
        'soil_types_pkey' => 'This soil type ID is already in use.',
        'crop_categories_pkey' => 'This crop category ID is already in use.',
        'recommended_seasons_pkey' => 'This season ID is already in use.',
        'harvest_remarks_pkey' => 'This harvest remark ID is already in use.',
        'users_username_key' => 'This username is already taken.',
        'roles_role_name_key' => 'This role name is already in use.',
        'soil_types_soil_type_name_key' => 'This soil type name is already in use.',
        'irrigation_types_irrigation_type_name_key' => 'This irrigation type name is already in use.',
        'crop_categories_crop_category_name_key' => 'This crop category name is already in use.',
        'recommended_seasons_recommended_season_name_key' => 'This recommended season name is already in use.',
        'planting_statuses_status_name_key' => 'This planting status name is already in use.',
        'harvest_quality_grades_quality_grade_name_key' => 'This quality grade name is already in use.',
        'harvest_remarks_remark_description_key' => 'This harvest remark is already in use.',
        'crop_name_normalized_unique' => 'This crop name is already registered.',
        'crop_crop_name_key' => 'This crop name is already registered.',
        'crop_name_unique' => 'This crop name is already registered.',
        'crop_duplicate_guard' => 'This crop name is already registered.',
        'crop_variety_name_normalized_unique' => 'This variety name is already registered for this crop.',
        'crop_variety_crop_id_variety_name_key' => 'This variety name is already registered for this crop.',
        'farm_farmer_name_unique' => 'This farmer already has a farm with that name.',
        'crop_name_variety_unique' => 'This crop and variety are already registered.',
        'farmer_duplicate_guard' => 'A farmer with matching details is already registered.',
        'farm_duplicate_guard' => 'This farmer already has a farm with that name.',
        'planting_duplicate_guard' => 'An identical planting record is already registered.',
        'harvest_duplicate_guard' => 'An identical harvest record is already registered.'
    ];

    return $messages[$constraint] ?? 'A value that must be unique is already in use.';
}
