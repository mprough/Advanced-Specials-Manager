# User guide

## Create a rule

Open **Catalog > Advanced Specials Manager** and choose **Create rule**. Give the rule a descriptive name, select any product filters, choose a pricing method, enter its value, and save it.

Blank filters do not restrict the selection. When several filters are used, a product must satisfy all of them.

## Rule fields

| Field | What it does |
| --- | --- |
| Rule name | Identifies the promotion in the administration. |
| Manufacturer | Limits the rule to one manufacturer. |
| Category | Limits the rule to products linked to one category. |
| Include subcategories | Includes products linked to categories below the selected category. |
| Minimum and maximum price | Limits products by their base price. Either field may be left blank. |
| Minimum and maximum stock | Limits products by the current product quantity. Either field may be left blank. |
| Model prefix | Matches product model values that begin with the entered text. |
| Date added from and to | Limits products by their Zen Cart date added value. |
| Featured products only | Requires the product to have a currently active Featured record. |
| Active products only | Excludes disabled products when selected. |
| Include products priced by attributes | Allows products whose base price is calculated from attributes. |
| Pricing method | Chooses percentage discount, amount discount, or set special price. |
| Value | Supplies the percentage, amount, or exact special price for the selected method. |
| Round calculated price | Rounds percentage and amount calculations to two decimal places. It does not change set prices. |
| Available date | Schedules the Special to begin on this date. Blank means it can begin immediately. |
| Expiration date | Schedules the Special to end on this date. Blank means no date limit. |
| Combined quantity sold limit | Ends every Special owned by the rule after the combined sold quantity reaches this value. Blank or zero means no quantity limit. |

## Category selection

A category rule matches products linked to the selected category. Enable **Include subcategories** to include products linked anywhere below it.

## Products priced by attributes

Set **Include products priced by attributes** to No when those products should be skipped. When they are included, Zen Cart applies the Special to an attribute only when that attribute has **Apply Discounts Used by Product Special/Sale** enabled.

## Pricing methods

- **Percentage discount** subtracts the entered percentage from the product base price.
- **Amount discount** subtracts the entered amount from the product base price.
- **Set special price** uses the entered value as the special price.

Optional rounding rounds calculated percentage and amount discounts to two decimal places. It never changes a set special price.

## Preview and apply

Preview reports each matching product, its current base price, its calculated special price, and any reason it cannot be changed. Apply evaluates the rule again and creates or updates its tracked Specials.

Existing manual Specials and Specials created by another rule are reported as conflicts and left unchanged.

A calculated price must be greater than zero and lower than the product base price. Invalid calculated results are reported and are not applied.

## End after a quantity is sold

Enter a combined quantity sold limit to end a sale when that many matching items have been sold across the entire rule. The expiration date and quantity limit work together: the sale ends when either condition happens first.

The counter starts when the rule is first applied with a quantity limit. It increases from completed order product quantities, not from manual inventory changes. Canceling or refunding an order does not automatically reopen the sale or subtract from the counter.

Use **Reset quantity sold** before enabling and applying a completed rule for a new promotion.

## Example rules

### Manufacturer sale

Select a manufacturer, choose a percentage discount, enter `15`, preview, then apply. Every matching product without a conflicting Special receives a native 15 percent Special.

### Category clearance

Select a category, include subcategories if required, set a maximum stock or date added range, choose an amount discount, then preview and apply.

### Fixed price promotion

Choose **Set special price** and enter the exact price. Rounding is ignored because the entered value is already the final price.

### Limited quantity promotion

Set an expiration date and a combined quantity sold limit. The sale ends when the first limit is reached. The quantity is shared across all products owned by that rule.

## Disable or delete

Disabling a rule disables the Specials it owns. Applying an enabled rule refreshes its product matches and prices. Deleting a rule removes only the Specials owned by that rule.

Review the preview again after editing filters or prices. Products and existing Specials may have changed since an earlier preview.
