<?php

use App\Enums\UserRole;

/**
 * Default ability mappings per role.
 * Keys are the role string values (not enum names).
 */
return [
    UserRole::ADMIN->value => [
        'admin:read',
        'admin:write',
        'admin:ping',
    ],

    UserRole::MANAGER->value => [
        'admin:read',
    ],

    UserRole::VIEWER->value => [],
];
