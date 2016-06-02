<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;

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

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->qb = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AttributeInterface $attribute, $direction, $locale = null, $scope = null)
    {
        $sortField = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $this->qb->sort(ProductQueryUtility::NORMALIZED_FIELD.'.'.$sortField, $direction);
        $this->qb->sort('_id');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction, $locale = null, $scope = null)
    {
        $this->qb->sort(ProductQueryUtility::NORMALIZED_FIELD.'.'.$field, $direction);
        $this->qb->sort('_id');

        return $this;
    }
}
