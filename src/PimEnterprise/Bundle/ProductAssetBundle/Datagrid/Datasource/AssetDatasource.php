<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Datasource;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\QueryBuilderUtility;
use Pim\Bundle\DataGridBundle\Datasource\Datasource;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Asset datasource, allows to prepare query builder from repository
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class AssetDatasource extends Datasource
{
    /**
     * @param ObjectManager     $om
     * @param HydratorInterface $hydrator
     */
    public function __construct(ObjectManager $om, HydratorInterface $hydrator)
    {
        $this->om       = $om;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $options = [
            'dataLocale' => $this->getConfiguration('locale_code', false),
        ];

        if (method_exists($this->qb, 'setParameters')) {
            QueryBuilderUtility::removeExtraParameters($this->qb);
        }

        $rows = $this->hydrator->hydrate($this->qb, $options);

        return $rows;
    }
}
