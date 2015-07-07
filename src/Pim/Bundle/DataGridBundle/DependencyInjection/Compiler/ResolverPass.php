<?php

namespace Pim\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Setup the the pager resolver and the datasource adapter resolver for the MongoDB support.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolverPass implements CompilerPassInterface
{
    /** @staticvar string */
    const PAGER_RESOLVER_ID = 'pim_datagrid.extension.pager.pager_resolver';

    /** @staticvar string */
    const MONGO_PAGER_ID = 'pim_datagrid.extension.pager.mongodb.pager';

    /** @staticvar string */
    const DATASOURCE_ADAPTER_RESOLVER_ID = 'pim_datagrid.datasource.adapter_resolver';

    /** @staticvar string */
    const MONGO_DATASOURCE_ADAPTER_CLASS = 'pim_filter.datasource.mongodb_adapter.class';

    /** @staticvar string */
    const MONGO_PRODUCT_DATASOURCE_ADAPTER_CLASS = 'pim_filter.datasource.product_mongodb_adapter.class';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $pagerResolver = $container->getDefinition(self::PAGER_RESOLVER_ID);
        $datasourceResolver = $container->getDefinition(self::DATASOURCE_ADAPTER_RESOLVER_ID);

        if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM ===
            $container->getParameter('pim_catalog_product_storage_driver')
        ) {
            $datasourceResolver->addMethodCall(
                'setMongodbAdapterClass',
                [ $container->getParameter(self::MONGO_DATASOURCE_ADAPTER_CLASS) ]
            );
            $datasourceResolver->addMethodCall(
                'setProductMongodbAdapterClass',
                [ $container->getParameter(self::MONGO_PRODUCT_DATASOURCE_ADAPTER_CLASS) ]
            );

            $pagerResolver->addMethodCall('setMongodbPager', [ new Reference(self::MONGO_PAGER_ID) ]);
        }
    }
}
