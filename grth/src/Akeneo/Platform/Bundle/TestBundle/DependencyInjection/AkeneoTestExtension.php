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

namespace Akeneo\Platform\Bundle\TestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class AkeneoTestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        if ('test_fake' === $container->getParameter('kernel.environment')) {
            $projectDir = $container->getParameter('kernel.project_dir');
            $loader = new YamlFileLoader($container, new FileLocator($projectDir . '/vendor/akeneo/pim-community-dev/tests/back/Platform/Acceptance/CatalogVolumeMonitoring/Resources/config/pim'));
            $loader->load('queries.yml');
            $loader = new YamlFileLoader($container, new FileLocator($projectDir . '/vendor/akeneo/pim-community-dev/tests/back/Acceptance/Resources/config/behat'));
            $loader->load('services.yml');
            $loader = new YamlFileLoader($container, new FileLocator($projectDir . '/vendor/akeneo/pim-community-dev/tests/back/Platform/Acceptance/CatalogVolumeMonitoring/Resources/config/behat'));
            $loader->load('services.yml');
        }
    }
}
