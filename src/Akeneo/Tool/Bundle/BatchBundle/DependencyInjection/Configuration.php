<?php

namespace Akeneo\Tool\Bundle\BatchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('akeneo_batch');

        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->booleanNode('enable_mail_notification')->defaultFalse()->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
