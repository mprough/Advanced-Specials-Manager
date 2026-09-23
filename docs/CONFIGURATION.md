# Configuration and rule fields

Current release: **1.0.5**. Advanced Specials Manager appears under **Catalog > Advanced Specials Manager**. It has one Zen Cart configuration key, which records the installed version. Promotion choices are saved separately for each rule; they are not store-wide settings under **Configuration**.

## Zen Cart configuration

| Admin label | Configuration key | Install default | Purpose |
| --- | --- | --- | --- |
| Installed version | `PLUGIN_ADVANCED_SPECIALS_MANAGER_VERSION` | `1.0.5` | Read-only version record managed by the Plugin Manager installer. |

## Rule fields

These are the defaults shown when selecting **Create rule**. An empty filter places no restriction on matching products. Several filters must all match.

| Admin field | Stored field | New rule default | Purpose |
| --- | --- | --- | --- |
| Rule name | `rule_name` | Empty, required | Name shown in the rule manager. |
| Rule enabled | `rule_status` | On | A disabled rule keeps its definition and disables its Specials. |
| Manufacturer | `manufacturer_id` | `0`, all | Select one manufacturer. |
| Category | `category_id` | `0`, all | Match products linked to the selected category. |
| Include subcategories | `include_subcategories` | On | Extend a selected category to its descendants. |
| Minimum base price | `price_min` | Empty | Include products at or above this base price. |
| Maximum base price | `price_max` | Empty | Include products at or below this base price. |
| Minimum stock | `stock_min` | Empty | Include products with at least this quantity. |
| Maximum stock | `stock_max` | Empty | Include products with at most this quantity. |
| Model prefix | `model_prefix` | Empty | Match models beginning with these exact characters. |
| Added on or after | `date_added_from` | Empty | Earliest product date added. |
| Added on or before | `date_added_to` | Empty | Latest product date added. |
| Featured products only | `featured_only` | Off | Require an active Featured record. |
| Active products only | `active_only` | On | Exclude disabled products. |
| Include products priced by attributes | `include_attribute_priced` | Off | Include products whose base price is calculated from attributes. Attribute discounts still follow Zen Cart's **Apply Discounts Used by Product Special/Sale** setting. |
| Pricing method | `price_method` | `percentage` | Choose `percentage` discount, `amount` discount, or `set` special price. |
| Pricing value | `price_value` | Empty, required | Percentage, amount off, or final special price according to the method. |
| Round calculated price to two decimal places | `round_price` | Off | Applies to percentage and amount discounts; set prices are unchanged. |
| Available date | `available_date` | Empty | Begin immediately when blank. |
| Expiration date | `expires_date` | Empty | No date limit when blank. |
| Combined quantity sold limit | `sales_limit` | `0` | End all Specials owned by this rule after this many matching items have sold across the rule. Zero means no quantity limit. |

The expiration date and quantity limit can be used together. The rule ends when either limit is reached. **Reset quantity sold** is an action on an existing rule, not a stored setting; use it before reusing a completed promotion. The manager tracks `sold_quantity`, `sales_started_at`, and `ended_reason` automatically.

## Maintenance

The Zen Cart setting comes from [the v1.0.5 scripted installer](../files/zc_plugins/AdvancedSpecialsManager/v1.0.5/Installer/ScriptedInstaller.php). Rule defaults and form controls come from [the admin rule page](../files/zc_plugins/AdvancedSpecialsManager/v1.0.5/admin/advanced_specials_manager.php), with validation in [the manager class](../files/zc_plugins/AdvancedSpecialsManager/v1.0.5/admin/includes/classes/AdvancedSpecialsManager.php). For each new release, compare all three files with this reference, update its version, labels, choices, and defaults, and confirm that stored fields and rule actions remain accurate. See the [user guide](USER_GUIDE.md) for examples and instructions.
