<?php

$locationCount = max(1, min(1000, (int) env('ROBOT_ARM_LOCATION_COUNT', 5)));

return [
    'enabled' => env('ROBOT_ARM_ENABLED', false),
    'base_url' => rtrim((string) env('ROBOT_ARM_BASE_URL', ''), '/'),
    'command_endpoint' => env('ROBOT_ARM_COMMAND_ENDPOINT', '/robot/command'),
    'timeout' => max(1, (int) env('ROBOT_ARM_TIMEOUT', 5)),
    'locations' => range(1, $locationCount),
];
