<?php

namespace Wexample\SymfonyRemoteAi\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Wexample\PhpRemoteAi\Class\AnthropicClient;
use Wexample\PhpRemoteAi\Class\OllamaClient;
use Wexample\PhpRemoteAi\Class\OpenAiClient;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyRemote\DependencyInjection\WexampleSymfonyRemoteExtension;
use Wexample\SymfonyRemoteAi\Service\RemoteAiProviderRegistry;

class WexampleSymfonyRemoteAiExtension extends AbstractWexampleSymfonyExtension
{
    /** The key of a provider's client among symfony-remote's. */
    public const string CLIENT_PREFIX = 'ai_';

    private const array CLIENT_CLASSES = [
        Configuration::TYPE_OPEN_AI => OpenAiClient::class,
        Configuration::TYPE_OPENAI_COMPATIBLE => OpenAiClient::class,
        Configuration::TYPE_ANTHROPIC => AnthropicClient::class,
        Configuration::TYPE_OLLAMA => OllamaClient::class,
    ];

    private const array LABELS = [
        Configuration::TYPE_OPEN_AI => 'OpenAI',
        Configuration::TYPE_OPENAI_COMPATIBLE => 'OpenAI-compatible',
        Configuration::TYPE_ANTHROPIC => 'Anthropic',
        Configuration::TYPE_OLLAMA => 'Ollama',
    ];

    /**
     * Each provider's client is one of symfony-remote's: built from its
     * definition, with its status among the app's remotes.
     */
    public function prepend(ContainerBuilder $container): void
    {
        parent::prepend($container);

        $config = $this->processConfiguration(new Configuration(), $container->getExtensionConfig($this->getAlias()));
        $clients = [];

        foreach ($config['providers'] as $key => $provider) {
            $clients[self::CLIENT_PREFIX.$key] = array_filter([
                'class' => self::CLIENT_CLASSES[$provider['type']],
                'label' => self::LABELS[$provider['type']].' ('.$key.')',
                'base_url' => $provider['base_url'] ?? Configuration::BASE_URLS[$provider['type']],
                'api_key' => $provider['api_key'],
                'api_key_required' => in_array($provider['type'], Configuration::API_KEY_REQUIRED, true),
            ], static fn (mixed $value): bool => null !== $value);
        }

        if ([] !== $clients) {
            $container->prependExtensionConfig('wexample_symfony_remote', ['clients' => $clients]);
        }
    }

    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $this->loadConfig(
            __DIR__,
            $container
        );

        $clients = [];
        $types = [];

        foreach ($config['providers'] as $key => $provider) {
            $clients[(string) $key] = new Reference(WexampleSymfonyRemoteExtension::SERVICE_CLIENT_PREFIX.self::CLIENT_PREFIX.$key);
            $types[(string) $key] = $provider['type'];
        }

        $container->getDefinition(RemoteAiProviderRegistry::class)
            ->setArgument('$clients', ServiceLocatorTagPass::register($container, $clients))
            ->setArgument('$types', $types);
    }
}
