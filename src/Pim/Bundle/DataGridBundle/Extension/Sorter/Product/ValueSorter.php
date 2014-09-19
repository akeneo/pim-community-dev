<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface;

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
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @param ProductRepositoryInterface $prodRepository
     * @param AttributeRepository        $attRepository
     */
    public function __construct(ProductRepositoryInterface $prodRepository, AttributeRepository $attRepository)
    {
        $this->productRepository   = $prodRepository;
        $this->attributeRepository = $attRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        $attribute = $this->attributeRepository->findOneByCode($field);
        $qb = $datasource->getQueryBuilder();
        $pqb = $this->productRepository->getProductQueryBuilder($qb);
        $pqb->addAttributeSorter($attribute, $direction);
    }
}
