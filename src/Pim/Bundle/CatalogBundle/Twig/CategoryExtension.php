<?php

namespace Pim\Bundle\CatalogBundle\Twig;

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
            'count_products' => new \Twig_Function_Method($this, 'countProducts')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
//             'count_products' => new \Twig_Filter_Method($this, 'countProducts'),
            'state'          => new \Twig_Filter_Method($this, 'getState')
        );
    }

    /**
     * Count products for a category
     *
     * @param CategoryInterface $category
     * @param bool $nested
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
     * Return the state of the category (leaf if no children, closed otherwise)
     *
     * @param CategoryInterface $category
     *
     * @return string
     */
    public function getState(CategoryInterface $category)
    {
        $state = $category->hasChildren() ? 'closed' : 'leaf';

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
