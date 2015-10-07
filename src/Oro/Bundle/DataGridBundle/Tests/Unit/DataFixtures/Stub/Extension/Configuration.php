<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\DataFixtures\Stub\Extension;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const ROOT = 'someExtensionConfig';
    const NODE = 'someKey';

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root(self::ROOT)->children()
                ->scalarNode(self::NODE)->end()
            ->end();

        return $builder;
    }
}
