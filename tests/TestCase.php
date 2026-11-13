<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Safety check. The tests wipe their database before every run (RefreshDatabase). They are meant to use
     * the in-memory SQLite database from phpunit.xml, but a cached config (bootstrap/cache/config.php, made by
     * "php artisan config:cache" or "php artisan optimize") overrides that and points them at the REAL database.
     * So refuse to run instead of erasing real data.
     */
    public function createApplication()
    {
        if (file_exists(__DIR__ . '/../bootstrap/cache/config.php')) {
            throw new \RuntimeException(
                'Tests stopped: the config is cached, so they would run against (and erase) the real database. '
                . 'Run "php artisan config:clear" first, then run the tests again.'
            );
        }

        // The app is created before the database is refreshed, so this check runs before anything is wiped
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        if ($connection !== 'sqlite' || $app['config']->get("database.connections.{$connection}.database") !== ':memory:') {
            throw new \RuntimeException(
                'Tests stopped: they are not using the in-memory SQLite test database (see phpunit.xml), '
                . 'so they could erase real data.'
            );
        }

        return $app;
    }
}
