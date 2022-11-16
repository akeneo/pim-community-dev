<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FrameworkBundle\DependencyInjection;

use Akeneo\Platform\Bundle\InstallerBundle\DependencyInjection\PimInstallerExtension as BasePimInstallerExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class PimEnterpriseFrameworkExtension extends BasePimInstallerExtension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('bounded_contexts.yml');
        $loader->load('acl_cache.yml');
    }
}
