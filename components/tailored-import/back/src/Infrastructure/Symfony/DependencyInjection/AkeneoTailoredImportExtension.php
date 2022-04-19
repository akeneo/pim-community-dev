<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AkeneoTailoredImportExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('handlers.yml');
        $loader->load('hydrators.yml');
        $loader->load('jobs.yml');
        $loader->load('operation_appliers.yml');
        $loader->load('processors.yml');
        $loader->load('queries.yml');
        $loader->load('ramsey.yml');
        $loader->load('readers.yml');
        $loader->load('services.yml');
        $loader->load('source_parameter_appliers.yml');
        $loader->load('spout.yml');
        $loader->load('user_intent_builders.yml');
        $loader->load('validations.yml');
        $loader->load('writers.yml');

        $isEnabled = (bool)($_ENV['FLAG_TAILORED_IMPORT_ENABLED'] ?? false);
        if (!$isEnabled) {
            $container->removeDefinition('akeneo.tailored_import.job.xlsx_product.import');
        }
    }
}
