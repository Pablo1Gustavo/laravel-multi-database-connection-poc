#!/usr/bin/env bash
set -euo pipefail

driver="${1:-}"
script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

case "$driver" in
    mysql|pgsql) ;;
    *)
        echo "Usage: $0 {mysql|pgsql}" >&2
        exit 1
        ;;
esac

src="$script_dir/phpunit_${driver}.xml"
dest="$script_dir/phpunit.xml"

if [[ ! -f "$src" ]]; then
    echo "Missing source file: $src" >&2
    exit 1
fi

cp "$src" "$dest"
echo "phpunit.xml -> $driver"
