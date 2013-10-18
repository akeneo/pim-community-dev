<?php

namespace Oro\Bundle\BatchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $root = $treeBuilder->root('oro_batch');

        $root
            ->children()
                ->booleanNode('enable_mail_notification')->defaultFalse()->end()
                ->scalarNode('sender_email')->defaultValue('mailer@bap.com')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
