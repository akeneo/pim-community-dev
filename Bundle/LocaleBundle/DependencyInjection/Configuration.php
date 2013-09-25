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

        SettingsBuilder::append(
            $treeBuilder->root('oro_locale'),
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
