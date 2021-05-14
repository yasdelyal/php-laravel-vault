<?php

namespace YaSdelyal\LaravelVault\Commands;

class CiSecrets extends AbstractSecretsCommand
{

    protected $signature = 'vault:ci
        {token : Vault token}
        {--connection : Set Vault connection, if not - default was used}
        {--host : Vault host}
        {--app : app patch variable}
        {--env : env patch variable}
    ';

    protected $description = 'Shorthand command for using in CI - uses default connection';

    public function handle(): int
    {
        //собираем конфиг
        $connection = $this->argument('connection') ?? config('vault.default_connection');
        if (! $connection) {
            $this->error('No connection or default connection was specified');
            return 1;
        }
        $config = [];
        $config['default_connection'] = $connection;
        $config['connections'][$connection]['token'] = $this->argument('token');
        if ($this->option('host')) {
            $config['connections'][$connection]['host'] = $this->option('host');
        }
        if ($this->option('app')) {
            $config['vars']['patch_variables']['app'] = $this->option('app');
        }
        if ($this->option('env')) {
            $config['vars']['patch_variables']['env'] = $this->option('env');
        }
        if (! $this->mergeConfig($config)) {
            return 1;
        }

        if (! $this->makeOutput('currentEnv')) {
            return 1;
        }
        return 0;
    }
}
