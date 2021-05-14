<?php

namespace YaSdelyal\LaravelVault\Tests;

use YaSdelyal\LaravelVault\LaravelVaultServiceProvider;

/**
 * Class TestCase
 * @todo clean tmp folder after all tests
 * @package YaSdelyal\LaravelVault\Tests
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        //TODO work with filesystem from adapter, not direct. Cons: Filesystem::class has not isWritable
        $this->app->useEnvironmentPath(implode(DIRECTORY_SEPARATOR, [__DIR__, 'tmp']));
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelVaultServiceProvider::class
        ];
    }
}
