<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TransformBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * EE Transform bundle extension
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PimEnterpriseTransformExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadSerializerConfig($configs, $container);
    }

    /**
     * Load serializer related configuration
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    protected function loadSerializerConfig(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/serializer'));
        $loader->load('structured.yml');
    }
}
