<?php

namespace Pim\Bundle\CatalogBundle\MassEditAction;

use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\CatalogBundle\Form\Type\MassEditAction\ClassifyType;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;

/**
 * Batch operation to classify products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Classify extends AbstractMassEditAction
{
    /**
     * @var FlexibleManager $manager
     */
    protected $manager;

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
     * @param FlexibleManager $manager
     * @param CategoryManager $categoryManager
     */
    public function __construct(FlexibleManager $manager, CategoryManager $categoryManager)
    {
        $this->manager         = $manager;
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
        return new ClassifyType();
    }

    /**
     * {@inheritdoc}
     */
    public function perform(array $products)
    {
        foreach ($products as $product) {
            foreach ($this->getCategories() as $category) {
                $product->addCategory($category);
                $this->manager->getStorageManager()->persist($product);
            }
        }
        $this->manager->getStorageManager()->flush();
    }
}
