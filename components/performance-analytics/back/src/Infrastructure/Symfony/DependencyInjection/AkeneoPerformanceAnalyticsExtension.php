<?php

declare(strict_types=1);

namespace Akeneo\PerformanceAnalytics\Infrastructure\Symfony\DependencyInjection;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class AkeneoPerformanceAnalyticsExtension extends Extension
{
    /**
     * @param array<mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('anti_corruption_layer.yml');
        $loader->load('actions.yml');
        $loader->load('command_handlers.yml');
        $loader->load('feature_flag.yml');
        $loader->load('pubsub.yml');
        $loader->load('query_handlers.yml');
        $loader->load('repositories.yml');
    }
}
