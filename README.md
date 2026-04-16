# Laravel Multi-Database Connection POC

Proof of concept exercising cross-database Eloquent JOINs against three drivers
(MySQL, PostgreSQL, SQLite) using a custom branch of
[`kirschbaum-development/eloquent-power-joins`](https://github.com/kirschbaum-development/eloquent-power-joins).

The branch under test is `dev-multiple-database-connections-support`, which
adds a `ConnectionAwareTable` helper that emits qualified table/column refs
(`"db"."table"."col"` or aliased `"prefix_table" as "table"`) when the related
model lives on a different connection from the base query.

## Domain

Two logical databases, two Laravel connections (`primary`, `secondary`):

| Connection  | Tables                                                     |
| ----------- | ---------------------------------------------------------- |
| `primary`   | `authors`, `author_tag`, `labelables`                      |
| `secondary` | `articles`, `comments`, `profiles`, `tags`, `labels`, `stickers` |

Relationships span connections (e.g. `Author hasMany Articles`,
`Author belongsToMany Tags through author_tag`), so most controller queries
require cross-connection JOINs.

## Switching driver

`./switch-db.sh {mysql|pgsql|sqlite}` rewrites the relevant `DB_*` keys in
`.env` for the chosen driver. The script is the only supported way to switch
— `phpunit.xml` no longer overrides connection envs.

```sh
./switch-db.sh mysql   # mysql 8.4 via sail
./switch-db.sh pgsql   # postgres 18 via sail
./switch-db.sh sqlite  # local files in database/
```

For mysql/pgsql, start the database services first:

```sh
./vendor/bin/sail up -d mysql pgsql
```

SQLite needs no service — the script creates the files locally.

## Running tests

```sh
php artisan test
```

The base `Tests\TestCase` uses `DatabaseTruncation` across both connections.
On the first test in a run it wipes and re-migrates both databases via
`db:wipe` + `migrate`; subsequent tests truncate.

## Architecture decisions

### Why one server per driver

Cross-connection JOINs have to execute on a single server: MySQL needs the
two databases on the same MySQL instance; PostgreSQL needs them on the same
cluster; SQLite needs the other file `ATTACH`ed. The compose file therefore
runs `mysql` and `pgsql` services that each host both `laravel_primary` and
`laravel_secondary`.

### Per-driver layout

| Driver | Layout                                                                |
| ------ | --------------------------------------------------------------------- |
| mysql  | Two physical databases (`laravel_primary`, `laravel_secondary`) on one server. Cross-DB refs use `db.table` syntax that MySQL resolves natively. |
| pgsql  | One physical database (`laravel_primary`) with two schemas (`laravel_primary`, `laravel_secondary`). Cross-schema refs use `schema.table` syntax. |
| sqlite | Two files (`database/laravel_primary.sqlite`, `database/laravel_secondary.sqlite`). Cross-file refs use `schema.table` syntax made available via `ATTACH DATABASE`. |

The `DB_*_SCHEMA` env vars hold the schema/attach name for pgsql and sqlite.
For mysql the schema name and the database name are the same thing, so
`DB_*_SCHEMA` is left unset.

### `AppServiceProvider` connection listener

`app/Providers/AppServiceProvider.php` listens on `ConnectionEstablished` and
performs two driver-specific tweaks:

1. **Override the reported database name** for pgsql and sqlite so the
   library can emit `"<schema>"."<table>"` qualifiers on cross-connection
   refs. Laravel's pgsql driver normally reports the physical database name;
   we replace it with the schema, since `ConnectionAwareTable::qualifiedDatabaseName()`
   uses whatever the connection reports.

2. **`ATTACH DATABASE`** for sqlite: when one sqlite connection opens, every
   other sqlite connection's file is attached under its `schema` name. After
   that, queries on this connection can reference the other file as
   `<schema>.<table>` exactly like MySQL/Postgres.

Doing this in a `ConnectionEstablished` listener keeps the test setup minimal
(`Tests\TestCase` doesn't have to know which driver is active) and ensures
ATTACH runs once per PDO open, including for connections Laravel creates
internally for migrations or test truncation.

### `config/database.php`

Both connections share the same shape and read every value from `DB_*` env
vars, so the switch script only has to mutate `.env`. The `schema` key is
present for both connections so pgsql/sqlite paths in the listener can read
it through `$connection->getConfig()`.

### `tests/TestCase.php`

`DatabaseTruncation` is configured with `connectionsToTruncate = ['primary',
'secondary']` so both databases are truncated between tests. The
`beforeTruncatingDatabase()` hook gates a one-shot `db:wipe` + `migrate`
behind `RefreshDatabaseState::$migrated`, which mirrors what `RefreshDatabase`
does internally but works across multiple connections without the per-test
transaction wrapper.

