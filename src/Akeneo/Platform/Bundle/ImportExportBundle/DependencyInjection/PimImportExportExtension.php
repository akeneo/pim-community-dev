<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Import export bundle extension
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
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('factory.yml');
        $loader->load('grid.yml');
        $loader->load('normalizers.yml');
        $loader->load('queries.yml');
        $loader->load('registries.yml');
        $loader->load('repositories.yml');
        $loader->load('services.yml');
        $loader->load('widget.yml');

        if ($config['record_mails']) {
            $loader->load('mail_recorder.yml');
        }
    }
}
