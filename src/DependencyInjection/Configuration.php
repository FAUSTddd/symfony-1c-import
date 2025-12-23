<?php

declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('faustddd_1c_import');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->scalarNode('endpoint')
            ->defaultValue('/1c/exchange')
            ->info('URL, на который 1С будет слать запросы')
            ->validate()
            ->ifTrue(fn ($v) => !is_string($v) || $v === '')
            ->thenInvalid('Endpoint must be a non-empty string')
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}