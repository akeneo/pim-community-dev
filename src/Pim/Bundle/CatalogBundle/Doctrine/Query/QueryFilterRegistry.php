<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Aims to register and retrieve filters useable on product query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QueryFilterRegistry implements QueryFilterRegistryInterface
{
    /** @var AttributeFilterInterface[] priorized attribute filters */
    protected $attributeFilters = [];

    /** @var FieldSorterInterface[] priorized field filters */
    protected $fieldFilters = [];

    /**
     * {@inheritdoc}
     */
    public function register(FilterInterface $filter)
    {
        if ($filter instanceof FieldFilterInterface) {
            $this->fieldFilters[]= $filter;
        }
        if ($filter instanceof AttributeFilterInterface) {
            $this->attributeFilters[]= $filter;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldFilter($field)
    {
        foreach ($this->fieldFilters as $filter) {
            if ($filter->supportsField($field)) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeFilter(AttributeInterface $attribute)
    {
        foreach ($this->attributeFilters as $filter) {
            if ($filter->supportsAttribute($attribute)) {
                return $filter;
            }
        }

        return null;
    }
}
