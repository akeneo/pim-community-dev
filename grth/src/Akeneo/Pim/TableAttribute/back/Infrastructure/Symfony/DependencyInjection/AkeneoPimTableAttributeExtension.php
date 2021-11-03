<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AkeneoPimTableAttributeExtension extends Extension
{
    /**
     * @param array<string, mixed> $configs
     *
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('array_converters.yml');
        $loader->load('attribute_types.yml');
        $loader->load('controllers.yml');
        $loader->load('enrichment.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('factories.yml');
        $loader->load('filters.yml');
        $loader->load('jobs.yml');
        $loader->load('normalizers.yml');
        $loader->load('persistence.yml');
        $loader->load('providers.yml');
        $loader->load('twig.yml');
        $loader->load('validators.yml');
        $loader->load('value_filters.yml');
    }
}
