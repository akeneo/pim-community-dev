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

    /** ProductQueryFilterRegistryInterface */
    protected $filterRegistry;

    /** ProductQuerySorterRegistryInterface */
    protected $sorterRegistry;

    /**
     * Constructor
     *
     * @param CatalogContext                      $catalogContext
     * @param AttributeRepository                 $attributeRepository
     * @param ProductQueryFilterRegistryInterface $filterRegistry
     * @param ProductQuerySorterRegistryInterface $sorterRegistry
     */
    public function __construct(
        CatalogContext $catalogContext,
        AttributeRepository $attributeRepository,
        ProductQueryFilterRegistryInterface $filterRegistry,
        ProductQuerySorterRegistryInterface $sorterRegistry
    ) {
        $this->context = $catalogContext;
        $this->attributeRepository = $attributeRepository;
        $this->filterRegistry = $filterRegistry;
        $this->sorterRegistry = $sorterRegistry;
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
                    sprintf(
                        'Filter on field "%s" is not supported or attribute %s not exists',
                        $field,
                        $field
                    )
                );
            }
            $applied = $this->addAttributeFilter($attribute, $operator, $value);
        }

        if (!$applied) {
            throw new \LogicException(
                sprintf(
                    'Filter on field "%s" is not supported',
                    $field,
                    $operator
                )
            );
        }

        return $this;
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
        // TODO : check operators in filters!

        $filter = $this->filterRegistry->getFieldFilter($field);
        if ($filter === null) {
            return false;
        }

        $filter->setQueryBuilder($this->getQueryBuilder());
        $filter->addFieldFilter($field, $operator, $value);

        return true;
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
        // TODO : check operators in filters!

        $filter = $this->filterRegistry->getAttributeFilter($attribute);
        if ($filter === null) {
            return false;
        }

        $filter->setQueryBuilder($this->getQueryBuilder());
        $filter->addAttributeFilter($attribute, $operator, $value);

        return true;
    }

    /**
     * Sort by field
     *
     * @param string $field     the field to sort on
     * @param string $direction the direction to use
     *
     * @throws \LogicException
     *
     * @return ProductQueryBuilderInterface
     */
    public function addSorter($field, $direction)
    {
        $applied = $this->addFieldSorter($field, $direction);

        if (!$applied) {
            $attribute = $this->attributeRepository->findOneByCode($field);
            if (!$attribute) {
                throw new \LogicException(
                    sprintf(
                        'Sorter on field "%s" is not supported or there is no attribute %s',
                        $field,
                        $field
                    )
                );
            }
            $applied = $this->addAttributeSorter($attribute, $direction);
        }

        if (!$applied) {
            throw new \LogicException(
                sprintf(
                    'Sorter on field "%s" is not supported',
                    $field
                )
            );
        }

        return $this;
    }

    /**
     * Sort by field
     *
     * @param string $field     the field to sort on
     * @param string $direction the direction to use
     *
     * @return boolean a sorter has been applied
     */
    protected function addFieldSorter($field, $direction)
    {
        $sorter = $this->sorterRegistry->getFieldSorter($field);
        if ($sorter === null) {
            return false;
        }

        $sorter->setQueryBuilder($this->getQueryBuilder());
        $sorter->addFieldSorter($field, $direction);

        return true;
    }

    /**
     * Sort by attribute value
     *
     * @param AbstractAttribute $attribute the attribute to sort on
     * @param string            $direction the direction to use
     *
     * @return boolean a sorter has been applied
     */
    protected function addAttributeSorter(AbstractAttribute $attribute, $direction)
    {
        $sorter = $this->sorterRegistry->getAttributeSorter($attribute);
        if ($sorter === null) {
            return false;
        }

        $sorter->setQueryBuilder($this->getQueryBuilder());
        $sorter->addAttributeSorter($attribute, $direction);

        return true;
    }
}
