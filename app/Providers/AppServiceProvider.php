<?php

namespace App\Providers;

use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (ConnectionEstablished $event): void {
            $connection = $event->connection;
            $config = $connection->getConfig();

            // For pgsql we keep a single physical database and separate the two
            // logical "databases" by schema. Overriding the connection's reported
            // database name with the schema lets cross-connection joins emit
            // "schema"."table", which PostgreSQL resolves natively.
            if ($connection->getDriverName() === 'pgsql' && ! empty($config['schema'])) {
                $connection->setDatabaseName($config['schema']);
            }
        });
    }
}
