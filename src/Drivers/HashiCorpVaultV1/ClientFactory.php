<?php

namespace YaSdelyal\LaravelVault\Drivers\HashiCorpVaultV1;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Vault\AuthenticationStrategies\AppRoleAuthenticationStrategy;
use Vault\AuthenticationStrategies\AuthenticationStrategy;
use Vault\AuthenticationStrategies\TokenAuthenticationStrategy;
use Vault\Client;
use YaSdelyal\LaravelVault\Exceptions\DriveException;

class ClientFactory
{
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;
    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }


    /**
     * @throws DriveException
     */
    public function create(string $host, string $port, array $config): Client
    {
        $client = new Client(
            new Uri($host . ':' . $port),
            $this->client,
            $this->requestFactory,
            $this->streamFactory
        );
        $client->setAuthenticationStrategy(
            $this->createAuthenticationStrategy($config)
        );
        return $client;
    }

    /**
     * @throws DriveException
     */
    public function createAuthenticationStrategy(array $config): AuthenticationStrategy
    {
        //TODO AbstractPathAuthenticationStrategy
        //TODO authStrategy param in config
        if (isset($config['token'])) {
            return new TokenAuthenticationStrategy($config['token']);
        } elseif (isset($config['role_id']) and ($config['secret_id'])) {
            return new AppRoleAuthenticationStrategy(
                $config['role_id'],
                $config['secret_id'],
                $config['role_name'] ?? 'approle'
            );
        } else {
            throw new DriveException('No supported AuthenticationStrategy');
        }
    }
}
