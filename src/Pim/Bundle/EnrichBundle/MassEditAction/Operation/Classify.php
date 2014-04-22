<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Batch operation to classify products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Classify extends ProductMassEditOperation
{
    /**
     * @var CategoryManager $categoryManager
     */
    protected $categoryManager;

    /**
     * @var CategoryInterface[]
     */
    protected $trees;

    /**
     * @var CategoryInterface[]
     */
    protected $categories;

    /**
     * @param CategoryManager $categoryManager
     */
    public function __construct(CategoryManager $categoryManager)
    {
        $this->categoryManager = $categoryManager;
        $this->trees           = $categoryManager->getEntityRepository()->findBy(array('parent' => null));
        $this->categories      = array();
    }

    /**
     * @return CategoryInterface[]
     */
    public function getTrees()
    {
        return $this->trees;
    }

    /**
     * @return CategoryInterface[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param CategoryInterface[] $categories
     *
     * @return Classify
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_classify';
    }

    protected function doPerform(ProductInterface $product)
    {
        foreach ($this->categories as $category) {
            $product->addCategory($category);
        }
    }
}
