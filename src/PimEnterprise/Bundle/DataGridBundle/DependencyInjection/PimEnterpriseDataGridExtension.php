<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Enterprise DataGrid extension
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PimEnterpriseDataGridExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('actions.yml');
        $loader->load('adapters.yml');
        $loader->load('configurators.yml');
        $loader->load('controllers.yml');
        $loader->load('data_sources.yml');
        $loader->load('event_listeners.yml');
        $loader->load('extensions.yml');
        $loader->load('filters.yml');
        $loader->load('helpers.yml');
        $loader->load('hydrators.yml');
        $loader->load('managers.yml');
        $loader->load('mass_actions.yml');
        $loader->load('normalizers.yml');
    }
}
