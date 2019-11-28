<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author    Arnaud Langlade <arnaud.langlade@gmail.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoPimStructureExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $configs = $this->processConfiguration($configuration, $configs);

        $container->setParameter('pim_reference_data.configurations', $configs['reference_data']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('event_subscribers.yml');
        $loader->load('factories.yml');
        $loader->load('updaters.yml');
        $loader->load('normalizers.yml');
        $loader->load('repositories.yml');
        $loader->load('removers.yml');
        $loader->load('services.yml');
        $loader->load('controllers.yml');
        $loader->load('attribute_types.yml');
        $loader->load('savers.yml');
        $loader->load('array_converters.yml');
        $loader->load('readers.yml');
        $loader->load('writers.yml');
        $loader->load('entities.yml');
        $loader->load('managers.yml');
        $loader->load('validators.yml');
        $loader->load('job_constraints.yml');
        $loader->load('job_defaults.yml');
        $loader->load('jobs.yml');
        $loader->load('steps.yml');
        $loader->load('commands.yml');
        $loader->load('queries.yml');
        $loader->load('processors.yml');
    }
}
