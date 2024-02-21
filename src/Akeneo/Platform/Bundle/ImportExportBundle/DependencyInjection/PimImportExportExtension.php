<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Import export bundle extension.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimImportExportExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('factory.yml');
        $loader->load('grid.yml');
        $loader->load('handlers.yml');
        $loader->load('hydrators.yml');
        $loader->load('normalizers.yml');
        $loader->load('queries.yml');
        $loader->load('repositories.yml');
        $loader->load('security.yml');
        $loader->load('services.yml');
        $loader->load('step.yml');
        $loader->load('storage_client.yml');
        $loader->load('validations.yml');
        $loader->load('jobs.yml');
    }
}
