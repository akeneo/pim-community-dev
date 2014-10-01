<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;

/**
 * Product value sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueSorter implements SorterInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param ProductRepositoryInterface $prodRepository
     */
    public function __construct(ProductRepositoryInterface $prodRepository)
    {
        $this->productRepository   = $prodRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        $qb = $datasource->getQueryBuilder();
        $pqb = $this->productRepository->getProductQueryBuilder($qb);
        $pqb->addSorter($field, $direction);
    }
}
