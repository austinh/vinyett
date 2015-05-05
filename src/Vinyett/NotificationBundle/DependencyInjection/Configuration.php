<?php

namespace Vinyett\NotificationBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('notification');
        
        $rootNode
            ->children()
                ->arrayNode('events')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('reference')
                                ->isRequired(true)
                            ->end()
                            ->booleanNode('is_active')
                                ->defaultValue(true)
                            ->end()
                        ->end()
                    ->end()
                ->end()                
                ->arrayNode('transports')
                    ->defaultValue(array())
                    ->prototype('array')
                    ->children()
                        ->scalarNode('class')
                            ->cannotBeEmpty()
                        ->end()
                        ->booleanNode('default_event')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('events')
                            ->defaultValue(array())
                            ->prototype('boolean')
                                ->defaultFalse()
                        ->end()
                    ->end()    
                ->end()
            ->end();

        return $treeBuilder;

    }
}
