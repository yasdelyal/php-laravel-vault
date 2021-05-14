<?php

namespace YaSdelyal\LaravelVault;

use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Illuminate\Support\ServiceProvider;
use YaSdelyal\LaravelVault\Commands\CiSecrets;
use YaSdelyal\LaravelVault\Commands\GetSecrets;
use YaSdelyal\LaravelVault\Commands\MoveEnv;
use YaSdelyal\LaravelVault\Drivers\HashiCorpVaultV1\ClientFactory;
use YaSdelyal\LaravelVault\Drivers\HashiCorpVaultV1\HashiCorpVault;
use YaSdelyal\LaravelVault\Facades\Vault;

class LaravelVaultServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                __DIR__ . '/../config/vault.php' => config_path('vault.php'),
                ],
                'config'
            );
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/vault.php', 'vault');

        $this->app->singleton(
            LaravelVault::class,
            function ($app) {
                return new LaravelVault($app);
            }
        );

        $this->app->singleton(
            EnvValidator::class,
            function () {
                return new EnvValidator(
                    $this->app['config']['vault.vars.validation.example_file_patch']
                        ?? $this->app->environmentPath(),
                    $this->app['config']['vault.vars.validation.example_file_name']
                        ?? $this->app->environmentFile() . '.example'
                );
            }
        );

        $this->app->singleton(
            EnvFileService::class,
            function ($app) {
                return new EnvFileService($app, $app->get(EnvValidator::class));
            }
        );

        $this->app->alias(LaravelVault::class, 'vault');

        $this->registerCommands();
        $this->registerDriver();
    }

    private function registerCommands()
    {
        $this->commands(
            [
            GetSecrets::class,
            CiSecrets::class,
            MoveEnv::class,
            ]
        );
    }

    private function registerDriver()
    {
        Vault::extend(
            'hashicorp_vault_v1',
            function ($app, $name, $config) {
                $factory = new ClientFactory(
                    new Client(),
                    new RequestFactory(),
                    new StreamFactory()
                );
                $client = $factory->create($config['host'], $config['port'], $config);
                return new HashiCorpVault($client);
            }
        );
    }
}
