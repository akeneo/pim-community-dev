<?php

namespace Pim\Bundle\ProductBundle\Manager;

use Oro\Bundle\SegmentationTreeBundle\Manager\SegmentManager;

use Pim\Bundle\ProductBundle\Entity\Category;
use Doctrine\Common\Collections\Collection;

/**
 * Extends SegmentManager for category tree
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
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

    /**
     * Provides a tree filled up to the categories provided, with all their ancestors
     * and ancestors sibligns are filled too, in order to be able to display the tree
     * directly without loading other data.
     *
     * @param Category   $root       Tree root category
     * @param Collection $categories categories
     *
     * @return array Multi-dimensional array representing the tree
     */
    public function getFilledTree(Category $root, Collection $categories)
    {
        $parentsIds = array();

        foreach ($categories as $category) {
            $categoryParentsIds = array();
            $path = $this->getEntityRepository()->getPath($category);

            if ($path[0]->getId() === $root->getId()) {
                foreach ($path as $pathItem) {
                    $categoryParentsIds[] = $pathItem->getId();
                }
            }
            $parentsIds = array_merge($parentsIds, $categoryParentsIds);
        }
        $parentsIds = array_unique($parentsIds);

        return $this->getEntityRepository()->getTreeFromParents($parentsIds);

    }
}
