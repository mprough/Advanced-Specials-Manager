#!/usr/bin/env bash
set -euo pipefail

version="1.0.5"
root="files/zc_plugins/AdvancedSpecialsManager/v${version}"

test -f "${root}/manifest.php"
test -f "${root}/Installer/ScriptedInstaller.php"
test -f "${root}/admin/advanced_specials_manager.php"
test -f "${root}/admin/includes/classes/AdvancedSpecialsManager.php"
test -f "${root}/catalog/includes/classes/observers/auto.advanced_specials_manager.php"

grep -q "'pluginVersion' => 'v${version}'" "${root}/manifest.php"
grep -q "public string \$version = '${version}'" "${root}/Installer/ScriptedInstaller.php"
php -r "define('IS_ADMIN_FLAG', true); require '${root}/admin/includes/extra_datafiles/advanced_specials_manager.php'; require '${root}/filenames.php'; exit(FILENAME_ADVANCED_SPECIALS_MANAGER === 'advanced_specials_manager' ? 0 : 1);"
if grep -q "asm_h(zen_href_link" "${root}/admin/advanced_specials_manager.php"; then
  echo "Zen Cart URLs must not be escaped twice."
  exit 1
fi

find files -type f -name '*.php' -print0 | xargs -0 -n1 php -l
git diff --check
