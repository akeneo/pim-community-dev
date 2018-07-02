<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SuggestDataBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Enterprise Security extension
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PimEnterpriseSuggestDataExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('clients.yml');
        $loader->load('connections.yml');
        $loader->load('connectors.yml');
        $loader->load('controllers.yml');
        $loader->load('data_providers.yml');
        $loader->load('handlers.yml');
        $loader->load('repositories.yml');
    }
}
