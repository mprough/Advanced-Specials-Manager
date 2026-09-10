# Installation

## Requirements

- Zen Cart 2.0.x, 2.1.x, or 2.2.x
- A PHP version supported by that Zen Cart release
- Database backup access
- Zen Cart Plugin Manager access

## Install

1. Back up the store files and database.
2. Extract the package on your computer.
3. Copy the contents of the package `files` directory into the store root. The `zc_plugins` directory will merge with the existing store directory.
4. In the Zen Cart administration, open **Modules > Plugin Manager**.
5. Find **Advanced Specials Manager** and choose **Install**.
6. Confirm that **Catalog > Advanced Specials Manager** appears and opens correctly.
7. Create a small test rule, preview it, apply it, and confirm the resulting Special on the storefront before creating a large promotion.

No core files or template files are replaced.

## Database changes

Installation creates three tables using the store database prefix:

- `advanced_specials_rules` stores rule settings and quantity counters.
- `advanced_specials_rule_products` records which native Specials belong to each rule.
- `advanced_specials_rule_orders` prevents an order from being counted more than once for a rule.

It also creates a version marker in the configuration table and registers the Catalog menu page.

## Upgrade

1. Back up the store files and database.
2. Copy the contents of the new package `files` directory into the store root.
3. Open **Modules > Plugin Manager**.
4. Choose the offered upgrade for **Advanced Specials Manager**.
5. Confirm the installed version and open the rule manager.

Each release has its own complete version directory. Version 1.0.4 can upgrade an installation of versions 1.0.0 through 1.0.3. Existing rules and owned Specials are retained. The 1.0.4 installer adds the quantity tracking columns and order tracking table when they are missing.

Do not remove an installed version directory manually before Plugin Manager completes the upgrade.

## Uninstall

Use Plugin Manager to uninstall the plugin. Uninstall removes Specials that are positively identified in the plugin ownership table, then removes plugin tables, settings, and menu registrations. Manually created Specials are not removed.

Uninstall permanently removes saved rules and their quantity history. Back up the database first if that information may be needed later.

## Troubleshooting

- If the menu entry is missing, reload an administration page. The plugin includes runtime menu repair. If it remains missing, confirm the plugin is installed in Plugin Manager and that the administrator profile can access the page.
- If a rule matches no products, remove filters one at a time and preview again. All entered filters must match.
- If a product is reported as a conflict, it already has a manual Special or belongs to another rule. The plugin will not replace it.
- If an attribute price does not change, confirm that **Include products priced by attributes** is enabled in the rule and that the attribute has **Apply Discounts Used by Product Special/Sale** enabled.
- If the quantity limit does not change, confirm that the order completed far enough for Zen Cart to create its order product rows. Manual stock changes are not sales and are not counted.

For reproducible bugs, use the [PRO-Webs helpdesk](https://prowebsinc.zohodesk.com/portal/en/newticket). Installation, configuration, and customization are not included with free distribution.
