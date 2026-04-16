<?php

use Illuminate\Support\Str;
use Pdo\Mysql;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'primary'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | "primary" and "secondary" are the two connections used for cross-database
    | testing. Switch between MySQL and PostgreSQL by changing the driver
    | and host env vars (DB_PRIMARY_DRIVER, DB_SECONDARY_DRIVER, etc.).
    |
    */

    'connections' => [

        'primary' => [
            'driver' => env('DB_PRIMARY_DRIVER', 'mysql'),
            'host' => env('DB_PRIMARY_HOST', 'mysql'),
            'port' => env('DB_PRIMARY_PORT', '3306'),
            'database' => env('DB_PRIMARY_DATABASE', 'laravel_primary'),
            'username' => env('DB_PRIMARY_USERNAME', 'sail'),
            'password' => env('DB_PRIMARY_PASSWORD', 'password'),
            'unix_socket' => env('DB_PRIMARY_SOCKET', ''),
            'charset' => env('DB_PRIMARY_CHARSET', 'utf8mb4'),
            'collation' => env('DB_PRIMARY_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PRIMARY_PREFIX', ''),
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'schema' => env('DB_PRIMARY_SCHEMA'),
            'search_path' => env('DB_PRIMARY_SCHEMA', 'public'),
            'sslmode' => env('DB_PRIMARY_SSLMODE', 'prefer'),
            'foreign_key_constraints' => env('DB_PRIMARY_FOREIGN_KEYS', true),
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'secondary' => [
            'driver' => env('DB_SECONDARY_DRIVER', 'mysql'),
            'host' => env('DB_SECONDARY_HOST', 'mysql'),
            'port' => env('DB_SECONDARY_PORT', '3306'),
            'database' => env('DB_SECONDARY_DATABASE', 'laravel_secondary'),
            'username' => env('DB_SECONDARY_USERNAME', 'sail'),
            'password' => env('DB_SECONDARY_PASSWORD', 'password'),
            'unix_socket' => env('DB_SECONDARY_SOCKET', ''),
            'charset' => env('DB_SECONDARY_CHARSET', 'utf8mb4'),
            'collation' => env('DB_SECONDARY_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_SECONDARY_PREFIX', ''),
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'schema' => env('DB_SECONDARY_SCHEMA'),
            'search_path' => env('DB_SECONDARY_SCHEMA', 'public'),
            'sslmode' => env('DB_SECONDARY_SSLMODE', 'prefer'),
            'foreign_key_constraints' => env('DB_SECONDARY_FOREIGN_KEYS', true),
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];
