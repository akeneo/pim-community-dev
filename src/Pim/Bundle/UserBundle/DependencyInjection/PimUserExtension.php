<?php

namespace Pim\Bundle\UserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimUserExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('context.yml');
        $loader->load('controllers.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('entities.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('form.yml');
        $loader->load('normalizers.yml');
        $loader->load('repositories.yml');
        $loader->load('twig.yml');
        $loader->load('view_elements.yml');
        $loader->load('view_elements/user.yml');
        $loader->load('savers.yml');
        $loader->load('removers.yml');
        $loader->load('menu.yml');
        $loader->load('security.yml');

        $container->setParameter('pim_user.reset.ttl', $config['reset']['ttl']);
        $container->setParameter('pim_user.email', [$config['email']['address'] => $config['email']['name']]);
        $container->setParameter('pim_user.privileges', $config['privileges']);
    }
}
