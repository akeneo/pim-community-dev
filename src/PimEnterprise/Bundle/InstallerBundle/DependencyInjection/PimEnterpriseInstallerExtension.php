<?php

namespace PimEnterprise\Bundle\InstallerBundle\DependencyInjection;

use Pim\Bundle\InstallerBundle\DependencyInjection\PimInstallerExtension as BasePimInstallerExtension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimEnterpriseInstallerExtension extends BasePimInstallerExtension
{
    /**
     * {@inheritdoc}
     */
    protected $entities = array(
        'channels',
        'locales',
        'currencies',
        'families',
        'attribute_groups',
        'attributes',
        'categories',
        'group_types',
        'groups',
        'associations',
        'jobs',
        'products',
        'users',
        'attribute_groups_accesses'
    );

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('fixture_loader.yml');
        $this->addInstallerDataFiles($container);
    }
}
