<?php

namespace YaSdelyal\LaravelVault\Commands;

use Illuminate\Console\Command;
use YaSdelyal\LaravelVault\Contracts\Variables;
use YaSdelyal\LaravelVault\EnvFileService;
use YaSdelyal\LaravelVault\Exceptions\VaultException;
use YaSdelyal\LaravelVault\LaravelVault;

abstract class AbstractSecretsCommand extends Command
{
    /**
     * @var LaravelVault
     */
    protected $vault;
    /**
     * @var EnvFileService
     */
    protected $envFileService;

    public function __construct()
    {
        parent::__construct();
        $this->vault = app(LaravelVault::class);
        $this->envFileService = app(EnvFileService::class);
    }

    protected function mergeConfig(array $config): bool
    {
        $currentConfig = config('vault');
        $config = array_replace_recursive($currentConfig, $config);
        if (! $config) {
            $this->error('Cannot merge config');
            return false;
        }
        config()->set('vault', $config);
        return true;
    }


    protected function makeOutput(string $format): bool
    {
        if (! $vars = $this->getVars()) {
            return false;
        }
        $method = $format . 'Format';
        if (! method_exists($this, $method)) {
            $this->error('Unsupported output format' . $format);
            return false;
        }
        return $this->{$method}($vars);
    }

    private function consoleFormat(Variables $variables): bool
    {
        $formatted = [];
        foreach ($variables->toArray() as $key => $value) {
            $formatted[] = [
                'key' => $key,
                'value' => $value
            ];
        }
        $this->table(['Key', 'Value'], $formatted);
        return true;
    }

    private function nextEnvFormat(Variables $variables): bool
    {
        try {
            $this->envFileService->saveNextEnv($variables);
            $this->info('Env saved to next');
            return true;
        } catch (VaultException $e) {
            $this->error($e->getMessage());
            return false;
        }
    }

    private function currentEnvFormat(Variables $variables): bool
    {
        try {
            $this->envFileService->saveCurrentEnv($variables);
            $this->info('Env saved to current');
            return true;
        } catch (VaultException $e) {
            $this->error($e->getMessage());
            return false;
        }
    }

    public function getVars(string $connection = null): ?Variables
    {
        $vars = $this->vault->get($connection);
        if ($vars->isEmpty()) {
            $this->error('Vars is empty, possible errors');
            return null;
        }
        return $vars;
    }
}
