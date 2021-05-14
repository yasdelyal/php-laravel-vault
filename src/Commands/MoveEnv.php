<?php

namespace YaSdelyal\LaravelVault\Commands;

use Illuminate\Console\Command;
use YaSdelyal\LaravelVault\EnvFileService;
use YaSdelyal\LaravelVault\Exceptions\EnvFileException;

class MoveEnv extends Command
{
    protected $signature = 'vault:move-env
    {--rollback : try to rollback from backup}
    ';

    protected $description = 'Move .env.next to .env';

    public function handle(EnvFileService $envFileService): int
    {
        try {
            if ($this->option('rollback')) {
                $envFileService->rollbackFromBackup();
                $this->info('.env is rollback from backup');
            } else {
                $envFileService->moveNextEnvToCurrent();
                $this->info('.env.next moved into current');
            }
            return 0;
        } catch (EnvFileException $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
