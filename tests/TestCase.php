<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['primary', 'secondary'];

    protected function setUpTraits(): array
    {
        foreach ($this->connectionsToTransact as $connection) {
            DB::connection($connection)->statement('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
        }

        return parent::setUpTraits();
    }
}
