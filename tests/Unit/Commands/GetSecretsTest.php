<?php

namespace YaSdelyal\LaravelVault\Tests\Unit\Commands;

use Illuminate\Foundation\Testing\WithFaker;
use YaSdelyal\LaravelVault\EnvFileService;
use YaSdelyal\LaravelVault\LaravelVault;
use YaSdelyal\LaravelVault\Models\BasicVariables;
use YaSdelyal\LaravelVault\Tests\TestCase;

class GetSecretsTest extends TestCase
{
    use WithFaker;

    protected $vars;
    protected $vault;
    protected $envService;
    protected $startedConfig;

    public function setUp(): void
    {
        parent::setUp();
        $this->vars = new BasicVariables([
            'test_var_1' => 'test_value_1',
            'test_var_2' => 'test_value_2',
        ]);
        $this->vault = $this->mock(LaravelVault::class);
        $this->envService = $this->mock(EnvFileService::class);
        $this->startedConfig = $this->app['config']['vault'];
    }

    public function vaultMustBeCalled()
    {
        $this->vault
            ->expects('get')
            ->andReturn($this->vars);
    }

    public function vaultMustNotBeCalled()
    {
        $this->vault->shouldNotHaveReceived('get');
    }

    public function testDefaultGet()
    {
        $this->vaultMustBeCalled();
        $this->artisan('vault:get')
            ->assertExitCode(0);
    }

    public function testNextEnv()
    {
        $this->envService->expects('saveNextEnv')->once();
        $this->vaultMustBeCalled();

        $this->artisan('vault:get --output=nextEnv')
            ->assertExitCode(0);
    }

    public function testCurrentEnv()
    {
        $this->envService->expects('saveCurrentEnv')->once();
        $this->vaultMustBeCalled();

        $this->artisan('vault:get --output=currentEnv')
            ->assertExitCode(0);
    }

    public function testEmptyGet()
    {
        $this->vars = new BasicVariables([]);
        $this->vaultMustBeCalled();
        $this->artisan('vault:get')
            ->expectsOutput('Vars is empty, possible errors')
            ->assertExitCode(1);
    }

    public function testWrongJson()
    {
        $this->vaultMustNotBeCalled();
        $this->artisan('vault:get --stdin')
            ->expectsQuestion('Pass config in JSON', '{"wrong_json": "eff",}')
            ->expectsOutput('Cannot parse JSON config from stdin: Syntax error')
            ->assertExitCode(1);
    }

    public function testWrongB64()
    {
        $this->vaultMustNotBeCalled();
        $this->artisan('vault:get --stdin --b64')
            ->expectsQuestion('Pass config in JSON', 'wfwefjuweiufbwefj')
            ->expectsOutput('Cannot parse base64 config from stdin')
            ->assertExitCode(1);
    }

    public function testGoodJson()
    {
        $newConfig = $this->getChangedConfig();
        $this->vaultMustBeCalled();
        $this->artisan('vault:get --stdin')
            ->expectsQuestion('Pass config in JSON', json_encode($newConfig))
            ->assertExitCode(0);
        $this->assertConfigChanged($newConfig);
    }

    public function testGoodB64()
    {
        $newConfig = $this->getChangedConfig();
        $this->vaultMustBeCalled();
        $this->artisan('vault:get --stdin --b64')
            ->expectsQuestion('Pass config in JSON', base64_encode(json_encode($newConfig)))
            ->assertExitCode(0);
        $this->assertConfigChanged($newConfig);
    }

    public function getChangedConfig(): array
    {
        return [
            'vars' => [
                'patch_variables' => [
                    'app' => $this->faker->word,
                    'new_var' => $this->faker->word
                ]
            ]
        ];
    }

    public function assertConfigChanged(array $newConfig)
    {
        $currentConfig = $this->app['config']['vault'];
        $this->assertSame(
            $currentConfig['vars']['patch_variables']['app'],
            $newConfig['vars']['patch_variables']['app']
        );
        $this->assertNotSame(
            $this->startedConfig['vars']['patch_variables']['app'],
            $newConfig['vars']['patch_variables']['app']
        );
    }
}
