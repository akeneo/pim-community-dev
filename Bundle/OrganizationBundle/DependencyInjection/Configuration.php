<?php

namespace Oro\Bundle\OrganizationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_organization');

        SettingsBuilder::append(
            $rootNode,
            array(
                'organization_name' => array(
                    'value'   => 'default',
                ),
            )
        );

        return $treeBuilder;
    }
}
