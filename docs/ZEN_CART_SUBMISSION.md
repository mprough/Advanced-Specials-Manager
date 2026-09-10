# Zen Cart submission copy

## Plugin name

Advanced Specials Manager

## Version

1.0.4

## Suggested category

Admin tools

## Short description

Create and manage native Zen Cart Specials in bulk with reusable product rules. Select products by manufacturer, category, base price, stock, model, date added, Featured status, or active status, then apply percentage, amount, or set price promotions.

## Full description

Advanced Specials Manager creates ordinary Zen Cart Specials from reusable administration rules. It is useful for manufacturer sales, category promotions, inventory clearance, new product offers, featured product sales, and price range promotions.

Rules can combine product filters, preview every match and conflict, and apply a percentage discount, amount discount, or exact special price. Percentage and amount calculations can optionally be rounded to two decimal places. Exact set prices are never changed by rounding.

Promotions may use an available date, expiration date, combined quantity sold limit, or both an expiration date and quantity limit. When both limits are present, the sale ends when either condition is reached first.

The plugin protects existing manual Specials and Specials owned by another rule. It tracks the exact Specials it creates so disabling, deleting, or uninstalling a rule affects only plugin owned records.

Products priced by attributes may be included or excluded. When included, Zen Cart's native **Apply Discounts Used by Product Special/Sale** attribute setting remains in control of whether the Special affects each attribute.

The plugin uses Zen Cart Plugin Manager, requires no core or template file changes, and makes no external service calls.

## Feature list

- Manufacturer selection
- Category selection with optional subcategories
- Minimum and maximum base price
- Minimum and maximum stock
- Product model prefix
- Date added range
- Featured products only
- Active products only
- Optional inclusion of products priced by attributes
- Percentage discount
- Amount discount
- Exact set special price
- Optional two decimal rounding for calculated prices
- Available and expiration dates
- Combined quantity sold limit across all products in a rule
- Date and quantity limits used together, whichever occurs first
- Product, price, exclusion, and conflict preview
- Protection for manual Specials and Specials owned by another rule
- Safe rule disable, refresh, delete, and uninstall behavior
- Runtime administration menu repair
- Native Zen Cart Specials output

## Compatibility

- Zen Cart 2.0.x, 2.1.x, and 2.2.x
- PHP versions supported by the installed Zen Cart release, through PHP 8.5
- MySQL and MariaDB versions supported by Zen Cart

## Installation summary

Copy the package `files` directory into the store root, then install **Advanced Specials Manager** through **Modules > Plugin Manager**. After installation, open **Catalog > Advanced Specials Manager**.

## Upgrade summary

Copy the new package files into the store root and use Plugin Manager to upgrade. Version 1.0.4 upgrades versions 1.0.0 through 1.0.3 while preserving existing rules and owned Specials.

## Support statement

Report reproducible bugs through the [PRO-Webs helpdesk](https://prowebsinc.zohodesk.com/portal/en/newticket). Installation, configuration, and customization are not included with free distribution. Custom work is available separately.

## License and warranty

Copyright Melanie Prough, PRO-Webs, Inc.

Distributed under the GNU General Public License version 2. This software is provided without warranty.

## Links

- Repository: https://github.com/mprough/Advanced-Specials-Manager
- Maintainer: https://pro-webs.net/
- Support: https://prowebsinc.zohodesk.com/portal/en/newticket

The Zen Cart plugin page link and numeric `pluginId` can be added after the submission is approved and an official plugin ID is assigned.
