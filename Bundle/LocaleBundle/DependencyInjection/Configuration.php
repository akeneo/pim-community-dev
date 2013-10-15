<?php

namespace Oro\Bundle\LocaleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

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
                                ->defaultValue('%name%\n%organization%\n%street%\n%CITY%')
                            ->end()
                            ->scalarNode('latin_format')
                                ->cannotBeEmpty()
                                ->defaultValue('%name%\n%organization%\n%street%\n%CITY%')
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
                            ->scalarNode('state_name_type')
                                ->cannotBeEmpty()
                                ->defaultValue('province')
                            ->end()
                            ->scalarNode('direction')
                                ->cannotBeEmpty()
                                ->defaultValue('ltr')
                            ->end()
                            ->scalarNode('format_charset')
                                ->cannotBeEmpty()
                                ->defaultValue('UTF-8')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        SettingsBuilder::append(
            $rootNode,
            array(
                'date_format'         => array('value' => 'm/d/y'),
                'time_format'         => array('value' => 'h:i a'),
                'locale'              => array('value' => 'en_US'),
                'timezone'            => array('value' => 'America/New_York'),
                'default_currency'    => array('value' => 'USD'),
                'decimal_symbol'      => array('value' => '.'),
                'thousands_separator' => array('value' => ','),
                'number_of_decimals'  => array('value' => 2),
                'name_format'         => array('value' => '%%first%% %%last%%'),
            )
        );

        return $treeBuilder;
    }
}
