<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Extension\Selector\Orm\Asset;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface;

/**
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ThumbnailSelector implements SelectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, DatagridConfiguration $configuration)
    {
        $rootAlias = $datasource->getQueryBuilder()->getRootAlias();

        $datasource->getQueryBuilder()
            ->leftJoin($rootAlias . '.references', 'aReferences')
            ->leftJoin('aReferences.variations', 'rVariations')
            ->leftJoin('rVariations.file', 'vFile')
            ->addSelect('aReferences')
            ->addSelect('rVariations')
            ->addSelect('vFile');
    }
}
