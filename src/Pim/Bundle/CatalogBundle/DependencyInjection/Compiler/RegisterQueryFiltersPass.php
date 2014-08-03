<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register product query filters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterQueryFiltersPass implements CompilerPassInterface
{
    /** @staticvar string */
    const QUERY_BUILDER_SERVICE = 'pim_catalog.doctrine.product_query_builder';

    /** @staticvar string */
    const QUERY_FILTER_TAG = 'pim_catalog.doctrine.query.filter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::QUERY_BUILDER_SERVICE)) {
            throw new \LogicException('ProductQueryBuilder must be configured');
        }

        $service = $container->getDefinition(self::QUERY_BUILDER_SERVICE);

        $taggedServices = $container->findTaggedServiceIds(self::QUERY_FILTER_TAG);
        foreach ($taggedServices as $id => $attributes) {
            $priority = current($attributes)['priority'];
            $service->addMethodCall('registerFilter', [new Reference($id), $priority]);
        }
    }
}
