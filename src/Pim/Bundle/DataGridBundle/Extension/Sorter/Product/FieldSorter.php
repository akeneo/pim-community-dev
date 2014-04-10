<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;

/**
 * Product field sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldSorter implements SorterInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $repository;

    /**
     * @param ProductRepositoryInterface $prodRepository
     */
    public function __construct(ProductRepositoryInterface $prodRepository)
    {
        $this->repository = $prodRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        $qb  = $datasource->getQueryBuilder();
        $pqb = $this->repository->getProductQueryBuilder($qb);
        $pqb->addFieldSorter($field, $direction);
    }
}
