<?php

namespace Wexample\SymfonyRemoteAi\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const string TYPE_OPEN_AI = 'open_ai';

    /** Any service speaking OpenAI's api at its own url: Mistral, Groq, OpenRouter, a llama.cpp server. */
    public const string TYPE_OPENAI_COMPATIBLE = 'openai_compatible';

    public const string TYPE_ANTHROPIC = 'anthropic';

    public const string TYPE_OLLAMA = 'ollama';

    /** Prior Labs' TabPFN api: tabular prediction. */
    public const string TYPE_TABPFN = 'tabpfn';

    /** Where each type answers when the provider names no url of its own; null, a url is required. */
    public const array BASE_URLS = [
        self::TYPE_OPEN_AI => 'https://api.openai.com/v1',
        self::TYPE_OPENAI_COMPATIBLE => null,
        self::TYPE_ANTHROPIC => 'https://api.anthropic.com/v1',
        self::TYPE_OLLAMA => null,
        self::TYPE_TABPFN => 'https://api.priorlabs.ai',
    ];

    /** The types that answer nothing without a key. */
    public const array API_KEY_REQUIRED = [
        self::TYPE_OPEN_AI,
        self::TYPE_ANTHROPIC,
        self::TYPE_TABPFN,
    ];

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wexample_symfony_remote_ai');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('providers')
                    ->info('The AI providers the app reads models from, by key: `open_ai`, `ollama`, `mistral`, `tabpfn`…')
                    ->useAttributeAsKey('key')
                    ->arrayPrototype()
                        ->children()
                            ->enumNode('type')
                                ->values(array_keys(self::BASE_URLS))
                                ->isRequired()
                            ->end()
                            ->scalarNode('base_url')
                                ->defaultNull()
                                ->info('Required for `openai_compatible` and `ollama` (`http://ollama:11434`); the public api of the others by default.')
                            ->end()
                            ->scalarNode('api_key')
                                ->defaultNull()
                                ->info('Required by `open_ai` and `anthropic`; an Ollama server asks none.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
