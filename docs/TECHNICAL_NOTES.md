# Technical notes

## Database tables

`advanced_specials_rules` stores rule definitions. `advanced_specials_rule_products` records the exact `specials_id` and `products_id` created for each rule. `advanced_specials_rule_orders` records each counted order once per rule so a repeated checkout notification cannot count the same order twice.

The ownership table allows the plugin to update and remove its own Specials without treating an existing manual Special as plugin data.

## Selection behavior

Rule filters are joined with AND. Category matching uses `products_to_categories` and optionally walks the category parent relationships. Normal products use `products.products_price`. Products priced by attributes use Zen Cart's `zen_get_products_base_price()` result, matching the native Specials calculation.

## Special price behavior

The plugin stores a calculated numeric value in `specials.specials_new_products_price`. Start and expiration values use Zen Cart's `specials_date_available` and `expires_date` columns.

## Lifecycle

The scripted installer creates the tables, version marker, and Catalog menu registration. A runtime bootstrap supplies constants before menu creation and repairs a missing registration without rewriting an existing permission record.

## Quantity limit

The catalog observer listens after order products are created. It totals the purchased quantities whose products belong to an active rule, updates that rule's combined counter atomically, and disables the rule's owned Specials when the limit is reached. The expiration date remains Zen Cart's native expiration mechanism, so either condition can end the sale.
