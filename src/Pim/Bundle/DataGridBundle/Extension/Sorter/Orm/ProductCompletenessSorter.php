<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter\Orm;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\ProductRepository;

/**
 * Product completeness sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCompletenessSorter implements SorterInterface
{
    /**
     * @var ProductRepository
     */
    protected $repository;

    /**
     * @param ProductRepository $repository
     */
    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        return function (DatasourceInterface $datasource, $field, $direction) {

            $qb        = $datasource->getQueryBuilder();
            $joinAlias = 'sorterCompleteness';

            $this->repository->addCompleteness($qb, $joinAlias);
            $qb->addOrderBy($joinAlias.'.'.$field, $direction);
        };
    }
}
