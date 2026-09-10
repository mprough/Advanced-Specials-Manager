<?php

declare(strict_types=1);

require 'includes/application_top.php';
require_once DIR_WS_CLASSES . 'AdvancedSpecialsManager.php';

$manager = new AdvancedSpecialsManager($db);
$action = (string)($_GET['action'] ?? 'list');
$ruleId = max(0, (int)($_GET['rule_id'] ?? $_POST['rule_id'] ?? 0));

function asm_h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, CHARSET);
}

function asm_checked(array $rule, string $field): string
{
    return !empty($rule[$field]) ? ' checked' : '';
}

function asm_token_valid(): bool
{
    $posted = (string)($_POST['securityToken'] ?? '');
    $session = (string)($_SESSION['securityToken'] ?? '');
    return $posted !== '' && $session !== '' && hash_equals($session, $posted);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!asm_token_valid()) {
        $messageStack->add_session('Security token validation failed. Please try again.', 'error');
        zen_redirect(zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER));
    }
    try {
        if ($action === 'save') {
            $savedId = $manager->saveRule($_POST);
            if (!isset($_POST['rule_status'])) {
                $summary = $manager->apply($savedId);
                $messageStack->add_session('The rule was saved as disabled. Its ' . $summary['disabled'] . ' generated Specials were disabled.', 'success');
            } else {
                $messageStack->add_session('The rule was saved. Preview it before applying.', 'success');
            }
            zen_redirect(zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER, 'action=preview&rule_id=' . $savedId));
        }
        if ($action === 'apply') {
            $summary = $manager->apply($ruleId);
            $messageStack->add_session(
                sprintf(
                    'Rule applied. Created: %d. Updated: %d. Removed: %d. Disabled: %d. Conflicts: %d. Invalid prices: %d.',
                    $summary['created'],
                    $summary['updated'],
                    $summary['removed'],
                    $summary['disabled'],
                    $summary['conflicts'],
                    $summary['invalid']
                ),
                $summary['conflicts'] > 0 || $summary['invalid'] > 0 ? 'warning' : 'success'
            );
            zen_redirect(zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER, 'action=preview&rule_id=' . $ruleId));
        }
        if ($action === 'delete') {
            $removed = $manager->deleteRule($ruleId);
            $messageStack->add_session('The rule and ' . $removed . ' owned Specials were removed.', 'success');
            zen_redirect(zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER));
        }
    } catch (Throwable $exception) {
        $messageStack->add($exception->getMessage(), 'error');
        $action = $action === 'save' ? 'edit' : $action;
    }
}

$blankRule = [
    'rule_id' => 0,
    'rule_name' => '',
    'rule_status' => 1,
    'manufacturer_id' => 0,
    'category_id' => 0,
    'include_subcategories' => 1,
    'price_min' => '',
    'price_max' => '',
    'stock_min' => '',
    'stock_max' => '',
    'model_prefix' => '',
    'date_added_from' => '',
    'date_added_to' => '',
    'featured_only' => 0,
    'active_only' => 1,
    'include_attribute_priced' => 0,
    'price_method' => 'percentage',
    'price_value' => '',
    'round_price' => 0,
    'available_date' => '',
    'expires_date' => '',
];
$rule = $blankRule;
if (($action === 'edit' || $action === 'preview') && $ruleId > 0) {
    $loadedRule = $manager->getRule($ruleId);
    if ($loadedRule === null) {
        $messageStack->add('The selected rule no longer exists.', 'error');
        $action = 'list';
    } else {
        $rule = array_merge($blankRule, $loadedRule);
    }
}

$manufacturers = [];
$manufacturerRows = $db->Execute('SELECT manufacturers_id, manufacturers_name FROM ' . TABLE_MANUFACTURERS . ' ORDER BY manufacturers_name');
while (!$manufacturerRows->EOF) {
    $manufacturers[] = $manufacturerRows->fields;
    $manufacturerRows->MoveNext();
}

$categories = [];
$categoryRows = $db->Execute(
    'SELECT c.categories_id, cd.categories_name FROM ' . TABLE_CATEGORIES . ' c INNER JOIN ' . TABLE_CATEGORIES_DESCRIPTION . ' cd ON cd.categories_id = c.categories_id AND cd.language_id = ' . (int)($_SESSION['languages_id'] ?? 1) . ' ORDER BY cd.categories_name'
);
while (!$categoryRows->EOF) {
    $categories[] = $categoryRows->fields;
    $categoryRows->MoveNext();
}

?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
  <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<style>
.asm-wrap{padding:16px}.asm-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}.asm-card{background:#fff;border:1px solid #ccd0d4;border-radius:4px;padding:18px;margin-bottom:18px}.asm-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px 18px}.asm-field label{display:block;font-weight:700;margin-bottom:5px}.asm-field input[type=text],.asm-field input[type=number],.asm-field input[type=date],.asm-field select{width:100%;max-width:100%;padding:7px}.asm-check{padding-top:26px}.asm-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:18px}.asm-table{width:100%;border-collapse:collapse}.asm-table th,.asm-table td{border-bottom:1px solid #ddd;padding:9px;text-align:left;vertical-align:top}.asm-table th{background:#f5f5f5}.asm-muted{color:#666}.asm-conflict{color:#a94442;font-weight:700}.asm-ready{color:#267326;font-weight:700}
</style>
<div class="container-fluid asm-wrap">
  <div class="asm-head">
    <div><h1>Advanced Specials Manager</h1><p class="asm-muted">Create normal Zen Cart Specials from reusable product rules.</p></div>
    <?php if ($action !== 'edit'): ?><a class="btn btn-primary" href="<?= zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER, 'action=edit') ?>">Create rule</a><?php endif; ?>
  </div>

<?php if ($action === 'edit'): ?>
  <form method="post" action="<?= zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER, 'action=save') ?>" class="asm-card">
    <input type="hidden" name="securityToken" value="<?= asm_h($_SESSION['securityToken'] ?? '') ?>">
    <input type="hidden" name="rule_id" value="<?= (int)$rule['rule_id'] ?>">
    <h2><?= (int)$rule['rule_id'] > 0 ? 'Edit rule' : 'Create rule' ?></h2>
    <div class="asm-grid">
      <div class="asm-field"><label for="rule_name">Rule name</label><input id="rule_name" name="rule_name" type="text" maxlength="128" required value="<?= asm_h($rule['rule_name']) ?>"></div>
      <div class="asm-field"><label for="manufacturer_id">Manufacturer</label><select id="manufacturer_id" name="manufacturer_id"><option value="0">All manufacturers</option><?php foreach ($manufacturers as $item): ?><option value="<?= (int)$item['manufacturers_id'] ?>"<?= (int)$rule['manufacturer_id'] === (int)$item['manufacturers_id'] ? ' selected' : '' ?>><?= asm_h($item['manufacturers_name']) ?></option><?php endforeach; ?></select></div>
      <div class="asm-field"><label for="category_id">Category</label><select id="category_id" name="category_id"><option value="0">All categories</option><?php foreach ($categories as $item): ?><option value="<?= (int)$item['categories_id'] ?>"<?= (int)$rule['category_id'] === (int)$item['categories_id'] ? ' selected' : '' ?>><?= asm_h($item['categories_name']) ?></option><?php endforeach; ?></select></div>
      <div class="asm-field asm-check"><label><input type="checkbox" name="include_subcategories" value="1"<?= asm_checked($rule, 'include_subcategories') ?>> Include subcategories</label></div>
      <div class="asm-field"><label for="price_min">Minimum base price</label><input id="price_min" name="price_min" type="number" min="0" step="0.0001" value="<?= asm_h($rule['price_min']) ?>"></div>
      <div class="asm-field"><label for="price_max">Maximum base price</label><input id="price_max" name="price_max" type="number" min="0" step="0.0001" value="<?= asm_h($rule['price_max']) ?>"></div>
      <div class="asm-field"><label for="stock_min">Minimum stock</label><input id="stock_min" name="stock_min" type="number" step="0.0001" value="<?= asm_h($rule['stock_min']) ?>"></div>
      <div class="asm-field"><label for="stock_max">Maximum stock</label><input id="stock_max" name="stock_max" type="number" step="0.0001" value="<?= asm_h($rule['stock_max']) ?>"></div>
      <div class="asm-field"><label for="model_prefix">Model prefix</label><input id="model_prefix" name="model_prefix" type="text" maxlength="64" value="<?= asm_h($rule['model_prefix']) ?>"></div>
      <div class="asm-field"><label for="date_added_from">Added on or after</label><input id="date_added_from" name="date_added_from" type="date" value="<?= asm_h($rule['date_added_from']) ?>"></div>
      <div class="asm-field"><label for="date_added_to">Added on or before</label><input id="date_added_to" name="date_added_to" type="date" value="<?= asm_h($rule['date_added_to']) ?>"></div>
      <div class="asm-field asm-check"><label><input type="checkbox" name="featured_only" value="1"<?= asm_checked($rule, 'featured_only') ?>> Featured products only</label></div>
      <div class="asm-field asm-check"><label><input type="checkbox" name="active_only" value="1"<?= asm_checked($rule, 'active_only') ?>> Active products only</label></div>
      <div class="asm-field asm-check"><label><input type="checkbox" name="include_attribute_priced" value="1"<?= asm_checked($rule, 'include_attribute_priced') ?>> Include products priced by attributes</label></div>
      <div class="asm-field asm-check"><label><input type="checkbox" name="rule_status" value="1"<?= asm_checked($rule, 'rule_status') ?>> Rule enabled</label></div>
      <div class="asm-field"><label for="price_method">Pricing method</label><select id="price_method" name="price_method"><option value="percentage"<?= $rule['price_method'] === 'percentage' ? ' selected' : '' ?>>Percentage discount</option><option value="amount"<?= $rule['price_method'] === 'amount' ? ' selected' : '' ?>>Amount discount</option><option value="set"<?= $rule['price_method'] === 'set' ? ' selected' : '' ?>>Set special price</option></select></div>
      <div class="asm-field"><label for="price_value">Pricing value</label><input id="price_value" name="price_value" type="number" min="0.0001" step="0.0001" required value="<?= asm_h($rule['price_value']) ?>"></div>
      <div class="asm-field asm-check"><label><input type="checkbox" name="round_price" value="1"<?= asm_checked($rule, 'round_price') ?>> Round calculated price to two decimal places</label><span class="asm-muted">Set prices are never rounded.</span></div>
      <div class="asm-field"><label for="available_date">Available date</label><input id="available_date" name="available_date" type="date" value="<?= asm_h($rule['available_date']) ?>"></div>
      <div class="asm-field"><label for="expires_date">Expiration date</label><input id="expires_date" name="expires_date" type="date" value="<?= asm_h($rule['expires_date']) ?>"></div>
    </div>
    <div class="asm-actions"><button class="btn btn-primary" type="submit">Save rule</button><a class="btn btn-default" href="<?= zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER) ?>">Cancel</a></div>
  </form>
<?php elseif ($action === 'preview' && $ruleId > 0): $preview = $manager->preview($ruleId); ?>
  <div class="asm-card">
    <h2><?= asm_h($rule['rule_name']) ?></h2>
    <p><?= count($preview) ?> matching products. Preview results are recalculated when the rule is applied.</p>
    <div class="asm-actions">
      <form method="post" action="<?= zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER, 'action=apply&rule_id=' . $ruleId) ?>"><input type="hidden" name="securityToken" value="<?= asm_h($_SESSION['securityToken'] ?? '') ?>"><button class="btn btn-success" type="submit">Apply rule</button></form>
      <a class="btn btn-primary" href="<?= zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER, 'action=edit&rule_id=' . $ruleId) ?>">Edit rule</a>
      <a class="btn btn-default" href="<?= zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER) ?>">Back to rules</a>
    </div>
  </div>
  <div class="asm-card table-responsive"><table class="asm-table"><thead><tr><th>ID</th><th>Product</th><th>Model</th><th>Base price</th><th>Special price</th><th>Stock</th><th>Attributes</th><th>Result</th></tr></thead><tbody>
  <?php foreach ($preview as $product): $ready = str_starts_with((string)$product['result'], 'Ready'); ?><tr><td><?= (int)$product['products_id'] ?></td><td><?= asm_h($product['products_name'] ?: '(unnamed product)') ?></td><td><?= asm_h($product['products_model']) ?></td><td><?= asm_h(number_format((float)$product['base_price'], 4)) ?></td><td><?= $product['calculated_price'] === null ? 'Not valid' : asm_h(number_format((float)$product['calculated_price'], 4)) ?></td><td><?= asm_h($product['products_quantity']) ?></td><td><?= (int)$product['products_priced_by_attribute'] === 1 ? 'Yes' : 'No' ?></td><td class="<?= $ready ? 'asm-ready' : 'asm-conflict' ?>"><?= asm_h($product['result']) ?></td></tr><?php endforeach; ?>
  <?php if ($preview === []): ?><tr><td colspan="8">No products match this rule.</td></tr><?php endif; ?>
  </tbody></table></div>
<?php else: $rules = $manager->getRules(); ?>
  <div class="asm-card table-responsive"><table class="asm-table"><thead><tr><th>Rule</th><th>Status</th><th>Method</th><th>Value</th><th>Generated Specials</th><th>Actions</th></tr></thead><tbody>
  <?php foreach ($rules as $item): ?><tr><td><?= asm_h($item['rule_name']) ?></td><td><?= (int)$item['rule_status'] === 1 ? 'Enabled' : 'Disabled' ?></td><td><?= asm_h(ucfirst((string)$item['price_method'])) ?></td><td><?= asm_h($item['price_value']) ?></td><td><?= (int)$item['generated_count'] ?></td><td><a class="btn btn-xs btn-primary" href="<?= zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER, 'action=preview&rule_id=' . (int)$item['rule_id']) ?>">Preview</a> <a class="btn btn-xs btn-default" href="<?= zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER, 'action=edit&rule_id=' . (int)$item['rule_id']) ?>">Edit</a> <form method="post" style="display:inline" action="<?= zen_href_link(FILENAME_ADVANCED_SPECIALS_MANAGER, 'action=delete&rule_id=' . (int)$item['rule_id']) ?>" onsubmit="return confirm('Delete this rule and the Specials it owns?');"><input type="hidden" name="securityToken" value="<?= asm_h($_SESSION['securityToken'] ?? '') ?>"><button class="btn btn-xs btn-danger" type="submit">Delete</button></form></td></tr><?php endforeach; ?>
  <?php if ($rules === []): ?><tr><td colspan="6">No rules have been created.</td></tr><?php endif; ?>
  </tbody></table></div>
<?php endif; ?>
</div>
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
</body>
</html>
