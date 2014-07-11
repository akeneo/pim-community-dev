<?php

namespace Pim\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension;

/**
 * Add grid pagers
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PagerPass implements CompilerPassInterface
{
    /** @staticvar string */
    const PAGER_RESOLVER_ID = 'pim_datagrid.extension.pager.pager_resolver';

    /** @staticvar string */
    const MONGO_PAGER_ID = 'pim_datagrid.extension.pager.mongodb.pager';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $resolver = $container->getDefinition(self::PAGER_RESOLVER_ID);

        if (PimCatalogExtension::DOCTRINE_MONGODB_ODM === $container->getParameter('pim_catalog.storage_driver')) {
            $resolver->addMethodCall('setMongoPager', [ new Reference(self::MONGO_PAGER_ID) ]);
        }
    }
}
