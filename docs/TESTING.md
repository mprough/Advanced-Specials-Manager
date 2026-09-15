# Testing checklist

Use a staging store or a backed up development store before testing a production promotion.

## Installation and access

- Install with Zen Cart Plugin Manager.
- Confirm the installed version is 1.0.5.
- Confirm **Catalog > Advanced Specials Manager** appears for an authorized administrator.
- Open the page and confirm the normal Zen Cart administration layout loads.
- Remove the menu registration in a test database, reload the administration, and confirm runtime repair restores it without changing existing administrator permissions.

## Rule selection

- Preview a manufacturer rule.
- Preview a category rule with and without subcategories.
- Test minimum and maximum base price filters separately and together.
- Test minimum and maximum stock filters separately and together.
- Test model prefix, date added range, Featured, and active product filters.
- Confirm combined filters use AND behavior.
- Confirm attribute priced products are excluded by default and included only when requested.

## Pricing

- Test percentage and amount discounts with rounding off and on.
- Test set price and confirm rounding does not alter the entered price.
- Confirm zero, negative, unchanged, and above base price results are rejected.
- Confirm a manual Special is reported as a conflict and remains unchanged.
- Confirm a Special owned by another rule is reported as a conflict.

## Dates and quantity limits

- Test an immediate Special and a future available date.
- Test an expiration date without a quantity limit.
- Test a quantity limit without an expiration date.
- Test both limits together and confirm the first reached condition ends the sale.
- Purchase more than one product owned by the same rule and confirm quantities are combined.
- Confirm the same order is not counted twice for the same rule.
- Confirm a manual inventory adjustment does not change the sold counter.
- Confirm reset clears the counter before the rule is reused.

## Lifecycle

- Disable a rule and confirm its owned Specials are disabled.
- Reapply an edited rule and confirm matches and prices refresh.
- Delete a rule and confirm only its owned Specials are removed.
- Upgrade an earlier plugin version and confirm rules and owned Specials remain.
- Uninstall and confirm plugin tables, settings, menu registration, and owned Specials are removed while manual Specials remain.

## Automated checks

The repository workflow lints every PHP file on PHP 8.0 through 8.5. Run `bash tests/check-package.sh` in an environment with PHP installed to check required paths, version consistency, URL handling, PHP syntax, and whitespace errors.
