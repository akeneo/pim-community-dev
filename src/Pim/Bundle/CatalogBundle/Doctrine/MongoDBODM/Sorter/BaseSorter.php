<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Doctrine\AttributeSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\FieldSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Base sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseSorter implements AttributeSorterInterface, FieldSorterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /**
     * @param QueryBuilder   $qb
     * @param CatalogContext $context
     */
    public function __construct(QueryBuilder $qb, CatalogContext $context)
    {
        $this->qb      = $qb;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AttributeInterface $attribute, $direction)
    {
        $sortField = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $this->context);
        $this->qb->sort(ProductQueryUtility::NORMALIZED_FIELD.'.'.$sortField, $direction);
        $this->qb->sort('_id');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        $this->qb->sort(ProductQueryUtility::NORMALIZED_FIELD.'.'.$field, $direction);
        $this->qb->sort('_id');

        return $this;
    }
}
