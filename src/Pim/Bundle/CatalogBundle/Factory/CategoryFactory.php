<?php

namespace Pim\Bundle\CatalogBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Category factory
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFactory
{
    /** @var string */
    protected $categoryClass;

    /**
     * @param string $categoryClass
     */
    public function __construct($categoryClass)
    {
        $this->categoryClass = $categoryClass;
    }

    /**
     * Create a category instance
     *
     * @return CategoryInterface
     */
    public function createCategory()
    {
        $category = new $this->categoryClass();

        return $category;
    }
}
