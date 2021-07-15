<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Akeneo Rule Engine extension
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class AkeneoRuleEngineExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('action_appliers.yml');
        $loader->load('doctrine.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('models.yml');
        $loader->load('normalizers.yml');
        $loader->load('repositories.yml');
        $loader->load('runners.yml');
        $loader->load('commands.yml');
        $loader->load('upgrades.yml');
    }
}
