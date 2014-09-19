<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;

/**
 * Builds a product query builder by using  shortcuts to easily select, filter or sort products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryBuilder implements ProductQueryBuilderInterface
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var mixed */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /** @var AttributeFilterInterface[] priorized attribute filters */
    protected $attributeFilters = [];

    /** @var FieldSorterInterface[] priorized field filters */
    protected $fieldFilters = [];

    /** @var AttributeSorterInterface[] priorized attribute sorters */
    protected $attributeSorters = [];

    /** @var FieldSorterInterface[] priorized field sorters */
    protected $fieldSorters = [];

    /**
     * Constructor
     *
     * @param CatalogContext      $catalogContext
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(CatalogContext $catalogContext, AttributeRepository $attributeRepository)
    {
        $this->context = $catalogContext;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Get query builder
     *
     * @param mixed $qb
     *
     * @return ProductQueryBuilder
     */
    public function setQueryBuilder($qb)
    {
        $this->qb = $qb;

        return $this;
    }

    /**
     * Get query builder
     *
     * @return string
     */
    public function getQueryBuilder()
    {
        if (!$this->qb) {
            throw new \LogicException('Query builder must be configured');
        }

        return $this->qb;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter($field, $operator, $value)
    {
        $applied = $this->addFieldFilter($field, $operator, $value);

        if (!$applied) {
            $attribute = $this->attributeRepository->findOneByCode($field);
            if (!$attribute) {
                throw new \LogicException(
                    sprintf('There is no field or attribute "%s"', $field)
                );
            }
            $applied = $this->addAttributeFilter($attribute, $operator, $value);
        }

        if (!$applied) {
            throw new \LogicException(
                sprintf(
                    'Filter on field "%s" with operator "%s" is not supported',
                    $field,
                    $operator
                )
            );
        }

        return $this;
    }

    /**
     * Add a filter condition on an attribute
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string|array      $operator  the used operator
     * @param string|array      $value     the value(s) to filter
     *
     * @return boolean a filter has been applied
     */
    protected function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        foreach ($this->attributeFilters as $filter) {
            if ($filter->supportsAttribute($attribute) && $filter->supportsOperator($operator)) {
                $filter->setQueryBuilder($this->getQueryBuilder());
                $filter->addAttributeFilter($attribute, $operator, $value);

                return true;
            }
        }

        return false;
    }

    /**
     * Add a filter condition on a field
     *
     * @param string $field    the field
     * @param string $operator the used operator
     * @param string $value    the value to filter
     *
     * @return boolean a filter has been applied
     */
    protected function addFieldFilter($field, $operator, $value)
    {
        foreach ($this->fieldFilters as $filter) {
            if ($filter->supportsField($field) && $filter->supportsOperator($operator)) {
                $filter->setQueryBuilder($this->getQueryBuilder());
                $filter->addFieldFilter($field, $operator, $value);

                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AbstractAttribute $attribute, $direction)
    {
        foreach ($this->attributeSorters as $sorter) {
            if ($sorter->supportsAttribute($attribute)) {
                $sorter->setQueryBuilder($this->getQueryBuilder());
                $sorter->addAttributeSorter($attribute, $direction);

                return $this;
            }
        }

        throw new \LogicException(
            sprintf(
                'Sort attribute "%s" (%s) is not supported',
                $attribute->getCode(),
                $attribute->getAttributeType()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        foreach ($this->fieldSorters as $sorter) {
            if ($sorter->supportsField($field)) {
                $sorter->setQueryBuilder($this->getQueryBuilder());
                $sorter->addFieldSorter($field, $direction);

                return $this;
            }
        }

        throw new \LogicException(sprintf('Sort field "%s" is not supported', $field));
    }

    /**
     * Register the filter
     *
     * @param FilterInterface $filter
     */
    public function registerFilter(FilterInterface $filter)
    {
        if ($filter instanceof FieldFilterInterface) {
            $this->fieldFilters[]= $filter;
        }
        if ($filter instanceof AttributeFilterInterface) {
            $this->attributeFilters[]= $filter;
        }
    }

    /**
     * Register the sorter
     *
     * @param SorterInterface $sorter
     */
    public function registerSorter(SorterInterface $sorter)
    {
        if ($sorter instanceof FieldSorterInterface) {
            $this->fieldSorters[]= $sorter;
        }
        if ($sorter instanceof AttributeSorterInterface) {
            $this->attributeSorters[]= $sorter;
        }
    }
}
