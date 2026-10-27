<?php

namespace Wexample\SymfonyRemoteAi\Service;

use Psr\Container\ContainerInterface;
use Wexample\PhpRemoteAi\Interface\ModelProviderClientInterface;

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

    public function getClient(string $key): ModelProviderClientInterface
    {
        $this->getType($key);

        return $this->clients->get($key);
    }
}
