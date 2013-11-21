<?php

namespace Pim\Bundle\CatalogBundle\Twig;

use Doctrine\Common\Collections\Collection;

use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Twig extension to render category from twig templates
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryExtension extends \Twig_Extension
{
    /**
     * @var CategoryManager
     */
    protected $categoryManager;

    /**
     * Constructor
     *
     * @param CategoryManager $categoryManager
     */
    public function __construct(CategoryManager $categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'list_categories_response' => new \Twig_Function_Method($this, 'listCategoriesResponse'),
            'count_products' => new \Twig_Function_Method($this, 'countProducts'),
            'define_state'   => new \Twig_Function_Method($this, 'defineState')
        );
    }

    public function listCategoriesResponse(array $categories, Collection $selectedCategories)
    {
        $selectedIds = array();

        foreach ($selectedCategories as $selectedCategory) {
            $selectedIds[] = $selectedCategory->getId();
        }

        return $this->formatCategoriesAndCount($categories, $selectedIds, true);
    }

    protected function formatCategoriesAndCount(array $categories, $selectedIds = array(), $count = false)
    {
        $result = array();

        foreach ($categories as $category) {
            $children = $this->formatCategoriesAndCount($category['__children'], $selectedIds, $count);

            $selectedChildren = 0;

            foreach ($children as $child) {
                $selectedChildren += $child['selectedChildrenCount'];
                if (preg_match('/checked/', $child['state'])) {
                    $selectedChildren++;
                }
            }

            $label = $category['item']->getLabel();

            if ($selectedChildren > 0) {
                $label = sprintf('<strong>%s</strong>', $label);
            }

            $result[] = array(
                'attr' => array(
                    'id' => sprintf('node_%s', $category['item']->getId())
                ),
                'data' => $label,
                'state' => $this->defineCategoryState($category, $selectedIds),
                'children' => $children,
                'selectedChildrenCount' => $selectedChildren
            );
        }

        return $result;
    }

    /**
     * Count products for a category
     *
     * @param CategoryInterface $category
     * @param bool              $nested
     *
     * @return string
     */
    public function countProducts(CategoryInterface $category, $nested)
    {
        return $this
            ->categoryManager
            ->getEntityRepository()
            ->countProductsLinked($category, !$nested);
    }

    /**
     * Define the state of a category
     *
     * @param CategoryInterface $category
     * @param boolean           $hasChild
     * @param CategoryInterface $selectNode
     *
     * @return string
     */
    public function defineState(CategoryInterface $category, $hasChild = false, CategoryInterface $selectNode = null)
    {
        $state = $category->hasChildren() ? 'closed' : 'leaf';

        if ($hasChild === true) {
            $state = 'open';
        }

        if ($selectNode !== null && $category->getId() === $selectNode->getId()) {
            $state .= ' toselect';
        }

        if ($category->isRoot()) {
            $state .= ' jstree-root';
        }

        return $state;
    }

    /**
     * TODO : Refactor with the method "defineState" just above
     * @param array $category
     * @param array $selectedIds
     * @return string
     */
    protected function defineCategoryState(array $category, $selectedIds = null)
    {
        $children = $category['__children'];
        $category = $category['item'];

        if (count($children) > 0) {
            $state = 'open';
        } elseif ($category->hasChildren()) {
            $state = 'closed';
        } else {
            $state = 'leaf';
        }

        if (in_array($category->getId(), $selectedIds)) {
            $state .= ' jstree-checked';
        }

        if ($category->isRoot()) {
            $state .= ' jstree-root';
        }

        return $state;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_category_extension';
    }
}
