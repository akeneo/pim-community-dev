<?php

namespace Pim\Bundle\EnrichBundle\ProductQueryBuilder;

use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

/**
 * Provides a way to search simply and efficiently product and product models.
 *
 * @author    Julien Sanchez <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditProductAndProductModelQueryBuilder implements ProductQueryBuilderInterface
{
    /** @var ProductQueryBuilderInterface */
    private $pqb;

    /**
     * @param ProductQueryBuilderInterface $pqb
     */
    public function __construct(ProductQueryBuilderInterface $pqb)
    {
        $this->pqb = $pqb;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter($field, $operator, $value, array $context = [])
    {
        return $this->pqb->addFilter($field, $operator, $value, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function addSorter($field, $direction, array $context = [])
    {
        return $this->pqb->addSorter($field, $direction, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getRawFilters()
    {
        return $this->pqb->getRawFilters();
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder()
    {
        return $this->pqb->getQueryBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        return $this->pqb->setQueryBuilder($queryBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->pqb->execute();
    }
}
