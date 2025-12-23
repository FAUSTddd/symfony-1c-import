<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return (new TreeBuilder('faustddd_1c_import'))
            ->getRootNode()
            ->children()
            ->scalarNode('endpoint')
            ->defaultValue('/import/1c-exchange')
            ->cannotBeEmpty()
            ->end()
            ->end()
            ->end();   // <-- закрываем root-ноду
    }
}