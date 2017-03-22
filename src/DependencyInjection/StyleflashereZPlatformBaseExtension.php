<?php

namespace Styleflasher\eZPlatformBaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Yaml\Yaml;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class StyleflashereZPlatformBaseExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $processor = new ConfigurationProcessor( $container, 'styleflashere_z_platform_base' );
        $processor->mapConfig(
            $config,
            function ( $scopeSettings, $currentScope, $contextualizer ) {
                $contextualizer->setContextualParameter( 'search.searchresult_view', $currentScope, $scopeSettings['search']['searchresult_view'] );

                $contextualizer->setContextualParameter( 'sujets.fallback_container_location_id', $currentScope, $scopeSettings['sujets']['fallback_container_location_id'] );
                $contextualizer->setContextualParameter( 'sujets.sujetclasses', $currentScope, $scopeSettings['sujets']['sujetclasses'] );

                $contextualizer->setContextualParameter( 'menu.has_submenu', $currentScope, $scopeSettings['menu']['has_submenu'] );
                $contextualizer->setContextualParameter( 'menu.levels', $currentScope, $scopeSettings['menu']['levels'] );
                $contextualizer->setContextualParameter( 'menu.main.classes', $currentScope, $scopeSettings['menu']['main']['classes'] );
                $contextualizer->setContextualParameter( 'menu.main.excluded_location_ids', $currentScope, $scopeSettings['menu']['main']['excluded_location_ids'] );
                $contextualizer->setContextualParameter( 'menu.sub.classes', $currentScope, $scopeSettings['menu']['sub']['classes'] );
                $contextualizer->setContextualParameter( 'menu.sub.excluded_location_ids', $currentScope, $scopeSettings['menu']['sub']['excluded_location_ids'] );

                $contextualizer->setContextualParameter( 'right_column.social_media.show', $currentScope, $scopeSettings['right_column']['social_media']['show'] );
                $contextualizer->setContextualParameter( 'right_column.social_media.facebook', $currentScope, $scopeSettings['right_column']['social_media']['facebook'] );
                $contextualizer->setContextualParameter( 'right_column.social_media.twitter', $currentScope, $scopeSettings['right_column']['social_media']['twitter'] );
                $contextualizer->setContextualParameter( 'right_column.social_media.linkedin', $currentScope, $scopeSettings['right_column']['social_media']['linkedin'] );
                $contextualizer->setContextualParameter( 'right_column.widgets.show', $currentScope, $scopeSettings['right_column']['widgets']['show'] );
                $contextualizer->setContextualParameter( 'right_column.widgets.classes', $currentScope, $scopeSettings['right_column']['widgets']['classes'] );
                $contextualizer->setContextualParameter( 'right_column.additional_menu_level.show', $currentScope, $scopeSettings['right_column']['additional_menu_level']['show'] );
                $contextualizer->setContextualParameter( 'right_column.additional_menu_level.depth', $currentScope, $scopeSettings['right_column']['additional_menu_level']['depth'] );
                $contextualizer->setContextualParameter( 'right_column.additional_menu_level.classes', $currentScope, $scopeSettings['right_column']['additional_menu_level']['classes'] );
                $contextualizer->setContextualParameter( 'right_column.additional_menu_level.excluded_location_ids', $currentScope, $scopeSettings['right_column']['additional_menu_level']['excluded_location_ids'] );
            }
        );
    }
    
    public function prepend( ContainerBuilder $container )
    {
        $stfConfigFile = __DIR__ . '/../Resources/config/default_settings.yml';
        $stfConfig = Yaml::parse( file_get_contents( $stfConfigFile ) );
        $container->prependExtensionConfig( 'styleflashere_z_platform_base', $stfConfig );
        $container->addResource( new FileResource( $stfConfigFile ) );
    }
}
