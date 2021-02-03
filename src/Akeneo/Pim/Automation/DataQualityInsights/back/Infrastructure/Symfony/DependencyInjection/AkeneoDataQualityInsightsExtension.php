<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AkeneoDataQualityInsightsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('commands.yml');
        $loader->load('controllers.yml');
        $loader->load('criteria.yml');
        $loader->load('elasticsearch.yml');
        $loader->load('jobs.yml');
        $loader->load('feature_flags.yml');
        $loader->load('productgrid.yml');
        $loader->load('queries.yml');
        $loader->load('repositories.yml');
        $loader->load('services.yml');
        $loader->load('subscribers.yml');
        $loader->load('transformation.yml');
        $loader->load('poc.yml');
    }
}
