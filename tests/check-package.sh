#!/usr/bin/env bash
set -euo pipefail

version="1.0.0"
root="files/zc_plugins/AdvancedSpecialsManager/v${version}"

test -f "${root}/manifest.php"
test -f "${root}/Installer/ScriptedInstaller.php"
test -f "${root}/admin/advanced_specials_manager.php"
test -f "${root}/admin/includes/classes/AdvancedSpecialsManager.php"

grep -q "'pluginVersion' => 'v${version}'" "${root}/manifest.php"
grep -q "public string \$version = '${version}'" "${root}/Installer/ScriptedInstaller.php"

find files -type f -name '*.php' -print0 | xargs -0 -n1 php -l
git diff --check
