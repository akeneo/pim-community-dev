<?php
namespace Pim\Bundle\ProductBundle\Manager;

use Oro\Bundle\SegmentationTreeBundle\Manager\SegmentManager;

use Pim\Bundle\ProductBundle\Entity\Category;

/**
 * Extends SegmentManager for category tree
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryManager extends SegmentManager
{

    /**
     * Get a new tree instance
     *
     * @return Category
     */
    public function getTreeInstance()
    {
        $tree = $this->getSegmentInstance();
        $tree->setParent(null);

        return $tree;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrees()
    {
        $entityRepository = $this->getEntityRepository();

        return $entityRepository->getChildren(null, true, 'created', 'DESC');
    }

    /**
     * Get a collection of categories based on the array of id provided
     * 
     * @param array $categoriesIds
     *
     * @return Collection of categories
     */
    public function getCategoriesByIds($categoriesIds)
    {
        return $this->getEntityRepository()->getCategoriesByIds($categoriesIds);

    }
}
