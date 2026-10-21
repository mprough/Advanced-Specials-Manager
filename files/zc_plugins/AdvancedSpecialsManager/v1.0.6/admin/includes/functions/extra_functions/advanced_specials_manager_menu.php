<?php

declare(strict_types=1);

if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    die('Illegal Access');
}

if (!defined('FILENAME_ADVANCED_SPECIALS_MANAGER')) {
    define('FILENAME_ADVANCED_SPECIALS_MANAGER', 'advanced_specials_manager');
}
if (!defined('BOX_CATALOG_ADVANCED_SPECIALS_MANAGER')) {
    define('BOX_CATALOG_ADVANCED_SPECIALS_MANAGER', 'Advanced Specials Manager');
}

$asmInstalled = defined('PLUGIN_ADVANCED_SPECIALS_MANAGER_VERSION');
if (!$asmInstalled && isset($db)) {
    $asmVersion = $db->Execute(
        "SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'PLUGIN_ADVANCED_SPECIALS_MANAGER_VERSION' LIMIT 1"
    );
    $asmInstalled = !$asmVersion->EOF;
}

if (
    function_exists('zen_register_admin_page')
    && function_exists('zen_page_key_exists')
    && $asmInstalled
    && !zen_page_key_exists('advancedSpecialsManager')
) {
    zen_register_admin_page(
        'advancedSpecialsManager',
        'BOX_CATALOG_ADVANCED_SPECIALS_MANAGER',
        'FILENAME_ADVANCED_SPECIALS_MANAGER',
        '',
        'catalog',
        'Y',
        21
    );
}

unset($asmInstalled, $asmVersion);
