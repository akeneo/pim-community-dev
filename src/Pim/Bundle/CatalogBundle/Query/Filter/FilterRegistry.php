<?php

namespace Pim\Bundle\CatalogBundle\Query\Filter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

/**
 * Aims to register and retrieve filters useable on product query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterRegistry implements FilterRegistryInterface
{
    /** @var AttributeFilterInterface[] priorized attribute filters */
    protected $attributeFilters = [];

    /** @var FieldSorterInterface[] priorized field filters */
    protected $fieldFilters = [];

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function register(FilterInterface $filter)
    {
        if ($filter instanceof FieldFilterInterface) {
            $this->fieldFilters[] = $filter;
        }
        if ($filter instanceof AttributeFilterInterface) {
            $this->attributeFilters[] = $filter;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFilter($code)
    {
        $attribute = $this->attributeRepository->findOneBy(['code' => FieldFilterHelper::getCode($code)]);

        if (null !== $attribute) {
            return $this->getAttributeFilter($attribute);
        }

        return $this->getFieldFilter($code);
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
