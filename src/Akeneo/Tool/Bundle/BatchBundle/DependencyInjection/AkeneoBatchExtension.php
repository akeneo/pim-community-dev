<?php

namespace Akeneo\Tool\Bundle\BatchBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Batch bundle services configuration declaration
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class AkeneoBatchExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('entities.yml');
        $loader->load('jobs.yml');
        $loader->load('removers.yml');
        $loader->load('savers.yml');
        $loader->load('services.yml');
        $loader->load('validators.yml');
        $loader->load('updaters.yml');
        $loader->load('cli_commands.yml');

        $container->setParameter('akeneo_batch.mail_notifier.sender_email', $config['sender_email']);
        if ($config['enable_mail_notification']) {
            $container
                ->getDefinition('akeneo_batch.mail_notifier')
                ->addTag('akeneo_batch.notifier');
        }
    }
}
