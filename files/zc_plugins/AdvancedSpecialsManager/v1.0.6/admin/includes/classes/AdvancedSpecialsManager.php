<?php

declare(strict_types=1);

if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    die('Illegal Access');
}

class AdvancedSpecialsManager
{
    private object $db;
    private string $rulesTable;
    private string $mappingTable;
    private string $ordersTable;

    public function __construct(object $db)
    {
        $this->db = $db;
        $this->rulesTable = DB_PREFIX . 'advanced_specials_rules';
        $this->mappingTable = DB_PREFIX . 'advanced_specials_rule_products';
        $this->ordersTable = DB_PREFIX . 'advanced_specials_rule_orders';
    }

    public function getRules(): array
    {
        return $this->rows($this->db->Execute(
            "SELECT r.*, COUNT(rp.products_id) AS generated_count
               FROM {$this->rulesTable} r
               LEFT JOIN {$this->mappingTable} rp ON rp.rule_id = r.rule_id
              GROUP BY r.rule_id
              ORDER BY r.rule_name, r.rule_id"
        ));
    }

    public function getRule(int $ruleId): ?array
    {
        $result = $this->db->Execute("SELECT * FROM {$this->rulesTable} WHERE rule_id = $ruleId LIMIT 1");
        return $result->EOF ? null : $result->fields;
    }

    public function saveRule(array $input): int
    {
        $ruleId = max(0, (int)($input['rule_id'] ?? 0));
        $name = trim((string)($input['rule_name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('A rule name is required.');
        }

        $method = (string)($input['price_method'] ?? 'percentage');
        if (!in_array($method, ['percentage', 'amount', 'set'], true)) {
            throw new InvalidArgumentException('Select a valid pricing method.');
        }
        $value = $this->decimal($input['price_value'] ?? null, false);
        if ($value === null || $value <= 0) {
            throw new InvalidArgumentException('The pricing value must be greater than zero.');
        }
        if ($method === 'percentage' && $value >= 100) {
            throw new InvalidArgumentException('A percentage discount must be less than 100.');
        }

        $available = $this->date($input['available_date'] ?? null);
        $expires = $this->date($input['expires_date'] ?? null);
        if ($available !== null && $expires !== null && $expires < $available) {
            throw new InvalidArgumentException('The expiration date cannot be earlier than the available date.');
        }

        $data = [
            'rule_name' => $name,
            'rule_status' => isset($input['rule_status']) ? 1 : 0,
            'manufacturer_id' => max(0, (int)($input['manufacturer_id'] ?? 0)),
            'category_id' => max(0, (int)($input['category_id'] ?? 0)),
            'include_subcategories' => isset($input['include_subcategories']) ? 1 : 0,
            'price_min' => $this->decimal($input['price_min'] ?? null),
            'price_max' => $this->decimal($input['price_max'] ?? null),
            'stock_min' => $this->decimal($input['stock_min'] ?? null),
            'stock_max' => $this->decimal($input['stock_max'] ?? null),
            'model_prefix' => trim((string)($input['model_prefix'] ?? '')),
            'date_added_from' => $this->date($input['date_added_from'] ?? null),
            'date_added_to' => $this->date($input['date_added_to'] ?? null),
            'featured_only' => isset($input['featured_only']) ? 1 : 0,
            'active_only' => isset($input['active_only']) ? 1 : 0,
            'include_attribute_priced' => isset($input['include_attribute_priced']) ? 1 : 0,
            'price_method' => $method,
            'price_value' => $value,
            'round_price' => isset($input['round_price']) ? 1 : 0,
            'available_date' => $available,
            'expires_date' => $expires,
            'sales_limit' => max(0, (int)($input['sales_limit'] ?? 0)),
        ];

        $assignments = [];
        foreach ($data as $column => $fieldValue) {
            $assignments[] = $column . ' = ' . $this->sqlValue($fieldValue);
        }
        $assignments[] = 'updated_at = NOW()';

        if ($ruleId > 0 && $this->getRule($ruleId) !== null) {
            $this->db->Execute("UPDATE {$this->rulesTable} SET " . implode(', ', $assignments) . " WHERE rule_id = $ruleId");
            if (isset($input['reset_sold_quantity'])) {
                $this->db->Execute("UPDATE {$this->rulesTable} SET sold_quantity = 0, sales_started_at = NULL, ended_reason = '' WHERE rule_id = $ruleId");
                $this->db->Execute("DELETE FROM {$this->ordersTable} WHERE rule_id = $ruleId");
            }
            return $ruleId;
        }

        $assignments[] = 'created_at = NOW()';
        $this->db->Execute("INSERT INTO {$this->rulesTable} SET " . implode(', ', $assignments));
        return (int)$this->db->Insert_ID();
    }

    public function preview(int $ruleId): array
    {
        $rule = $this->requireRule($ruleId);
        $products = $this->matchingProducts($rule);
        foreach ($products as &$product) {
            $product['calculated_price'] = $this->calculatePrice($rule, (float)$product['base_price']);
            $product['result'] = $this->resultForProduct($ruleId, $product);
        }
        unset($product);
        return $products;
    }

    public function apply(int $ruleId): array
    {
        $rule = $this->requireRule($ruleId);
        if ((int)$rule['rule_status'] !== 1) {
            return $this->disableOwnedSpecials($ruleId);
        }
        if ((int)$rule['sales_limit'] > 0 && (int)$rule['sold_quantity'] >= (int)$rule['sales_limit']) {
            $this->db->Execute("UPDATE {$this->rulesTable} SET rule_status = 0, ended_reason = 'quantity', updated_at = NOW() WHERE rule_id = $ruleId");
            return $this->disableOwnedSpecials($ruleId);
        }

        $products = $this->preview($ruleId);
        $matchedIds = array_map(static fn(array $product): int => (int)$product['products_id'], $products);
        $summary = ['created' => 0, 'updated' => 0, 'removed' => 0, 'conflicts' => 0, 'invalid' => 0, 'disabled' => 0];

        $owned = $this->rows($this->db->Execute("SELECT * FROM {$this->mappingTable} WHERE rule_id = $ruleId"));
        foreach ($owned as $mapping) {
            if (!in_array((int)$mapping['products_id'], $matchedIds, true)) {
                $summary['removed'] += $this->removeOwnedSpecial($mapping) ? 1 : 0;
            }
        }

        $available = $this->specialDate($rule['available_date'] ?? null);
        $expires = $this->specialDate($rule['expires_date'] ?? null);
        foreach ($products as $product) {
            $productId = (int)$product['products_id'];
            $price = $product['calculated_price'];
            if ($price === null) {
                $summary['invalid']++;
                continue;
            }
            if ($product['result'] === 'Manual Special conflict' || $product['result'] === 'Owned by another rule') {
                $summary['conflicts']++;
                continue;
            }

            $mapping = $this->db->Execute(
                "SELECT * FROM {$this->mappingTable} WHERE rule_id = $ruleId AND products_id = $productId LIMIT 1"
            );
            if (!$mapping->EOF) {
                $specialsId = (int)$mapping->fields['specials_id'];
                $this->db->Execute(
                    "UPDATE " . TABLE_SPECIALS . " SET specials_new_products_price = " . $this->sqlValue($price) . ", specials_last_modified = NOW(), expires_date = '$expires', specials_date_available = '$available', status = 1 WHERE specials_id = $specialsId AND products_id = $productId"
                );
                $this->db->Execute("UPDATE {$this->mappingTable} SET updated_at = NOW() WHERE rule_id = $ruleId AND products_id = $productId");
                $summary['updated']++;
                continue;
            }

            $existing = $this->db->Execute("SELECT specials_id FROM " . TABLE_SPECIALS . " WHERE products_id = $productId LIMIT 1");
            if (!$existing->EOF) {
                $summary['conflicts']++;
                continue;
            }
            $this->db->Execute(
                "INSERT INTO " . TABLE_SPECIALS . " (products_id, specials_new_products_price, specials_date_added, expires_date, status, specials_date_available) VALUES ($productId, " . $this->sqlValue($price) . ", NOW(), '$expires', 1, '$available')"
            );
            $specialsId = (int)$this->db->Insert_ID();
            $this->db->Execute(
                "INSERT INTO {$this->mappingTable} (rule_id, products_id, specials_id, created_at, updated_at) VALUES ($ruleId, $productId, $specialsId, NOW(), NOW())"
            );
            $summary['created']++;
        }
        if ((int)$rule['sales_limit'] > 0 && empty($rule['sales_started_at'])) {
            $this->db->Execute("UPDATE {$this->rulesTable} SET sales_started_at = NOW(), ended_reason = '' WHERE rule_id = $ruleId");
        }
        return $summary;
    }

    public function deleteRule(int $ruleId): int
    {
        $removed = 0;
        $owned = $this->rows($this->db->Execute("SELECT * FROM {$this->mappingTable} WHERE rule_id = $ruleId"));
        foreach ($owned as $mapping) {
            $removed += $this->removeOwnedSpecial($mapping) ? 1 : 0;
        }
        $this->db->Execute("DELETE FROM {$this->ordersTable} WHERE rule_id = $ruleId");
        $this->db->Execute("DELETE FROM {$this->rulesTable} WHERE rule_id = $ruleId");
        return $removed;
    }

    private function disableOwnedSpecials(int $ruleId): array
    {
        $this->db->Execute(
            "UPDATE " . TABLE_SPECIALS . " s INNER JOIN {$this->mappingTable} rp ON rp.specials_id = s.specials_id AND rp.products_id = s.products_id SET s.status = 0, s.date_status_change = NOW() WHERE rp.rule_id = $ruleId"
        );
        return ['created' => 0, 'updated' => 0, 'removed' => 0, 'conflicts' => 0, 'invalid' => 0, 'disabled' => $this->db->affectedRows()];
    }

    private function removeOwnedSpecial(array $mapping): bool
    {
        $ruleId = (int)$mapping['rule_id'];
        $productId = (int)$mapping['products_id'];
        $specialsId = (int)$mapping['specials_id'];
        $this->db->Execute("DELETE FROM " . TABLE_SPECIALS . " WHERE specials_id = $specialsId AND products_id = $productId");
        $deleted = $this->db->affectedRows() > 0;
        $this->db->Execute("DELETE FROM {$this->mappingTable} WHERE rule_id = $ruleId AND products_id = $productId AND specials_id = $specialsId");
        return $deleted;
    }

    private function matchingProducts(array $rule): array
    {
        $where = ['1 = 1'];
        if ((int)$rule['manufacturer_id'] > 0) {
            $where[] = 'p.manufacturers_id = ' . (int)$rule['manufacturer_id'];
        }
        if ((int)$rule['category_id'] > 0) {
            $categories = [(int)$rule['category_id']];
            if ((int)$rule['include_subcategories'] === 1) {
                $categories = $this->categoryIds($categories);
            }
            $where[] = 'EXISTS (SELECT 1 FROM ' . TABLE_PRODUCTS_TO_CATEGORIES . ' p2c WHERE p2c.products_id = p.products_id AND p2c.categories_id IN (' . implode(',', $categories) . '))';
        }
        foreach (['stock_min' => 'p.products_quantity >= ', 'stock_max' => 'p.products_quantity <= '] as $field => $condition) {
            if ($rule[$field] !== null && $rule[$field] !== '') {
                $where[] = $condition . $this->sqlValue((float)$rule[$field]);
            }
        }
        if ((string)$rule['model_prefix'] !== '') {
            $prefix = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], (string)$rule['model_prefix']);
            $prefix = zen_db_input($prefix);
            $where[] = "p.products_model LIKE '$prefix%' ESCAPE '\\\\'";
        }
        if (!empty($rule['date_added_from'])) {
            $where[] = "DATE(p.products_date_added) >= '" . zen_db_input((string)$rule['date_added_from']) . "'";
        }
        if (!empty($rule['date_added_to'])) {
            $where[] = "DATE(p.products_date_added) <= '" . zen_db_input((string)$rule['date_added_to']) . "'";
        }
        if ((int)$rule['featured_only'] === 1) {
            $where[] = 'EXISTS (SELECT 1 FROM ' . TABLE_FEATURED . ' f WHERE f.products_id = p.products_id AND f.status = 1)';
        }
        if ((int)$rule['active_only'] === 1) {
            $where[] = 'p.products_status = 1';
        }
        if ((int)$rule['include_attribute_priced'] !== 1) {
            $where[] = 'p.products_priced_by_attribute = 0';
        }
        $languageId = (int)($_SESSION['languages_id'] ?? 1);
        $products = $this->rows($this->db->Execute(
            "SELECT p.products_id, p.products_model, p.products_price, p.products_quantity, p.products_priced_by_attribute, p.products_status, pd.products_name,
                    s.specials_id, rp.rule_id AS owner_rule_id
               FROM " . TABLE_PRODUCTS . " p
               LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pd.products_id = p.products_id AND pd.language_id = $languageId
               LEFT JOIN " . TABLE_SPECIALS . " s ON s.products_id = p.products_id
               LEFT JOIN {$this->mappingTable} rp ON rp.specials_id = s.specials_id AND rp.products_id = p.products_id
              WHERE " . implode(' AND ', $where) . "
              ORDER BY pd.products_name, p.products_id"
        ));
        $priceMin = $rule['price_min'] !== null && $rule['price_min'] !== '' ? (float)$rule['price_min'] : null;
        $priceMax = $rule['price_max'] !== null && $rule['price_max'] !== '' ? (float)$rule['price_max'] : null;
        foreach ($products as $index => &$product) {
            $product['base_price'] = (int)$product['products_priced_by_attribute'] === 1
                ? (float)zen_get_products_base_price((int)$product['products_id'])
                : (float)$product['products_price'];
            if (($priceMin !== null && $product['base_price'] < $priceMin) || ($priceMax !== null && $product['base_price'] > $priceMax)) {
                unset($products[$index]);
            }
        }
        unset($product);
        return array_values($products);
    }

    private function categoryIds(array $categoryIds): array
    {
        $found = array_fill_keys($categoryIds, true);
        $pending = $categoryIds;
        while ($pending !== []) {
            $parents = implode(',', array_map('intval', $pending));
            $pending = [];
            $children = $this->db->Execute("SELECT categories_id FROM " . TABLE_CATEGORIES . " WHERE parent_id IN ($parents)");
            while (!$children->EOF) {
                $id = (int)$children->fields['categories_id'];
                if (!isset($found[$id])) {
                    $found[$id] = true;
                    $pending[] = $id;
                }
                $children->MoveNext();
            }
        }
        return array_map('intval', array_keys($found));
    }

    private function calculatePrice(array $rule, float $basePrice): ?float
    {
        $value = (float)$rule['price_value'];
        if ($rule['price_method'] === 'percentage') {
            $price = $basePrice * (1 - ($value / 100));
        } elseif ($rule['price_method'] === 'amount') {
            $price = $basePrice - $value;
        } else {
            $price = $value;
        }
        if ($rule['price_method'] !== 'set' && (int)$rule['round_price'] === 1) {
            $price = round($price, 2);
        }
        if ($price <= 0 || $price >= $basePrice) {
            return null;
        }
        return round($price, 4);
    }

    private function resultForProduct(int $ruleId, array $product): string
    {
        if ($product['calculated_price'] === null) {
            return 'Invalid calculated price';
        }
        if (empty($product['specials_id'])) {
            return 'Ready to create';
        }
        if ((int)($product['owner_rule_id'] ?? 0) === $ruleId) {
            return 'Ready to update';
        }
        return empty($product['owner_rule_id']) ? 'Manual Special conflict' : 'Owned by another rule';
    }

    private function requireRule(int $ruleId): array
    {
        $rule = $this->getRule($ruleId);
        if ($rule === null) {
            throw new RuntimeException('The selected rule no longer exists.');
        }
        return $rule;
    }

    private function decimal(mixed $value, bool $allowBlank = true): ?float
    {
        if ($allowBlank && ($value === null || trim((string)$value) === '')) {
            return null;
        }
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Numeric filters must contain valid numbers.');
        }
        return (float)$value;
    }

    private function date(mixed $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException('Dates must use YYYY-MM-DD format.');
        }
        return $value;
    }

    private function specialDate(mixed $value): string
    {
        return empty($value) ? '0001-01-01' : zen_db_input((string)$value);
    }

    private function sqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }
        return "'" . zen_db_input((string)$value) . "'";
    }

    private function rows(object $result): array
    {
        $rows = [];
        while (!$result->EOF) {
            $rows[] = $result->fields;
            $result->MoveNext();
        }
        return $rows;
    }
}
