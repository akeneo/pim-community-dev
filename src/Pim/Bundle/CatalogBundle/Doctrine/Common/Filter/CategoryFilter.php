<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Filter;

use Akeneo\Component\Classification\Repository\CategoryFilterableRepositoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Product category filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFilter implements FieldFilterInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var CategoryFilterableRepositoryInterface */
    protected $itemCategoryRepo;

    /** @var ObjectIdResolverInterface */
    protected $objectIdResolver;

    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedFields;

    /** @var array */
    protected $supportedOperators;

    /**
     * @param CategoryRepositoryInterface           $categoryRepository
     * @param CategoryFilterableRepositoryInterface $itemCategoryRepo
     * @param ObjectIdResolverInterface             $objectIdResolver
     * @param string[]                              $supportedFields
     * @param string[]                              $supportedOperators
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategoryFilterableRepositoryInterface $itemCategoryRepo,
        ObjectIdResolverInterface $objectIdResolver,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->itemCategoryRepo = $itemCategoryRepo;
        $this->objectIdResolver = $objectIdResolver;
        $this->supportedFields = $supportedFields;
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

            $categoryIds = $this->objectIdResolver->getIdsFromCodes('category', $value);
        }

        switch ($operator) {
            case Operators::IN_LIST:
                $this->itemCategoryRepo->applyFilterByCategoryIds($this->qb, $categoryIds, true);
                break;
            case Operators::NOT_IN_LIST:
                $this->itemCategoryRepo->applyFilterByCategoryIds($this->qb, $categoryIds, false);
                break;
            case Operators::IN_CHILDREN_LIST:
                $categoryIds = $this->getAllChildrenIds($categoryIds);
                $this->itemCategoryRepo->applyFilterByCategoryIds($this->qb, $categoryIds, true);
                break;
            case Operators::NOT_IN_CHILDREN_LIST:
                $categoryIds = $this->getAllChildrenIds($categoryIds);
                $this->itemCategoryRepo->applyFilterByCategoryIds($this->qb, $categoryIds, false);
                break;
            case Operators::UNCLASSIFIED:
                $this->itemCategoryRepo->applyFilterByUnclassified($this->qb);
                break;
            case Operators::IN_LIST_OR_UNCLASSIFIED:
                $this->itemCategoryRepo->applyFilterByCategoryIdsOrUnclassified($this->qb, $categoryIds);
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
    public function getFields()
    {
        return $this->supportedFields;
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
        FieldFilterHelper::checkArray($field, $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
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
