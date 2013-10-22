<?php

namespace Oro\Bundle\LocaleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Intl\Intl;

/**
 * This is the class that validates and merges configuration from your app/config files
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder
            ->root('oro_locale')
            ->children()
                ->arrayNode('name_format')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('address_format')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('format')
                                ->cannotBeEmpty()
                                ->defaultValue('%name%\n%organization%\n%street%\n%CITY%\n%COUNTRY%')
                            ->end()
                            ->scalarNode('latin_format')
                                ->cannotBeEmpty()
                                ->defaultValue('%name%\n%organization%\n%street%\n%CITY%\n%COUNTRY%')
                            ->end()
                            ->arrayNode('require')
                                ->treatNullLike(array())
                                ->prototype('scalar')->end()
                                ->defaultValue(array('street', 'city'))
                            ->end()
                            ->scalarNode('zip_name_type')
                                ->cannotBeEmpty()
                                ->defaultValue('postal')
                            ->end()
                            ->scalarNode('region_name_type')
                                ->cannotBeEmpty()
                                ->defaultValue('province')
                            ->end()
                            ->scalarNode('direction')
                                ->cannotBeEmpty()
                                ->defaultValue('ltr')
                            ->end()
                            ->scalarNode('postprefix')
                                ->defaultNull()
                            ->end()
                            ->booleanNode('has_disputed')
                                ->defaultFalse()
                            ->end()
                            ->scalarNode('format_charset')
                                ->cannotBeEmpty()
                                ->defaultValue('UTF-8')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('locale_data')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('phone_prefix')
                            ->end()
                            ->scalarNode('default_locale')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        $date = new \DateTime('now');
        SettingsBuilder::append(
            $rootNode,
            array(
                'language' => array('value' => null),
                'locale'   => array('value' => '%locale%'),
                'country'  => array('value' => null),
                'timezone' => array('value' => $date->getTimezone()->getName()),
                'currency' => array('value' => 'USD'),
                'format_address_by_address_country' => array('value' => false, 'type' => 'boolean'),
            )
        );

        return $treeBuilder;
    }
}
