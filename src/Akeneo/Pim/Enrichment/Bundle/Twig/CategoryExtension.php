<?php

namespace Akeneo\Pim\Enrichment\Bundle\Twig;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter\CategoryItemsCounterInterface;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter\CategoryItemsCounterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Doctrine\Common\Collections\Collection;
use Twig_SimpleFunction;

/**
 * Twig extension to render category from twig templates
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryExtension extends \Twig_Extension
{
    /** @var CategoryItemsCounterRegistryInterface */
    protected $categoryItemsCounter;

    /** @var int */
    protected $itemsLimitRemoval;

    public function __construct(CategoryItemsCounterRegistryInterface $categoryItemsCounter, int $itemsLimitRemoval = null)
    {
        $this->categoryItemsCounter = $categoryItemsCounter;
        $this->itemsLimitRemoval = $itemsLimitRemoval;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new Twig_SimpleFunction('children_response', fn(array $categories, CategoryInterface $parent = null, $withProductCount = false, $includeSub = false, $relatedEntity = 'product') => $this->childrenResponse($categories, $parent, $withProductCount, $includeSub, $relatedEntity)),
            new Twig_SimpleFunction('children_tree_response', fn(array $categories, CategoryInterface $selectedCategory = null, CategoryInterface $parent = null, $withProductCount = false, $includeSub = false, $relatedEntity = 'product') => $this->childrenTreeResponse($categories, $selectedCategory, $parent, $withProductCount, $includeSub, $relatedEntity)),
            new Twig_SimpleFunction('list_categories_response', fn(array $categories, Collection $selectedCategories, $relatedEntity = 'product') => $this->listCategoriesResponse($categories, $selectedCategories, $relatedEntity)),
            new Twig_SimpleFunction('list_trees_response', fn(array $trees, $selectedTreeId = null, $withProductCount = true, $includeSub = false, $relatedEntity = 'product') => $this->listTreesResponse($trees, $selectedTreeId, $withProductCount, $includeSub, $relatedEntity)),
            new Twig_SimpleFunction('exceeds_products_limit_for_removal', fn(CategoryInterface $category, $includeSub, $relatedEntity = 'product') => $this->exceedsProductsLimitForRemoval($category, $includeSub, $relatedEntity)),
            new Twig_SimpleFunction('get_products_limit_for_removal', fn() => $this->getProductsLimitForRemoval()),
        ];
    }

    /**
     * List root categories (trees) for jstree
     *
     * @param array  $trees
     * @param int    $selectedTreeId
     * @param bool   $withProductCount
     * @param bool   $includeSub
     * @param string $relatedEntity
     */
    public function listTreesResponse(
        array $trees,
        int $selectedTreeId = null,
        bool $withProductCount = true,
        bool $includeSub = false,
        string $relatedEntity = 'product'
    ): array {
        $return = [];
        foreach ($trees as $tree) {
            $return[] = $this->formatTree($tree, $selectedTreeId, $withProductCount, $includeSub, $relatedEntity);
        }

        return $return;
    }

    /**
     * Format a list of categories
     *
     * @param array             $categories
     * @param CategoryInterface $selectedCategory
     * @param CategoryInterface $parent
     * @param bool              $withProductCount
     * @param bool              $includeSub
     * @param string            $relatedEntity
     */
    public function childrenTreeResponse(
        array $categories,
        CategoryInterface $selectedCategory = null,
        CategoryInterface $parent = null,
        bool $withProductCount = false,
        bool $includeSub = false,
        string $relatedEntity = 'product'
    ): array {
        $selectedIds = [$selectedCategory->getId()];
        $result = $this->formatCategoriesFromArray(
            $categories,
            $selectedIds,
            $withProductCount,
            $includeSub,
            $relatedEntity
        );

        if ($parent !== null) {
            $result = $this->formatCategory(
                $parent,
                $selectedIds,
                $withProductCount,
                $includeSub,
                $result,
                $relatedEntity
            );
        }

        return $result;
    }

    /**
     * List categories and children
     *
     * @param array             $categories
     * @param CategoryInterface $parent
     * @param bool              $withProductCount
     * @param bool              $includeSub
     * @param string            $relatedEntity
     */
    public function childrenResponse(
        array $categories,
        CategoryInterface $parent = null,
        bool $withProductCount = false,
        bool $includeSub = false,
        string $relatedEntity = 'product'
    ): array {
        $result = $this->formatCategories($categories, [], $withProductCount, $includeSub, $relatedEntity);

        if ($parent !== null) {
            $result = $this->formatCategory($parent, [], $withProductCount, $includeSub, $result, $relatedEntity);
        }

        return $result;
    }

    /**
     * List categories
     *
     * @param array      $categories
     * @param Collection $selectedCategories
     * @param string     $relatedEntity
     */
    public function listCategoriesResponse(
        array $categories,
        Collection $selectedCategories,
        string $relatedEntity = 'product'
    ): array {
        $selectedIds = [];
        foreach ($selectedCategories as $selectedCategory) {
            $selectedIds[] = $selectedCategory->getId();
        }

        return $this->formatCategoriesAndCount($categories, $selectedIds, $relatedEntity);
    }

    /**
     * Check if specified category exceeds the products limit for removal
     *
     * @param CategoryInterface $category
     * @param bool              $includeSub
     * @param string            $relatedEntity
     *
     * @throws \Exception
     */
    public function exceedsProductsLimitForRemoval(CategoryInterface $category, bool $includeSub, string $relatedEntity = 'product'): bool
    {
        return null !== $this->itemsLimitRemoval &&
            $this->countItems($category, $includeSub, $relatedEntity) > $this->itemsLimitRemoval;
    }

    /**
     * Return the linked products limit for category removal
     */
    public function getProductsLimitForRemoval(): int
    {
        return $this->itemsLimitRemoval;
    }

    /**
     * Format categories from an array
     *
     * @param array  $categories
     * @param array  $selectedIds
     * @param bool   $withProductCount
     * @param bool   $includeSub
     * @param string $relatedEntity
     */
    protected function formatCategoriesFromArray(
        array $categories,
        array $selectedIds,
        bool $withProductCount = false,
        bool $includeSub = false,
        string $relatedEntity = 'product'
    ): array {
        $result = [];
        foreach ($categories as $category) {
            $result[] = $this->formatCategoryFromArray(
                $category,
                $selectedIds,
                $withProductCount,
                $includeSub,
                $relatedEntity
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
     * )
     *
     * @param array  $category
     * @param array  $selectedIds
     * @param bool   $withProductCount
     * @param bool   $includeSub
     * @param string $relatedEntity
     */
    protected function formatCategoryFromArray(
        array $category,
        array $selectedIds,
        bool $withProductCount = false,
        bool $includeSub = false,
        string $relatedEntity = 'product'
    ): array {
        $state = $this->defineCategoryStateFromArray($category, $selectedIds);
        $label = $this->getLabel($category['item'], $withProductCount, $includeSub, $relatedEntity);

        return [
            'attr'     => [
                'id'        => 'node_'. $category['item']->getId(),
                'data-code' => $category['item']->getCode()
            ],
            'data'     => $label,
            'state'    => $state,
            'children' => $this->formatCategoriesFromArray(
                $category['__children'],
                $selectedIds,
                $withProductCount,
                $includeSub,
                $relatedEntity
            )
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
     * )
     *
     * @param CategoryInterface $category
     * @param array             $selectedIds
     * @param bool              $withProductCount
     * @param bool              $includeSub
     * @param array             $children
     * @param string            $relatedEntity
     */
    protected function formatCategory(
        CategoryInterface $category,
        array $selectedIds = [],
        bool $withProductCount = false,
        bool $includeSub = false,
        array $children = [],
        string $relatedEntity = 'product'
    ): array {
        $state = $this->defineCategoryState($category, false, $selectedIds);
        $label = $this->getLabel($category, $withProductCount, $includeSub, $relatedEntity);

        $result = [
            'attr'  => [
                'id'        => 'node_'. $category->getId(),
                'data-code' => $category->getCode()
            ],
            'data'  => $label,
            'state' => $state
        ];

        if (!empty($children)) {
            $result['children'] = $children;
        }

        return $result;
    }

    /**
     * Format a tree for jstree js plugin
     * Returns an array formated as:
     * array(
     *     'id'       => int,    // the tree id
     *     'label'    => string, // the tree label
     *     'selected' => bool    // predicate to know if the tree is selected or not
     * )
     *
     * @param CategoryInterface $tree
     * @param int               $selectedTreeId
     * @param bool              $withProductCount
     * @param bool              $includeSub
     * @param string            $relatedEntity
     */
    protected function formatTree(
        CategoryInterface $tree,
        int $selectedTreeId,
        bool $withProductCount,
        bool $includeSub,
        string $relatedEntity
    ): array {
        $label = $this->getLabel($tree, $withProductCount, $includeSub, $relatedEntity);

        return [
            'id'       => $tree->getId(),
            'code'     => $tree->getCode(),
            'label'    => $label,
            'selected' => ($tree->getId() === $selectedTreeId) ? 'true' : 'false'
        ];
    }

    /**
     * Format categories
     *
     * @param array  $categories
     * @param array  $selectedIds
     * @param bool   $withProductCount
     * @param bool   $includeSub
     * @param string $relatedEntity
     */
    protected function formatCategories(
        array $categories,
        array $selectedIds = [],
        bool $withProductCount = false,
        bool $includeSub = false,
        string $relatedEntity = 'product'
    ): array {
        $result = [];
        foreach ($categories as $category) {
            $result[] = $this->formatCategory($category, [], $withProductCount, $includeSub, [], $relatedEntity);
        }

        return $result;
    }

    /**
     * Format categories counting selected children
     *
     * @param array  $categories
     * @param array  $selectedIds
     * @param string $relatedEntity
     */
    protected function formatCategoriesAndCount(array $categories, array $selectedIds = [], string $relatedEntity = 'product'): array
    {
        $result = [];
        foreach ($categories as $category) {
            $result[] = $this->formatCategoryAndCount($category, $selectedIds, $relatedEntity);
        }

        return $result;
    }

    /**
     * Format category and count selected children
     *
     * @param array  $category
     * @param array  $selectedIds
     * @param string $relatedEntity
     */
    protected function formatCategoryAndCount(array $category, array $selectedIds, string $relatedEntity): array
    {
        $children = $this->formatCategoriesAndCount($category['__children'], $selectedIds, $relatedEntity);

        $result = $this->formatCategoryFromArray($category, $selectedIds, false, false, $relatedEntity);
        $result['children'] = $children;

        // count children
        $selectedChildren = 0;
        foreach ($children as $child) {
            $selectedChildren += $child['selectedChildrenCount'];
            if (preg_match('/checked/', $child['state'])) {
                $selectedChildren++;
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
     * including|excluding sub-categories
     *
     * @param CategoryInterface $category
     * @param bool              $withCount
     * @param bool              $includeSub
     * @param string            $relatedEntity
     */
    protected function getLabel(
        CategoryInterface $category,
        bool $withCount = false,
        bool $includeSub = false,
        string $relatedEntity = 'product'
    ): string {
        $label = $category->getLabel();
        if ($withCount) {
            $label = $label .' ('. $this->countItems($category, $includeSub, $relatedEntity) .')';
        }

        return $label;
    }

    /**
     * Count items for a category
     *
     * @param CategoryInterface $category
     * @param bool              $includeSub
     * @param string            $relatedEntity
     */
    protected function countItems(CategoryInterface $category, bool $includeSub, string $relatedEntity = 'product'): int
    {
        $categoryItemsCounter = $this->getExtension($relatedEntity);

        return $categoryItemsCounter->getItemsCountInCategory($category, $includeSub);
    }

    /**
     * Define the state of a category
     *
     * @param CategoryInterface $category
     * @param bool              $hasChild
     * @param array             $selectedIds
     */
    protected function defineCategoryState(CategoryInterface $category, bool $hasChild = false, array $selectedIds = []): string
    {
        $state = $category->hasChildren() ? 'closed' : 'leaf';

        if ($hasChild) {
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
     * )
     *
     * @param array $category
     * @param array $selectedIds
     */
    protected function defineCategoryStateFromArray(array $category, array $selectedIds = []): string
    {
        $children = $category['__children'];
        $category = $category['item'];
        $hasChild = (count($children) > 0);

        return $this->defineCategoryState($category, $hasChild, $selectedIds);
    }

    /**
     * Get type of extension
     *
     * @param string $type
     *
     * @throws \Exception
     */
    protected function getExtension(string $type): CategoryItemsCounterInterface
    {
        $categoryItemsCounter = $this->categoryItemsCounter->get($type);

        if (null === $categoryItemsCounter) {
            throw new \Exception(sprintf('No category counter found for %s', $type));
        }

        return $categoryItemsCounter;
    }
}
