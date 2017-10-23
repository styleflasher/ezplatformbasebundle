<?php

namespace Styleflasher\eZPlatformBaseBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SiteAccessConfiguration
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('styleflashere_z_platform_base');

        $systemNode = $this->generateScopeBaseNode( $rootNode );
        $systemNode
            ->arrayNode( 'search' )
                ->children()
                    ->scalarNode( 'searchresult_view' )->defaultFalse()->end()
                    ->scalarNode( 'wildcard' )->defaultTrue()->end()
                ->end()
            ->end()
            ->arrayNode( 'sujets' )
                ->children()
                    ->integerNode( 'fallback_container_location_id' )->end()
                    ->arrayNode( 'sujetclasses' )
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode( 'menu' )
                ->children()
                    ->booleanNode('has_submenu')->defaultFalse()->end()
                    ->integerNode( 'levels' )->end()
                    ->arrayNode( 'main' )
                        ->children()
                            ->arrayNode( 'classes' )
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode( 'excluded_location_ids' )
                                ->prototype('scalar')->end()
                            ->end()
                         ->end()
                    ->end()
                    ->arrayNode( 'sub' )
                        ->children()
                            ->arrayNode( 'classes' )
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode( 'excluded_location_ids' )
                                ->prototype('scalar')->end()
                            ->end()
                         ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode( 'right_column' )
                ->children()
                    ->arrayNode( 'social_media' )
                        ->children()
                            ->booleanNode('show')->defaultFalse()->end()
                            ->scalarNode( 'facebook' )->defaultFalse()->end()
                            ->scalarNode( 'twitter' )->defaultFalse()->end()
                            ->scalarNode( 'linkedin' )->defaultFalse()->end()
                         ->end()
                    ->end()
                    ->arrayNode( 'widgets' )
                        ->children()
                            ->booleanNode('show')->defaultFalse()->end()
                            ->arrayNode( 'classes' )
                                ->prototype('scalar')->end()
                            ->end()
                         ->end()
                    ->end()
                    ->arrayNode( 'additional_menu_level' )
                        ->children()
                            ->booleanNode('show')->defaultFalse()->end()
                            ->integerNode( 'depth' )->end()
                            ->arrayNode( 'classes' )
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode( 'excluded_location_ids' )
                                ->prototype('scalar')->end()
                            ->end()
                         ->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
    }
}
