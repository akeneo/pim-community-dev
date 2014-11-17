<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Doctrine\ORM\QueryBuilder;

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

    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedFields;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the base filter
     *
     * @param CategoryRepository                 $categoryRepo
     * @param ProductCategoryRepositoryInterface $productRepo
     * @param array                              $supportedFields
     * @param array                              $supportedOperators
     */
    public function __construct(
        CategoryRepository $categoryRepo,
        ProductCategoryRepositoryInterface $productRepo,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->categoryRepository = $categoryRepo;
        $this->productRepository = $productRepo;
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null)
    {
        $categoryIds = $value;

        if ($operator === 'IN') {
            $this->productRepository->applyFilterByCategoryIds($this->qb, $categoryIds, true);

        } elseif ($operator === 'NOT IN') {
            $this->productRepository->applyFilterByCategoryIds($this->qb, $categoryIds, false);

        } elseif ($operator === 'IN CHILDREN') {
            $categoryIds = $this->getAllChildrenIds($categoryIds);
            $this->productRepository->applyFilterByCategoryIds($this->qb, $categoryIds, true);

        } elseif ($operator === 'NOT IN CHILDREN') {
            $categoryIds = $this->getAllChildrenIds($categoryIds);
            $this->productRepository->applyFilterByCategoryIds($this->qb, $categoryIds, false);

        } elseif ($operator === 'UNCLASSIFIED') {
            $this->productRepository->applyFilterByUnclassified($this->qb);

        } elseif ($operator === 'IN OR UNCLASSIFIED') {
            $this->productRepository->applyFilterByCategoryIdsOrUnclassified($this->qb, $categoryIds);
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
