<?php

namespace Wexample\SymfonyRemoteAi\Service;

use Psr\Container\ContainerInterface;
use Wexample\PhpRemote\Class\RemoteRegistry;
use Wexample\PhpRemote\Class\RemoteStatus;
use Wexample\PhpRemoteAi\Interface\ModelProviderClientInterface;
use Wexample\SymfonyRemoteAi\DependencyInjection\WexampleSymfonyRemoteAiExtension;

/**
 * The AI providers of the configuration, by key, each with its client: what a
 * discovery asks which models there are.
 */
class RemoteAiProviderRegistry
{
    /**
     * @param array<string, string> $types the type of each provider, by key
     */
    public function __construct(
        private readonly ContainerInterface $clients,
        private readonly array $types,
        private readonly RemoteRegistry $remotes,
    ) {
    }

    /** @return list<string> */
    public function getKeys(): array
    {
        return array_keys($this->types);
    }

    public function has(string $key): bool
    {
        return isset($this->types[$key]);
    }

    public function getType(string $key): string
    {
        return $this->types[$key] ?? throw new \InvalidArgumentException('No AI provider configured under '.$key);
    }

    /**
     * The provider as symfony-remote sees it: unconfigured when its url or its
     * key is missing — checked before anything is asked of it —, otherwise up
     * or down from one request.
     */
    public function getStatus(string $key): RemoteStatus
    {
        $this->getType($key);

        return $this->remotes->check(WexampleSymfonyRemoteAiExtension::CLIENT_PREFIX.$key);
    }

    public function getClient(string $key): ModelProviderClientInterface
    {
        $this->getType($key);

        return $this->clients->get($key);
    }
}
