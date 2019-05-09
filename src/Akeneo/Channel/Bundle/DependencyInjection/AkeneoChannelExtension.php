<?php

declare(strict_types=1);

namespace Akeneo\Channel\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoChannelExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('array_converters.yml');
        $loader->load('controllers.yml');
        $loader->load('processors.yml');
        $loader->load('readers.yml');
        $loader->load('jobs.yml');
        $loader->load('job_defaults.yml');
        $loader->load('job_constraints.yml');
        $loader->load('services.yml');
        $loader->load('steps.yml');
        $loader->load('validators.yml');
        $loader->load('writers.yml');
        $loader->load('view_elements/attribute.yml');
    }
}
