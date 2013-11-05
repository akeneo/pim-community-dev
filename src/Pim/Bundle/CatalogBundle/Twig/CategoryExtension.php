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
    public function getFilters()
    {
        return array(
            'count_products' => new \Twig_Filter_Method($this, 'countProducts')
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
    public function countProducts($category, $nested)
    {
        if ($category instanceof CategoryInterface) {
            return $this
                ->categoryManager
                ->getEntityRepository()
                ->countProductsLinked($category, !$nested);
        } else {
            throw \Twig_Error_Runtime('"count_products" filter is only allowed for CategoryInterface');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_category_extension';
    }
}
