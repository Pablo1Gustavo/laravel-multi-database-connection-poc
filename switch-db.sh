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

env_file="$script_dir/.env"

if [[ ! -f "$env_file" ]]; then
    echo ".env not found" >&2
    exit 1
fi

set_env() {
    local key="$1" value="$2"
    if grep -qE "^#?[[:space:]]*${key}=" "$env_file"; then
        sed -i -E "s|^#?[[:space:]]*${key}=.*|${key}=${value}|" "$env_file"
    else
        printf '%s=%s\n' "$key" "$value" >> "$env_file"
    fi
}

comment_env() {
    local key="$1"
    sed -i -E "s|^${key}=(.*)|# ${key}=\\1|" "$env_file"
}

if [[ "$driver" == "pgsql" ]]; then
    set_env DB_PRIMARY_DRIVER pgsql
    set_env DB_PRIMARY_PORT 5432
    set_env DB_PRIMARY_CHARSET utf8
    set_env DB_SECONDARY_DRIVER pgsql
    set_env DB_SECONDARY_PORT 5432
    set_env DB_SECONDARY_CHARSET utf8
    set_env DB_SECONDARY_DATABASE laravel_primary
    set_env DB_PRIMARY_SCHEMA laravel_primary
    set_env DB_SECONDARY_SCHEMA laravel_secondary
    comment_env DB_PRIMARY_COLLATION
    comment_env DB_SECONDARY_COLLATION
else
    set_env DB_PRIMARY_DRIVER mysql
    set_env DB_PRIMARY_PORT 3306
    set_env DB_PRIMARY_CHARSET utf8mb4
    set_env DB_PRIMARY_COLLATION utf8mb4_unicode_ci
    set_env DB_SECONDARY_DRIVER mysql
    set_env DB_SECONDARY_PORT 3306
    set_env DB_SECONDARY_CHARSET utf8mb4
    set_env DB_SECONDARY_COLLATION utf8mb4_unicode_ci
    set_env DB_SECONDARY_DATABASE laravel_secondary
    comment_env DB_PRIMARY_SCHEMA
    comment_env DB_SECONDARY_SCHEMA
fi

echo ".env -> $driver"
