<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Enterprise Security extension
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AkeneoSuggestDataExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('connections.yml');
        $loader->load('data_providers.yml');
        $loader->load('handlers.yml');
        $loader->load('mappings.yml');
        $loader->load('services.yml');

        $loader->load('controllers.yml');
        $loader->load('repositories.yml');
        $loader->load('data_provider/in_memory.yml');
        $loader->load('data_provider/pim_ai.yml');

        $loader->load('client/pim_ai.yml');

        $loader->load('jobs.yml');
    }
}
