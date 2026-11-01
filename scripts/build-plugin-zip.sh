#!/usr/bin/env bash
set -euo pipefail

plugin_slug="as-atomic-content"
project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
dist_dir="$project_dir/dist"

mkdir -p "$dist_dir"
rm -rf "$dist_dir/$plugin_slug"
rm -f "$dist_dir/$plugin_slug.zip" "$project_dir/$plugin_slug.zip"
cp -R "$project_dir/$plugin_slug" "$dist_dir/$plugin_slug"
find "$dist_dir/$plugin_slug" -name '.DS_Store' -delete
rm -rf "$dist_dir/$plugin_slug/node_modules"

(
	cd "$dist_dir"
	zip -qr "$plugin_slug.zip" "$plugin_slug"
)
cp "$dist_dir/$plugin_slug.zip" "$project_dir/$plugin_slug.zip"
