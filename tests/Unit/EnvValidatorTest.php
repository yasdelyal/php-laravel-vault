<?php

namespace YaSdelyal\LaravelVault\Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use YaSdelyal\LaravelVault\EnvValidator;
use YaSdelyal\LaravelVault\Exceptions\EnvValidationException;
use YaSdelyal\LaravelVault\Models\BasicVariables;
use YaSdelyal\LaravelVault\Tests\TestCase;

class EnvValidatorTest extends TestCase
{
    use WithFaker;

    protected $vars;
    protected $exampleFile;
    protected $service;

    public function setUp(): void
    {
        parent::setUp();
        $exampleFileName = $this->app->environmentFile() . '.example';
        $this->exampleFile = $this->app->environmentPath() . DIRECTORY_SEPARATOR . $exampleFileName;
        $this->vars = new BasicVariables([
            'var1' => $this->faker->word,
            'var2' => $this->faker->word,
        ]);
        file_put_contents($this->exampleFile, $this->vars->toEnv());
        $this->service = new EnvValidator($this->app->environmentPath(), $exampleFileName);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        if (is_file($this->exampleFile)) {
            unlink($this->exampleFile);
        }
    }

    public function testSingleton()
    {
        $obj1 = $this->app->make(EnvValidator::class);
        $obj2 = $this->app->make(EnvValidator::class);
        $this->assertSame($obj1, $obj2);
    }

    public function testPassValidation()
    {
        $newVars = (new BasicVariables(['new_var' => 'tatata']))->merge($this->vars);
        $this->assertTrue($newVars->has('new_var'));
        $this->assertFalse($this->vars->has('new_var'));
        $this->assertTrue(
            $this->service->validate($newVars)
        );
    }

    public function testPassStrictValidation()
    {
        $this->assertTrue(
            $this->service->validate($this->vars, true)
        );
    }

    public function testNotPassStrictValidation()
    {
        $newVars = (new BasicVariables(['new_var' => 'tatata']))->merge($this->vars);
        $this->assertTrue($newVars->has('new_var'));
        $this->assertFalse($this->vars->has('new_var'));
        $this->expectException(EnvValidationException::class);
        $this->service->validate($newVars, true);
    }

    public function testNotPassValidation()
    {
        $newVars = new BasicVariables(['new_var' => 'tatata']);
        $this->assertTrue($newVars->has('new_var'));
        $this->assertFalse($this->vars->has('new_var'));
        $this->expectException(EnvValidationException::class);
        $this->service->validate($newVars);
    }
}
