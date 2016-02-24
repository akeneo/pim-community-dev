<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\Classification\Factory\CategoryFactory;
use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Category manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated Will be removed in 1.6
 */
class CategoryManager
{
    /** @var ObjectManager */
    protected $om;

    /** @var string */
    protected $categoryClass;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var CategoryFactory */
    protected $categoryFactory;

    /**
     * @param ObjectManager               $om
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryFactory             $categoryFactory
     * @param string                      $categoryClass
     */
    public function __construct(
        ObjectManager $om,
        CategoryRepositoryInterface $categoryRepository,
        CategoryFactory $categoryFactory,
        $categoryClass
    ) {
        $this->om                  = $om;
        $this->categoryRepository  = $categoryRepository;
        $this->categoryFactory     = $categoryFactory;
        $this->categoryClass       = $categoryClass;
    }

    /**
     * Return the entity repository reponsible for the category
     *
     * @return CategoryRepositoryInterface
     *
     * @deprecated Please inject "pim_catalog.repository.category" to retrieve the repository.
     */
    public function getEntityRepository()
    {
        return $this->categoryRepository;
    }

    /**
     * @return array
     *
     * @deprecated not used anymore, will be removed in 1.6
     */
    public function getTreeChoices()
    {
        $trees   = $this->categoryRepository->getTrees();
        $choices = [];
        foreach ($trees as $tree) {
            $choices[$tree->getId()] = $tree->getLabel();
        }

        return $choices;
    }

    /**
     * Get a collection of categories based on the array of id provided
     *
     * @param array $categoriesIds
     *
     * @return Collection of categories
     *
     * @deprecated Please use CategoryRepositoryInterface::getCategoriesByIds($categoriesIds) instead
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
     * @param CategoryInterface $root       Tree root category
     * @param Collection        $categories categories
     *
     * @return array Multi-dimensional array representing the tree
     *
     * TODO: To MOVE in the Repository. Try it in SQL.
     */
    public function getFilledTree(CategoryInterface $root, Collection $categories)
    {
        $parentsIds = [];

        foreach ($categories as $category) {
            $categoryParentsIds = [];
            $path               = $this->getEntityRepository()->getPath($category);

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
