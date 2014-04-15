<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Category manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryManager
{
    /**
     * @var ObjectManager $om
     */
    protected $om;

    /**
     * Class name for managed category
     *
     * @var string $categoryClass
     */
    protected $categoryClass;

    /**
     * Constructor
     *
     * @param ObjectManager $om
     * @param string        $categoryClass
     */
    public function __construct(ObjectManager $om, $categoryClass)
    {
        $this->om = $om;
        $this->categoryClass = $categoryClass;
    }

    /**
     * Return object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->om;
    }

    /**
     * Get a new category instance
     *
     * @return CategoryInterface
     */
    public function getCategoryInstance()
    {
        return new $this->categoryClass;
    }

    /**
     * Return category class name (mainly used in Doctrine context)
     *
     * @return string category class name
     */
    public function getCategoryClass()
    {
        return $this->categoryClass;
    }

    /**
     * Return the entity repository reponsible for the category
     *
     * @return CategoryRepository
     *
     * TODO: Inject CategoryRepository
     */
    public function getEntityRepository()
    {
        return $this->om->getRepository($this->getCategoryClass());
    }

    /**
     * Get a new tree instance
     *
     * @return CategoryInterface
     */
    public function getTreeInstance()
    {
        $tree = $this->getCategoryInstance();
        $tree->setParent(null);

        return $tree;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrees()
    {
        return $this->getEntityRepository()->getChildren(null, true, 'created', 'DESC');
    }

    /**
     * Get all direct children for a parent category id.
     * If the $selectNodeId is provided, all the children
     * level needed to provides the selectNode are returned
     *
     * @param integer $parentId
     * @param integer $selectNodeId
     *
     * @return ArrayCollection
     */
    public function getChildren($parentId, $selectNodeId = false)
    {
        $children = array();

        $entityRepository = $this->getEntityRepository();

        if ($selectNodeId === false) {
            $children = $entityRepository->getChildrenByParentId($parentId);
        } else {
            $children = $entityRepository->getChildrenTreeByParentId($parentId, $selectNodeId);
        }

        return $children;
    }

    /**
     * {@inheritdoc}
     */
    public function getTreeChoices()
    {
        $trees = $this->getTrees();
        $choices = array();
        foreach ($trees as $tree) {
            $choices[$tree->getId()] = $tree;
        }

        return $choices;
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
     * @param CategoryInterface $root       Tree root category
     * @param Collection        $categories categories
     *
     * @return array Multi-dimensional array representing the tree
     */
    public function getFilledTree(CategoryInterface $root, Collection $categories)
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

    /**
     * Get tree by code
     *
     * @param string $code
     *
     * @return CategoryInterface
     */
    public function getTreeByCode($code)
    {
        return $this
            ->getEntityRepository()
            ->findOneBy(array('code' => $code, 'parent' => null));
    }

    /**
     * Get category by code
     *
     * @param string $code
     *
     * @return CategoryInterface
     */
    public function getCategoryByCode($code)
    {
        return $this
            ->getEntityRepository()
            ->findOneBy(array('code' => $code));
    }

    /**
     * {@inheritdoc}
     */
    public function remove(CategoryInterface $category)
    {
        if ($category instanceof CategoryInterface) {
            foreach ($category->getProducts() as $product) {
                $product->removeCategory($category);
            }
        }

        parent::remove($category);
    }
}
