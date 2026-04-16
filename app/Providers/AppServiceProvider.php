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
            $driver = $connection->getDriverName();

            // pgsql: single physical database, logical separation by schema.
            // sqlite: separate files, cross-file access via ATTACH DATABASE.
            // Overriding the reported database name with the schema lets the
            // library emit "schema"."table" refs that the DB resolves natively
            // (pgsql by default, sqlite after ATTACH).
            if (in_array($driver, ['pgsql', 'sqlite'], true) && ! empty($config['schema'])) {
                $connection->setDatabaseName($config['schema']);
            }

            if ($driver === 'sqlite') {
                foreach (config('database.connections') as $other) {
                    if (($other['driver'] ?? null) !== 'sqlite' || empty($other['schema'])) {
                        continue;
                    }
                    if ($other['schema'] === ($config['schema'] ?? null)) {
                        continue;
                    }
                    $path = base_path($other['database']);
                    $connection->statement("ATTACH DATABASE '{$path}' AS {$other['schema']}");
                }
            }
        });
    }
}
