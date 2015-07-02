<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class PimEnterpriseProductAssetExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('attribute_types.yml');
        $loader->load('builders.yml');
        $loader->load('controllers.yml');
        $loader->load('data_sources.yml');
        $loader->load('datagrid/attribute_types.yml');
        $loader->load('datagrid/filters.yml');
        $loader->load('datagrid/formatters.yml');
        $loader->load('datagrid/selectors.yml');
        $loader->load('events.yml');
        $loader->load('factories.yml');
        $loader->load('finders.yml');
        $loader->load('formatters.yml');
        $loader->load('forms.yml');
        $loader->load('models.yml');
        $loader->load('normalizers.yml');
        $loader->load('providers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
        $loader->load('selectors.yml');
        $loader->load('services.yml');
        $loader->load('twig_extension.yml');
        $loader->load('updaters.yml');
    }
}
