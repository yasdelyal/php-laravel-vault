<?php

namespace YaSdelyal\LaravelVault\Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use YaSdelyal\LaravelVault\EnvFileService;
use YaSdelyal\LaravelVault\EnvValidator;
use YaSdelyal\LaravelVault\Models\BasicVariables;
use YaSdelyal\LaravelVault\Tests\TestCase;

class EnvFileServiceTest extends TestCase
{
    use WithFaker;

    protected $vars;
    protected $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->vars = new BasicVariables([
           'var1' => $this->faker->word,
           'var2' => $this->faker->word,
        ]);
        $mockValidator = $this->createMock(EnvValidator::class);
        $this->service = new EnvFileService($this->app, $mockValidator);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $stubs = [
            $this->service->getCurrentFile(),
            $this->service->getNextFile(),
            $this->service->getBackupFile(),
        ];
        foreach ($stubs as $stub) {
            if (is_file($stub)) {
                unlink($stub);
            }
        }
    }

    public function testSingleton()
    {
        $obj1 = $this->app->make(EnvFileService::class);
        $obj2 = $this->app->make(EnvFileService::class);
        $this->assertSame($obj1, $obj2);
    }

    public function testSaveNextEnv()
    {
        $this->service->saveNextEnv($this->vars);
        $this->assertFileSameAsVars($this->service->getNextFile());
    }

    public function testSaveCurrent()
    {
        $this->service->saveCurrentEnv($this->vars);
        $this->assertFileSameAsVars($this->service->getCurrentFile());
    }

    private function assertFileExist(string $patch)
    {
        $this->assertTrue(is_file($patch));
    }

    private function assertFileSameAsVars($patch)
    {
        $this->assertFileExist($patch);
        $contents = file_get_contents($patch);
        $this->assertSame($this->vars->toEnv(), $contents);
    }
}
