<?php

namespace YaSdelyal\LaravelVault;

use YaSdelyal\LaravelVault\Contracts\Variables;
use YaSdelyal\LaravelVault\Exceptions\EnvFileException;
use Illuminate\Foundation\Application;
use YaSdelyal\LaravelVault\Exceptions\VaultException;

class EnvFileService
{
    public const NEXT = 'next';
    public const BAK  = 'backup';
    private $app;
    private $currentFile;
    private $nextFile;

    private $backupFile;
    private $validator;

    public function __construct(Application $app, EnvValidator $validator)
    {
        $this->app = $app;
        $this->validator = $validator;
        $this->currentFile = $this->getEnvFilePatch();
        $this->nextFile = $this->getEnvFilePatch(self::NEXT);
        $this->backupFile = $this->getEnvFilePatch(self::BAK);
    }

    /**
     * @throws VaultException
     */
    public function saveCurrentEnv(Variables $variables): void
    {
        $this->saveNextEnv($variables);
        $this->moveNextEnvToCurrent();
    }

    /**
     * @throws VaultException
     */
    public function saveNextEnv(Variables $variables): void
    {
        $this->checkOrFail($this->nextFile);
        if (! file_put_contents($this->nextFile, $variables->toEnv())) {
            throw new EnvFileException('Cannot write to file ' . $this->nextFile);
        }
        try {
            $this->validator->validate($variables);
        } catch (VaultException $exception) {
            unlink($this->nextFile);
            throw $exception;
        }
    }

    /**
     * @throws EnvFileException
     */
    public function backupCurrentEnv()
    {
        if (! $this->isFile($this->currentFile)) {
            return;
        }
        $this->checkOrFail($this->backupFile);
        if (! copy($this->currentFile, $this->backupFile)) {
            throw new EnvFileException('Cannot backup current .env to  ' . $this->backupFile);
        }
    }

    /**
     * @throws EnvFileException
     */
    public function moveNextEnvToCurrent()
    {
        if (! $this->isFile($this->nextFile)) {
            throw new EnvFileException('Next file not created ' . $this->nextFile);
        }
        $this->backupCurrentEnv();
        if (! copy($this->nextFile, $this->currentFile)) {
            throw new EnvFileException('Cannot move next .env to  ' . $this->currentFile);
        }
        unlink($this->nextFile);
    }


    private function isFile(string $patch): bool
    {
        return is_file($patch) and is_readable($patch);
    }

    /**
     * @throws EnvFileException
     */
    private function checkOrFail(string $patch): void
    {
        if (! $this->isFile($patch) and ! touch($patch)) {
            throw new EnvFileException('Cannot create ' . $patch);
        }
        if (! is_writable($patch)) {
            throw new EnvFileException('File is not writeable ' . $patch);
        }
    }

    private function getEnvFilePatch(string $postfix = null): string
    {
        return $this->app->environmentPath() . DIRECTORY_SEPARATOR . $this->getEnvFileName($postfix);
    }

    private function getEnvFileName(string $postfix = null): string
    {
        return $this->app->environmentFile() . ($postfix ? '.' . $postfix : '');
    }

    /**
     * @throws EnvFileException
     */
    public function rollbackFromBackup()
    {
        if (! $this->isFile($this->backupFile)) {
            throw new EnvFileException('Backup file not created ' . $this->backupFile);
        }
        $this->checkOrFail($this->currentFile);
        if (! copy($this->backupFile, $this->currentFile)) {
            throw new EnvFileException('Cannot copy backup to .env: ' . $this->currentFile);
        }
    }

    /**
     * @return string
     */
    public function getCurrentFile(): string
    {
        return $this->currentFile;
    }

    /**
     * @return string
     */
    public function getNextFile(): string
    {
        return $this->nextFile;
    }

    /**
     * @return string
     */
    public function getBackupFile(): string
    {
        return $this->backupFile;
    }
}
