<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Enterprise Security extension
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class PimEnterpriseSecurityExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('api.yml');
        $loader->load('analytics.yml');
        $loader->load('array_converters.yml');
        $loader->load('controllers.yml');
        $loader->load('entities.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('factories.yml');
        $loader->load('form.yml');
        $loader->load('guessers.yml');
        $loader->load('managers.yml');
        $loader->load('processors.yml');
        $loader->load('queries.yml');
        $loader->load('removers.yml');
        $loader->load('readers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
        $loader->load('twig.yml');
        $loader->load('updaters.yml');
        $loader->load('voters.yml');
        $loader->load('normalizers.yml');
        $loader->load('writers.yml');
        $loader->load('filters.yml');
        $loader->load('query_builder.yml');
        $loader->load('datagrid.yml');
        $loader->load('mass_edit.yml');
        $loader->load('renderers.yml');
    }
}
