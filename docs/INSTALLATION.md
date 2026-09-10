# Installation

## Requirements

- Zen Cart 2.0.x, 2.1.x, or 2.2.x
- A PHP version supported by that Zen Cart release
- Database backup access
- Zen Cart Plugin Manager access

## Install

1. Back up the store files and database.
2. Copy the contents of the package `files` directory into the store root.
3. In the Zen Cart administration, open **Modules > Plugin Manager**.
4. Find **Advanced Specials Manager** and choose **Install**.
5. Open **Catalog > Advanced Specials Manager**.

No core files or template files are replaced.

## Upgrade

Copy the new package files into the store root, then use Plugin Manager to upgrade. Each release has its own complete version directory.

## Uninstall

Use Plugin Manager to uninstall the plugin. Uninstall removes Specials that are positively identified in the plugin ownership table, then removes plugin tables, settings, and menu registrations. Manually created Specials are not removed.
