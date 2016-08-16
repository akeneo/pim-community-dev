<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection;

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
        $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('factory.yml');
        $loader->load('form_types.yml');
        $loader->load('forms.yml');
        $loader->load('grid.yml');
        $loader->load('job_labels.yml');
        $loader->load('job_parameters.yml');
        $loader->load('managers.yml');
        $loader->load('normalizers.yml');
        $loader->load('repositories.yml');
        $loader->load('services.yml');
        $loader->load('twig.yml');
        $loader->load('validators.yml');
        $loader->load('view_elements.yml');
        $loader->load('view_elements/job_profile.yml');
    }
}
