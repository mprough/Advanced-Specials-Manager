<?php

declare(strict_types=1);

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

return [
    'pluginVersion' => 'v1.0.1',
    'pluginName' => 'Advanced Specials Manager',
    'pluginDescription' => 'Create native Zen Cart Specials from reusable product selection rules.',
    'pluginAuthor' => 'PRO-Webs.net',
    'pluginId' => 0,
    'zcVersions' => ['v200', 'v210', 'v220'],
    'changelog' => 'https://github.com/mprough/Advanced-Specials-Manager/blob/main/CHANGELOG.md',
    'github_repo' => 'https://github.com/mprough/Advanced-Specials-Manager',
    'pluginGroups' => [],
];
