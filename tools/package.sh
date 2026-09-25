#!/bin/sh
# Builds dist/workflow-dashboard.zip with only the files the server needs.
set -e
cd "$(dirname "$0")/.."
rm -rf dist && mkdir -p dist
zip -qr dist/workflow-dashboard.zip \
  index.php install.php config.sample.php .htaccess \
  app pages partials assets database storage/.htaccess \
  -x 'config.php' 'storage/sessions/*'
echo "dist/workflow-dashboard.zip ($(du -h dist/workflow-dashboard.zip | cut -f1))"
