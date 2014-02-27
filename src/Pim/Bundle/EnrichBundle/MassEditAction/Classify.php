<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType;

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
     * @var ProductManager $manager
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
     * @param ProductManager  $manager
     * @param CategoryManager $categoryManager
     */
    public function __construct(ProductManager $manager, CategoryManager $categoryManager)
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
    public function perform(QueryBuilder $qb)
    {
        $products = $qb->getQuery()->getResult();
        foreach ($products as $product) {
            foreach ($this->getCategories() as $category) {
                $product->addCategory($category);
            }
        }
    }
}
