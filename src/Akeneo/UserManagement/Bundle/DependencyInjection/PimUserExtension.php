<?php

namespace Akeneo\UserManagement\Bundle\DependencyInjection;

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
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('context.yml');
        $loader->load('controllers.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('entities.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('factories.yml');
        $loader->load('form.yml');
        $loader->load('form_types.yml');
        $loader->load('normalizers.yml');
        $loader->load('providers.yml');
        $loader->load('queries.yml');
        $loader->load('removers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
        $loader->load('twig.yml');
        $loader->load('updaters.yml');
        $loader->load('validators.yml');
        $loader->load('view_elements.yml');
        $loader->load('view_elements/user.yml');
        $loader->load('view_elements/group.yml');
        $loader->load('services.yml');
        $loader->load('array_converters.yml');
        $loader->load('commands.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('pim_user.reset.ttl', $config['reset']['ttl']);
        $container->setParameter('pim_user.email', [$config['email']['address'] => $config['email']['name']]);
        $container->setParameter('pim_user.privileges', $config['privileges']);
        $container->setParameter('pim_user.login_form.action_route', $config['login_form']['action_route']);
        $container->setParameter(
            'pim_user.login_form.additional_hidden_fields',
            $config['login_form']['additional_hidden_fields']
        );
    }
}
