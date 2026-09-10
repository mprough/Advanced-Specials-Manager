# User guide

## Create a rule

Open **Catalog > Advanced Specials Manager** and choose **Create rule**. Give the rule a descriptive name, select any product filters, choose a pricing method, enter its value, and save it.

Blank filters do not restrict the selection. When several filters are used, a product must satisfy all of them.

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

## Disable or delete

Disabling a rule disables the Specials it owns. Applying an enabled rule refreshes its product matches and prices. Deleting a rule removes only the Specials owned by that rule.
