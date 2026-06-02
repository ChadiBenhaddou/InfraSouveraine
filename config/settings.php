<?php

return [
    'storage_cost_per_gb_monthly' => (float) env('STORAGE_COST_PER_GB_MONTHLY', 0.10),
    'default_benefit_margin' => (float) env('DEFAULT_BENEFIT_MARGIN', 0.35),
    'fixed_platform_markup' => (float) env('FIXED_PLATFORM_MARKUP', 9.99),
    'hours_per_month' => 730,
    'default_container_disk_gb' => 50,
    'testing_mode' => (bool) env('APP_TESTING_MODE', false),
];
