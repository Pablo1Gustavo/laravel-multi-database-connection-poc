<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected $connectionsToTransact = ['primary', 'secondary'];

    protected function setUpTraits(): array
    {
        foreach ($this->connectionsToTransact as $connection) {
            DB::connection($connection)->statement('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
        }

        return parent::setUpTraits();
    }

    protected function migrateDatabases(): void
    {
        foreach ($this->connectionsToTransact as $connection) {
            $this->artisan('db:wipe', [
                '--database' => $connection,
                '--force' => true,
            ]);
        }

        $this->artisan('migrate', ['--force' => true]);
    }
}
