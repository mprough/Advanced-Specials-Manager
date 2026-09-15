<?php

declare(strict_types=1);

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class zcObserverAdvancedSpecialsManager extends base
{
    public function __construct()
    {
        if (!defined('PLUGIN_ADVANCED_SPECIALS_MANAGER_VERSION')) {
            return;
        }
        $this->attach($this, ['NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_CREATE_ADD_PRODUCTS']);
    }

    public function updateNotifyCheckoutProcessAfterOrderCreateAddProducts(&$class, $eventId, $orderId): void
    {
        global $db;

        $orderId = (int)$orderId;
        if ($orderId < 1) {
            return;
        }

        $rulesTable = DB_PREFIX . 'advanced_specials_rules';
        $mappingTable = DB_PREFIX . 'advanced_specials_rule_products';
        $ordersTable = DB_PREFIX . 'advanced_specials_rule_orders';
        $totals = $db->Execute(
            "SELECT r.rule_id, SUM(op.products_quantity) AS sold_now
               FROM " . TABLE_ORDERS_PRODUCTS . " op
               INNER JOIN $mappingTable rp ON rp.products_id = op.products_id
               INNER JOIN $rulesTable r ON r.rule_id = rp.rule_id
               INNER JOIN " . TABLE_SPECIALS . " s ON s.specials_id = rp.specials_id AND s.products_id = rp.products_id
              WHERE op.orders_id = $orderId
                AND r.rule_status = 1
                AND r.sales_limit > 0
                AND s.status = 1
              GROUP BY r.rule_id"
        );

        while (!$totals->EOF) {
            $ruleId = (int)$totals->fields['rule_id'];
            $soldNow = max(0, (int)$totals->fields['sold_now']);
            if ($soldNow > 0) {
                $db->Execute(
                    "INSERT IGNORE INTO $ordersTable (rule_id, orders_id, quantity, created_at) VALUES ($ruleId, $orderId, $soldNow, NOW())"
                );
                if ($db->affectedRows() < 1) {
                    $totals->MoveNext();
                    continue;
                }
                $db->Execute(
                    "UPDATE $rulesTable SET sold_quantity = sold_quantity + $soldNow, sales_started_at = COALESCE(sales_started_at, NOW()), updated_at = NOW() WHERE rule_id = $ruleId AND rule_status = 1"
                );
                $rule = $db->Execute("SELECT sales_limit, sold_quantity FROM $rulesTable WHERE rule_id = $ruleId LIMIT 1");
                if (!$rule->EOF && (int)$rule->fields['sold_quantity'] >= (int)$rule->fields['sales_limit']) {
                    $db->Execute("UPDATE $rulesTable SET rule_status = 0, ended_reason = 'quantity', updated_at = NOW() WHERE rule_id = $ruleId");
                    $db->Execute(
                        "UPDATE " . TABLE_SPECIALS . " s INNER JOIN $mappingTable rp ON rp.specials_id = s.specials_id AND rp.products_id = s.products_id SET s.status = 0, s.date_status_change = NOW() WHERE rp.rule_id = $ruleId"
                    );
                }
            }
            $totals->MoveNext();
        }
    }
}
