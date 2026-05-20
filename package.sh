#!/bin/bash
#
# Package Map Blocks plugin for distribution.
# Creates a zip file ready for WordPress plugin installation.
#
# Usage: ./package.sh

set -euo pipefail

PLUGIN_SLUG="map-blocks"
VERSION=$(grep -m1 "Version:" map-blocks.php | sed 's/.*Version:[[:space:]]*//')

# Ensure we're in the repo root
cd "$(dirname "$0")"

echo "Packaging ${PLUGIN_SLUG} v${VERSION}..."

# Prepare dist directory
rm -rf dist
mkdir -p "dist/${PLUGIN_SLUG}"

# Copy plugin files into the slug folder
cp -r build lib map-blocks.php uninstall.php readme.txt LICENSE CHANGELOG.md "dist/${PLUGIN_SLUG}/"

# Remove unwanted files
find "dist/${PLUGIN_SLUG}" -name '.DS_Store' -o -name '*.map' | xargs rm -f

# Create zip from within dist so the zip root is the slug folder
cd dist
zip -r "${PLUGIN_SLUG}-${VERSION}.zip" "${PLUGIN_SLUG}/"
rm -rf "${PLUGIN_SLUG}"

echo "Created dist/${PLUGIN_SLUG}-${VERSION}.zip"
