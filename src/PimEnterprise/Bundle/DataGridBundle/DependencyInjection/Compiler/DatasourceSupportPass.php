<?php

namespace PimEnterprise\Bundle\DataGridBundle\DependencyInjection\Compiler;

use PimEnterprise\Bundle\DataGridBundle\Datasource\DatasourceTypes;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Datasource support pass
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class DatasourceSupportPass implements CompilerPassInterface
{
    /** @staticvar string */
    const DATASOURCE_SUPPORT_RESOLVER_ID = 'pim_datagrid.datasource.support_resolver';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $supportResolver = $container->getDefinition(self::DATASOURCE_SUPPORT_RESOLVER_ID);
        $supportResolver->addMethodCall(
            'addMongoEligibleDatasources',
            [ DatasourceTypes::DATASOURCE_PRODUCT_HISTORY ]
        );
    }
}
