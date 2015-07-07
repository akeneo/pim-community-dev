<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Filter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;

/**
 * Category filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFilter implements FieldFilterInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var ProductCategoryRepositoryInterface */
    protected $productRepository;

    /** @var ObjectIdResolverInterface */
    protected $objectIdResolver;

    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedFields;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the base filter
     *
     * @param CategoryRepositoryInterface        $categoryRepository
     * @param ProductCategoryRepositoryInterface $productRepository
     * @param ObjectIdResolverInterface          $objectIdResolver
     * @param array                              $supportedFields
     * @param array                              $supportedOperators
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        ProductCategoryRepositoryInterface $productRepository,
        ObjectIdResolverInterface $objectIdResolver,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository  = $productRepository;
        $this->objectIdResolver   = $objectIdResolver;
        $this->supportedFields    = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        $categoryIds = $value;
        if ($operator !== Operators::UNCLASSIFIED) {
            $this->checkValue($field, $value);

            if (FieldFilterHelper::getProperty($field) === FieldFilterHelper::CODE_PROPERTY) {
                $categoryIds = $this->objectIdResolver->getIdsFromCodes('category', $value);
            }
        }

        switch ($operator) {
            case Operators::IN_LIST:
                $this->productRepository->applyFilterByCategoryIds($this->qb, $categoryIds, true);
                break;
            case Operators::NOT_IN_LIST:
                $this->productRepository->applyFilterByCategoryIds($this->qb, $categoryIds, false);
                break;
            case Operators::IN_CHILDREN_LIST:
                $categoryIds = $this->getAllChildrenIds($categoryIds);
                $this->productRepository->applyFilterByCategoryIds($this->qb, $categoryIds, true);
                break;
            case Operators::NOT_IN_CHILDREN_LIST:
                $categoryIds = $this->getAllChildrenIds($categoryIds);
                $this->productRepository->applyFilterByCategoryIds($this->qb, $categoryIds, false);
                break;
            case Operators::UNCLASSIFIED:
                $this->productRepository->applyFilterByUnclassified($this->qb);
                break;
            case Operators::IN_LIST_OR_UNCLASSIFIED:
                $this->productRepository->applyFilterByCategoryIdsOrUnclassified($this->qb, $categoryIds);
                break;
        }

        return $this;
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
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator)
    {
        return in_array($operator, $this->supportedOperators);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperators()
    {
        return $this->supportedOperators;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, 'category');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, 'category');
        }
    }

    /**
     * Get children category ids
     *
     * @param integer[] $categoryIds
     *
     * @return integer[]
     */
    protected function getAllChildrenIds(array $categoryIds)
    {
        $allChildrenIds = [];
        foreach ($categoryIds as $categoryId) {
            $category = $this->categoryRepository->find($categoryId);
            $childrenIds = $this->categoryRepository->getAllChildrenIds($category);
            $childrenIds[] = $category->getId();
            $allChildrenIds = array_merge($allChildrenIds, $childrenIds);
        }

        return $allChildrenIds;
    }
}
