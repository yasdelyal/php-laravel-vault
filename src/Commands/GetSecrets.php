<?php

namespace YaSdelyal\LaravelVault\Commands;

class GetSecrets extends AbstractSecretsCommand
{

    protected $signature = 'vault:get 
        {connection? : Set Vault connection from config}
        {--stdin : When present, command will be wait JSON config from stdin} 
        {--b64 : Work only with --stdin - when present, config must be base64 encoded}
        {--output=console : Where the vars will be output. Possible: console, nextEnv, currentEnv}
    ';

    protected $description = 'Get env from Vault';

    public function handle(): int
    {
        if ($this->option('stdin')) {
            $input = $this->secret('Pass config in JSON');
            $b64   = (bool) $this->option('b64');
            if (! $this->setConfigFromStdin($input, $b64)) {
                return 1;
            }
        }

        if (! $this->makeOutput($this->option('output'))) {
            return 1;
        }
        return 0;
    }

    private function setConfigFromStdin(string $input, bool $b64 = false): bool
    {
        if ($b64) {
            $input = base64_decode($input, true);
            if (!is_string($input)) {
                $this->error('Cannot parse base64 config from stdin');
                return false;
            }
        }
        $stdinConfig = json_decode($input, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $this->error('Cannot parse JSON config from stdin: ' . json_last_error_msg());
            return false;
        }
        if (! $this->mergeConfig($stdinConfig)) {
            return false;
        }
        return true;
    }
}
