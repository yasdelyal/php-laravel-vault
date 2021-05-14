<?php

namespace YaSdelyal\LaravelVault\Drivers\HashiCorpVaultV1;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use YaSdelyal\LaravelVault\Exceptions\DriveException;
use YaSdelyal\LaravelVault\Models\BasicVariables;
use YaSdelyal\LaravelVault\Contracts\Driver;
use YaSdelyal\LaravelVault\Contracts\Variables;
use Vault\Client;

class HashiCorpVault implements Driver
{
    private $isAuthenticated = false;
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param  string $patch
     * @return Variables
     * @throws DriveException
     */
    public function patch(string $patch): Variables
    {
        $this->authOrFail();
        try {
            $response = $this->client->read($patch);
        } catch (ClientExceptionInterface $e) {
            throw new DriveException('Cannot read: ' . $e->getMessage());
        }
        return new BasicVariables($response->getData());
    }

    /**
     * @throws DriveException
     */
    private function authOrFail(): void
    {
        if (! $this->isAuthenticated) {
            try {
                $this->isAuthenticated = $this->client->authenticate();
            } catch (InvalidArgumentException | ClientExceptionInterface | Exception $e) {
                throw new DriveException('Cannot authenticate: ' . $e->getMessage());
            }
        }
        if (! $this->isAuthenticated) {
            throw new DriveException('Cannot authenticate');
        }
    }
}
