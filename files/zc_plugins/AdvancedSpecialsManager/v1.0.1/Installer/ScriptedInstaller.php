<?php

declare(strict_types=1);

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    public string $pluginKey = 'AdvancedSpecialsManager';
    public string $version = '1.0.1';

    protected function executeInstall(): bool
    {
        $this->executeInstallerSql(
            "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "advanced_specials_rules (
                rule_id int unsigned NOT NULL AUTO_INCREMENT,
                rule_name varchar(128) NOT NULL,
                rule_status tinyint(1) NOT NULL DEFAULT 1,
                manufacturer_id int unsigned NOT NULL DEFAULT 0,
                category_id int unsigned NOT NULL DEFAULT 0,
                include_subcategories tinyint(1) NOT NULL DEFAULT 1,
                price_min decimal(15,4) DEFAULT NULL,
                price_max decimal(15,4) DEFAULT NULL,
                stock_min decimal(15,4) DEFAULT NULL,
                stock_max decimal(15,4) DEFAULT NULL,
                model_prefix varchar(64) NOT NULL DEFAULT '',
                date_added_from date DEFAULT NULL,
                date_added_to date DEFAULT NULL,
                featured_only tinyint(1) NOT NULL DEFAULT 0,
                active_only tinyint(1) NOT NULL DEFAULT 1,
                include_attribute_priced tinyint(1) NOT NULL DEFAULT 0,
                price_method varchar(16) NOT NULL DEFAULT 'percentage',
                price_value decimal(15,4) NOT NULL DEFAULT 0,
                round_price tinyint(1) NOT NULL DEFAULT 0,
                available_date date DEFAULT NULL,
                expires_date date DEFAULT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (rule_id),
                KEY idx_rule_status (rule_status)
            ) ENGINE=InnoDB"
        );
        $this->executeInstallerSql(
            "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "advanced_specials_rule_products (
                rule_id int unsigned NOT NULL,
                products_id int unsigned NOT NULL,
                specials_id int unsigned NOT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (rule_id, products_id),
                UNIQUE KEY idx_asm_specials_id (specials_id),
                UNIQUE KEY idx_asm_product_owner (products_id)
            ) ENGINE=InnoDB"
        );
        $this->executeInstallerSql(
            "INSERT IGNORE INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function)
             VALUES ('Installed version', 'PLUGIN_ADVANCED_SPECIALS_MANAGER_VERSION', '1.0.1', 'Installed Advanced Specials Manager version.', 0, 0, 'zen_cfg_select_option(array(\'1.0.1\'),')"
        );
        $this->executeInstallerSql(
            "UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '1.0.1', set_function = 'zen_cfg_select_option(array(\'1.0.1\'),' WHERE configuration_key = 'PLUGIN_ADVANCED_SPECIALS_MANAGER_VERSION'"
        );

        zen_deregister_admin_pages(['advancedSpecialsManager']);
        zen_register_admin_page(
            'advancedSpecialsManager',
            'BOX_CATALOG_ADVANCED_SPECIALS_MANAGER',
            'FILENAME_ADVANCED_SPECIALS_MANAGER',
            '',
            'catalog',
            'Y',
            21
        );
        return true;
    }
    protected function executeUpgrade(...$args): bool
    {
        return $this->executeInstall();
    }

    protected function executeUninstall(): bool
    {
        $mappingTable = DB_PREFIX . 'advanced_specials_rule_products';
        $mappingPattern = str_replace(['\\', '_', '%'], ['\\\\', '\\_', '\\%'], $mappingTable);
        $tableCheck = $this->dbConn->Execute("SHOW TABLES LIKE '" . zen_db_input($mappingPattern) . "'");
        if (!$tableCheck->EOF) {
            $this->executeInstallerSql(
                "DELETE s FROM " . TABLE_SPECIALS . " s INNER JOIN $mappingTable rp ON rp.specials_id = s.specials_id AND rp.products_id = s.products_id"
            );
        }
        $this->executeInstallerSql("DROP TABLE IF EXISTS " . DB_PREFIX . "advanced_specials_rule_products");
        $this->executeInstallerSql("DROP TABLE IF EXISTS " . DB_PREFIX . "advanced_specials_rules");
        $this->executeInstallerSql("DELETE FROM " . TABLE_ADMIN_PAGES . " WHERE page_key = 'advancedSpecialsManager'");
        $this->executeInstallerSql("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'PLUGIN_ADVANCED_SPECIALS_MANAGER_VERSION'");
        return true;
    }
}
