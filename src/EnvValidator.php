<?php

namespace YaSdelyal\LaravelVault;

use Dotenv\Dotenv;
use YaSdelyal\LaravelVault\Contracts\Variables;
use YaSdelyal\LaravelVault\Exceptions\EnvValidationException;

class EnvValidator
{
    private $dotenv;
    public function __construct(string $patch, string $name)
    {
        $this->dotenv = Dotenv::create($patch, $name);
    }

    /**
     * @throws EnvValidationException
     */
    public function validate(Variables $variables, bool $strict = false): bool
    {
        $this->dotenv->load();
        if ($diff = array_diff($this->dotenv->getEnvironmentVariableNames(), $variables->keys())) {
            throw new EnvValidationException('Env has not keys: ' . implode(',', $diff));
        }
        if ($strict and $diff = array_diff($variables->keys(), $this->dotenv->getEnvironmentVariableNames())) {
            throw new EnvValidationException('Env has excess keys: ' . implode(',', $diff));
        }
        return true;
    }
}
