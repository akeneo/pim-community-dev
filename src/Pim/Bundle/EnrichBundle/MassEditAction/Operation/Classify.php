<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;

/**
 * Batch operation to classify products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Classify extends ProductMassEditOperation
{
    /** @var CategoryManager $categoryManager */
    protected $categoryManager;

    /** @var CategoryInterface[] */
    protected $trees;

    /** @var CategoryInterface[] */
    protected $categories;

    /**
     * @param CategoryManager                     $categoryManager
     * @param BulkSaverInterface                  $productSaver
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param PaginatorFactoryInterface           $paginatorFactory
     * @param ObjectDetacherInterface             $objectDetacher
     */
    public function __construct(
        CategoryManager $categoryManager,
        BulkSaverInterface $productSaver,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PaginatorFactoryInterface $paginatorFactory,
        ObjectDetacherInterface $objectDetacher
    ) {
        parent::__construct($productSaver, $pqbFactory, $paginatorFactory, $objectDetacher);

        $this->categoryManager = $categoryManager;
        $this->trees           = $categoryManager->getEntityRepository()->findBy(['parent' => null]);
        $this->categories      = [];
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

    /**
     * {@inheritdoc}
     */
    protected function doPerform(ProductInterface $product)
    {
        foreach ($this->categories as $category) {
            $product->addCategory($category);
        }
    }

    /**
     * Get the form options to configure the operation
     *
     * @return array
     */
    public function getFormOptions()
    {
        return [];
    }
}
