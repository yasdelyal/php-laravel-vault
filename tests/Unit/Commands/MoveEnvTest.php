<?php

namespace YaSdelyal\LaravelVault\Tests\Unit\Commands;

use YaSdelyal\LaravelVault\EnvFileService;
use YaSdelyal\LaravelVault\Exceptions\EnvFileException;
use YaSdelyal\LaravelVault\Tests\TestCase;

class MoveEnvTest extends TestCase
{
    private $envService;

    public function setUp(): void
    {
        parent::setUp();
        $this->envService = $this->mock(EnvFileService::class);
    }

    public function testMove()
    {
        $this->envService->expects('moveNextEnvToCurrent')->once();
        $this->artisan('vault:move-env')
            ->expectsOutput('.env.next moved into current')
            ->assertExitCode(0);
    }

    public function testRollback()
    {
        $this->envService->expects('rollbackFromBackup')->once();
        $this->artisan('vault:move-env --rollback')
            ->expectsOutput('.env is rollback from backup')
            ->assertExitCode(0);
    }

    public function testError()
    {
        $message = 'Test message';
        $exception = new EnvFileException($message);
        $this->envService->expects('moveNextEnvToCurrent')->andThrowExceptions([$exception]);
        $this->artisan('vault:move-env')
            ->expectsOutput($message)
            ->assertExitCode(1);
    }
}
