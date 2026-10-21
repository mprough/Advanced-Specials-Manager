<?php

declare(strict_types=1);

if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    die('Illegal Access');
}

if (!defined('FILENAME_ADVANCED_SPECIALS_MANAGER')) {
    define('FILENAME_ADVANCED_SPECIALS_MANAGER', 'advanced_specials_manager');
}
