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
    /**
     * @var string
     */
    const FILTER_PROPOSAL_EXTENSION_ID = 'pimee_datagrid.extension.filter.proposal_filter';

    /**
     * @var string
     */
    const TAG_NAME = 'oro_filter.extension.orm_filter.filter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $proposalExtension = $container->getDefinition(self::FILTER_PROPOSAL_EXTENSION_ID);

        $filters = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach ($filters as $serviceId => $tags) {
            $tagAttrs = reset($tags);
            if ($proposalExtension) {
                $proposalExtension->addMethodCall('addFilter', array($tagAttrs['type'], new Reference($serviceId)));
            }
        }
    }
}
