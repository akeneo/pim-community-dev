<?php

namespace Akeneo\Category\Infrastructure\Twig;

use Akeneo\Category\Infrastructure\Component\CategoryItemsCounterInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Doctrine\ORM\Counter\CategoryItemsCounterRegistry;
use Doctrine\Common\Collections\Collection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension to render category from twig templates.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryExtension extends AbstractExtension
{
    /** @var CategoryItemsCounterRegistry */
    protected $categoryItemsCounter;

    /** @var int */
    protected $itemsLimitRemoval;

    public function __construct(CategoryItemsCounterRegistry $categoryItemsCounter, $itemsLimitRemoval = null)
    {
        $this->categoryItemsCounter = $categoryItemsCounter;
        $this->itemsLimitRemoval = $itemsLimitRemoval;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('children_response', [$this, 'childrenResponse']),
            new TwigFunction('children_tree_response', [$this, 'childrenTreeResponse']),
            new TwigFunction('list_categories_response', [$this, 'listCategoriesResponse']),
            new TwigFunction('exceeds_products_limit_for_removal', [$this, 'exceedsProductsLimitForRemoval']),
            new TwigFunction('get_products_limit_for_removal', [$this, 'getProductsLimitForRemoval']),
        ];
    }

    /**
     * Format a list of categories.
     *
     * @param CategoryInterface $selectedCategory
     * @param CategoryInterface $parent
     * @param bool $withProductCount
     * @param bool $includeSub
     * @param string $relatedEntity
     *
     * @return array
     */
    public function childrenTreeResponse(
        array $categories,
        ?CategoryInterface $selectedCategory = null,
        ?CategoryInterface $parent = null,
        $withProductCount = false,
        $includeSub = false,
        $relatedEntity = 'product',
    ) {
        $selectedIds = [$selectedCategory->getId()];
        $result = $this->formatCategoriesFromArray(
            $categories,
            $selectedIds,
            $withProductCount,
            $includeSub,
            $relatedEntity,
        );

        if ($parent !== null) {
            $result = $this->formatCategory(
                $parent,
                $selectedIds,
                $withProductCount,
                $includeSub,
                $result,
                $relatedEntity,
            );
        }

        return $result;
    }

    /**
     * List categories and children.
     *
     * @param CategoryInterface $parent
     * @param bool $withProductCount
     * @param bool $includeSub
     * @param string $relatedEntity
     *
     * @return array
     */
    public function childrenResponse(
        array $categories,
        ?CategoryInterface $parent = null,
        $withProductCount = false,
        $includeSub = false,
        $relatedEntity = 'product',
    ) {
        $result = $this->formatCategories($categories, [], $withProductCount, $includeSub, $relatedEntity);

        if ($parent !== null) {
            $result = $this->formatCategory($parent, [], $withProductCount, $includeSub, $result, $relatedEntity);
        }

        return $result;
    }

    /**
     * List categories.
     *
     * @param string $relatedEntity
     *
     * @return array
     */
    public function listCategoriesResponse(
        array $categories,
        Collection $selectedCategories,
        $relatedEntity = 'product',
    ) {
        $selectedIds = [];
        foreach ($selectedCategories as $selectedCategory) {
            $selectedIds[] = $selectedCategory->getId();
        }

        return $this->formatCategoriesAndCount($categories, $selectedIds, $relatedEntity);
    }

    /**
     * Check if specified category exceeds the products limit for removal.
     *
     * @param bool $includeSub
     * @param string $relatedEntity
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function exceedsProductsLimitForRemoval(CategoryInterface $category, $includeSub, $relatedEntity = 'product')
    {
        return null !== $this->itemsLimitRemoval &&
            $this->countItems($category, $includeSub, $relatedEntity) > $this->itemsLimitRemoval;
    }

    /**
     * Return the linked products limit for category removal.
     *
     * @return int
     */
    public function getProductsLimitForRemoval()
    {
        return $this->itemsLimitRemoval;
    }

    /**
     * Format categories from an array.
     *
     * @param bool $withProductCount
     * @param bool $includeSub
     * @param string $relatedEntity
     *
     * @return array
     */
    protected function formatCategoriesFromArray(
        array $categories,
        array $selectedIds,
        $withProductCount = false,
        $includeSub = false,
        $relatedEntity = 'product',
    ) {
        $result = [];
        foreach ($categories as $category) {
            $result[] = $this->formatCategoryFromArray(
                $category,
                $selectedIds,
                $withProductCount,
                $includeSub,
                $relatedEntity,
            );
        }

        return $result;
    }

    /**
     * Format a category from an array
     * Returns an array formatted as:
     * array(
     *     'attr'     => array(
     *         'id' => 'node_' + <categoryId>
     *     ),
     *     'data'     => string, // the category label + count if needed
     *     'state'    => string, // the css classes for category state
     *     'children' => array() // the same array for children
     * ).
     *
     * @param bool $withProductCount
     * @param bool $includeSub
     * @param string $relatedEntity
     *
     * @return array
     */
    protected function formatCategoryFromArray(
        array $category,
        array $selectedIds,
        $withProductCount = false,
        $includeSub = false,
        $relatedEntity = 'product',
    ) {
        $state = $this->defineCategoryStateFromArray($category, $selectedIds);
        $label = $this->getLabel($category['item'], $withProductCount, $includeSub, $relatedEntity);

        return [
            'attr' => [
                'id' => 'node_'.$category['item']->getId(),
                'data-code' => $category['item']->getCode(),
            ],
            'data' => $label,
            'state' => $state,
            'children' => $this->formatCategoriesFromArray(
                $category['__children'],
                $selectedIds,
                $withProductCount,
                $includeSub,
                $relatedEntity,
            ),
        ];
    }

    /**
     * Format a category from an array
     * Returns an array formatted as:
     * array(
     *     'attr'     => array(
     *         'id' => 'node_' + <categoryId>
     *     ),
     *     'data'     => string, // the category label + count if needed
     *     'state'    => string, // the css classes for category state
     * ).
     *
     * @param bool $withProductCount
     * @param bool $includeSub
     * @param string $relatedEntity
     *
     * @return array
     */
    protected function formatCategory(
        CategoryInterface $category,
        array $selectedIds = [],
        $withProductCount = false,
        $includeSub = false,
        array $children = [],
        $relatedEntity = 'product',
    ) {
        $state = $this->defineCategoryState($category, false, $selectedIds);
        $label = $this->getLabel($category, $withProductCount, $includeSub, $relatedEntity);

        $result = [
            'attr' => [
                'id' => 'node_'.$category->getId(),
                'data-code' => $category->getCode(),
            ],
            'data' => $label,
            'state' => $state,
        ];

        if (!empty($children)) {
            $result['children'] = $children;
        }

        return $result;
    }

    /**
     * Format categories.
     *
     * @param array $selectedIds
     * @param bool $withProductCount
     * @param bool $includeSub
     * @param string $relatedEntity
     *
     * @return array
     */
    protected function formatCategories(
        array $categories,
        $selectedIds = [],
        $withProductCount = false,
        $includeSub = false,
        $relatedEntity = 'product',
    ) {
        $result = [];
        foreach ($categories as $category) {
            $result[] = $this->formatCategory($category, [], $withProductCount, $includeSub, [], $relatedEntity);
        }

        return $result;
    }

    /**
     * Format categories counting selected children.
     *
     * @param array $selectedIds
     * @param string $relatedEntity
     *
     * @return array
     */
    protected function formatCategoriesAndCount(array $categories, $selectedIds = [], $relatedEntity = 'product')
    {
        $result = [];
        foreach ($categories as $category) {
            $result[] = $this->formatCategoryAndCount($category, $selectedIds, $relatedEntity);
        }

        return $result;
    }

    /**
     * Format category and count selected children.
     *
     * @param string $relatedEntity
     *
     * @return array
     */
    protected function formatCategoryAndCount(array $category, array $selectedIds, $relatedEntity)
    {
        $children = $this->formatCategoriesAndCount($category['__children'], $selectedIds, $relatedEntity);

        $result = $this->formatCategoryFromArray($category, $selectedIds, false, false, $relatedEntity);
        $result['children'] = $children;

        // count children
        $selectedChildren = 0;
        foreach ($children as $child) {
            $selectedChildren += $child['selectedChildrenCount'];
            if (preg_match('/checked/', $child['state'])) {
                ++$selectedChildren;
            }
        }
        $result['selectedChildrenCount'] = $selectedChildren;

        // set label in bold
        if ($selectedChildren > 0) {
            $result['data'] = sprintf('%s', $result['data']);
        }

        return $result;
    }

    /**
     * Returns category label with|without children count
     * including|excluding sub-categories.
     *
     * @param bool $withCount
     * @param bool $includeSub
     * @param string $relatedEntity
     *
     * @return string
     */
    protected function getLabel(
        CategoryInterface $category,
        $withCount = false,
        $includeSub = false,
        $relatedEntity = 'product',
    ) {
        $label = $category->getLabel();
        if ($withCount) {
            $label = $label.' ('.$this->countItems($category, $includeSub, $relatedEntity).')';
        }

        return $label;
    }

    /**
     * Count items for a category.
     *
     * @param bool $includeSub
     * @param string $relatedEntity
     *
     * @return int
     */
    protected function countItems(CategoryInterface $category, $includeSub, $relatedEntity = 'product')
    {
        $categoryItemsCounter = $this->getExtension($relatedEntity);

        return $categoryItemsCounter->getItemsCountInCategory($category, $includeSub);
    }

    /**
     * Define the state of a category.
     *
     * @param bool $hasChild
     *
     * @return string
     */
    protected function defineCategoryState(CategoryInterface $category, $hasChild = false, array $selectedIds = [])
    {
        $state = $category->hasChildren() ? 'closed' : 'leaf';

        if ($hasChild === true) {
            $state = 'open';
        }

        if (in_array($category->getId(), $selectedIds)) {
            $state .= ' toselect jstree-checked';
        }

        if ($category->isRoot()) {
            $state .= ' jstree-root';
        }

        return $state;
    }

    /**
     * Define category state from a category array
     * array(
     *     'item'       => CategoryInterface,
     *     '__children' => array()
     * ).
     *
     * @param array $selectedIds
     *
     * @return string
     */
    protected function defineCategoryStateFromArray(array $category, $selectedIds = [])
    {
        $children = $category['__children'];
        $category = $category['item'];
        $hasChild = (count($children) > 0);

        return $this->defineCategoryState($category, $hasChild, $selectedIds);
    }

    /**
     * Get type of extension.
     *
     * @param string $type
     *
     * @return CategoryItemsCounterInterface
     *
     * @throws \Exception
     */
    protected function getExtension($type)
    {
        $categoryItemsCounter = $this->categoryItemsCounter->get($type);

        if (null === $categoryItemsCounter) {
            throw new \Exception(sprintf('No category counter found for %s', $type));
        }

        return $categoryItemsCounter;
    }
}
