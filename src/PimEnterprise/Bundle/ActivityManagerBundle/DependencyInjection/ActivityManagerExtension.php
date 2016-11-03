<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ActivityManagerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $containerBuilder)
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('datagrid.yml');
        $loader->load('doctrine/models.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('event_listeners.yml');
        $loader->load('factories.yml');
        $loader->load('jobs.yml');
        $loader->load('job_parameters.yml');
        $loader->load('normalizers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
        $loader->load('updaters.yml');
    }
}
