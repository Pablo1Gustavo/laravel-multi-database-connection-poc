<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTruncation;

    protected array $connectionsToTruncate = ['primary', 'secondary'];

    protected function beforeTruncatingDatabase(): void
    {
        if (RefreshDatabaseState::$migrated) {
            return;
        }

        foreach ($this->connectionsToTruncate as $connection) {
            $this->artisan('db:wipe', [
                '--database' => $connection,
                '--force' => true,
            ]);
        }

        $this->artisan('migrate', ['--force' => true]);

        RefreshDatabaseState::$migrated = true;
    }
}
