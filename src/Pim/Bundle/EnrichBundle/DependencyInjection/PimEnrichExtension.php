<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Enrich extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimEnrichExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('handlers.yml');
        $loader->load('forms.yml');
        $loader->load('form_types.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('colors.yml');
        $loader->load('attribute_icons.yml');
        $loader->load('mass_actions.yml');
        $loader->load('factories.yml');
        $loader->load('twig.yml');
        $loader->load('providers.yml');
        $loader->load('event_listeners.yml');
        $loader->load('form_subscribers.yml');
        $loader->load('resolvers.yml');

        if ($config['record_mails']) {
            $loader->load('mail_recorder.yml');
        }
    }
}
