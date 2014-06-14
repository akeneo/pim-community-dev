<?php

namespace PimEnterprise\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Add grid filter types
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddFilterTypesPass implements CompilerPassInterface
{
    /** @staticvar string */
    const FILTER_PROPOSITION_EXTENSION_ID = 'pimee_datagrid.extension.filter.proposition_filter';

    /** @staticvar string */
    const TAG_NAME = 'oro_filter.extension.orm_filter.filter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $propositionExtension = $container->getDefinition(self::FILTER_PROPOSITION_EXTENSION_ID);

        $filters = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach ($filters as $serviceId => $tags) {
            $tagAttrs = reset($tags);
            if ($propositionExtension) {
                $propositionExtension->addMethodCall('addFilter', array($tagAttrs['type'], new Reference($serviceId)));
            }
        }
    }
}
