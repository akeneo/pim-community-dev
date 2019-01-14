<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Enterprise Security extension.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AkeneoFranklinInsightsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('commands.yml');
        $loader->load('controllers.yml');
        $loader->load('client/franklin.yml');
        $loader->load('cursors.yml');
        $loader->load('data_provider/franklin.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('handlers.yml');
        $loader->load('jobs.yml');
        $loader->load('normalizers.yml');
        $loader->load('processors.yml');
        $loader->load('queries.yml');
        $loader->load('readers.yml');
        $loader->load('repositories.yml');
        $loader->load('services.yml');
        $loader->load('steps.yml');
        $loader->load('subscribers.yml');
        $loader->load('tasklets.yml');
        $loader->load('validators.yml');
        $loader->load('writers.yml');
    }
}
