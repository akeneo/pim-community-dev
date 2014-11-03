<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
        $loader->load('attribute_icons.yml');
        $loader->load('colors.yml');
        $loader->load('controllers.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('entities.yml');
        $loader->load('event_listeners.yml');
        $loader->load('factories.yml');
        $loader->load('form_subscribers.yml');
        $loader->load('form_types.yml');
        $loader->load('forms.yml');
        $loader->load('handlers.yml');
        $loader->load('managers.yml');
        $loader->load('mass_actions.yml');
        $loader->load('normalizers.yml');
        $loader->load('providers.yml');
        $loader->load('repositories.yml');
        $loader->load('resolvers.yml');
        $loader->load('serializers.yml');
        $loader->load('twig.yml');
        $loader->load('view_elements.yml');

        $loader->load('view_elements/association_type.yml');
        $loader->load('view_elements/attribute.yml');
        $loader->load('view_elements/attribute_group.yml');
        $loader->load('view_elements/channel.yml');
        $loader->load('view_elements/family.yml');
        $loader->load('view_elements/group_type.yml');
        $loader->load('view_elements/product.yml');
        $loader->load('view_elements/category.yml');
        $loader->load('view_elements/group.yml');

        if ($config['record_mails']) {
            $loader->load('mail_recorder.yml');
        }
    }
}
