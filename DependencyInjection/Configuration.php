<?php

namespace Picoss\SMoneyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('picoss_smoney');

        $rootNode
            ->children()
                ->scalarNode('access_token')->cannotBeEmpty()->isRequired()
                ->scalarNode('base_url')->cannotBeEmpty()->isRequired()
                ->scalarNode('web_profiler')->defaultFalse()
            ->end()
        ;

        return $treeBuilder;
    }
}
