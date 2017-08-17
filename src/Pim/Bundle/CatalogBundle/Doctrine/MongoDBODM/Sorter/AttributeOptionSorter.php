<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface;

/**
 * Attribute option sorter
 *
 * @author    Willy MESNAGE <willy.mesnage@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionSorter implements AttributeSorterInterface
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
    public function addAttributeSorter(AttributeInterface $attribute, $direction, $locale = null, $scope = null)
    {
        $sortField = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $optionValueField = sprintf(
            '%s.%s.optionValues.%s.value',
            ProductQueryUtility::NORMALIZED_FIELD,
            $sortField,
            $locale
        );

        $this->qb->sort($optionValueField, $direction);
        $this->qb->sort(ProductQueryUtility::NORMALIZED_FIELD.'.'.$sortField.'.code', $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getType(),
            [
                AttributeTypes::OPTION_SIMPLE_SELECT
            ]
        );
    }
}
