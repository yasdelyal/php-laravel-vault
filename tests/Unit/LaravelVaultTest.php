<?php

namespace YaSdelyal\LaravelVault\Tests\Unit;

use YaSdelyal\LaravelVault\Drivers\HashiCorpVaultV1\HashiCorpVault;
use YaSdelyal\LaravelVault\LaravelVault;
use YaSdelyal\LaravelVault\Tests\TestCase;

class LaravelVaultTest extends TestCase
{
    public function testCanCreateVault()
    {
        $vault = $this->app->make('vault');
        $driver = $vault->connection('vault');
        $this->assertInstanceOf(LaravelVault::class, $vault);
        $this->assertInstanceOf(HashiCorpVault::class, $driver);
    }

    public function testVaultIsSingleton()
    {
        $vault = $this->app->make('vault');
        $vault2 = $this->app->make(LaravelVault::class);
        $this->assertSame($vault, $vault2);
    }

    public function canParsePatches()
    {
        $app = 'php-laravel-vault';
        $env = 'testing';
        config()->set('app.name', $app);
        config()->set('app.env', $env);

        $vault = $this->app->make('vault');
        $patches = $vault->getVarPatches();
        $this->assertSame("/secret/{$app}/{$env}", $patches[0]);
        $this->assertSame("/secret/{$app}/common", $patches[1]);
    }
}
