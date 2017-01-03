<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\MongoDB\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;

/**
 * Reference data sorter
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataSorter implements AttributeSorterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /**
     * @param ConfigurationRegistryInterface $registry
     */
    public function __construct(ConfigurationRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

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
    public function supportsAttribute(AttributeInterface $attribute)
    {
        $referenceDataName = $attribute->getReferenceDataName();

        return null !== $referenceDataName && null !== $this->registry->get($referenceDataName) ? true : false;
    }
}
