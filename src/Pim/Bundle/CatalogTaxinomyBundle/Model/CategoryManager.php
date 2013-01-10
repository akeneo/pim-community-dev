<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Model;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Category;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\Common\Persistence\ObjectManager;
/**
 * Service class to manage categories
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryManager
{
    /**
     * {@inheritdoc}
     */
    public function getEntityShortname()
    {
        return 'PimCatalogTaxinomyBundle:Category';
    }

    /**
     * @var ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     */
    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get entity repository
     *
     * @return EntityRepository
     */
    public function getEntityRepository()
    {
        return $this->objectManager->getRepository($this->getEntityShortname());
    }

    /**
     * Get all categories
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->getEntityRepository()->findAll();
    }

    /**
     * Get all children for a parent category id
     * @param integer $parentId
     *
     * @return ArrayCollection
     */
    public function getChildren($parentId)
    {
        return $this->getEntityRepository()->getChildrenFromParentId($parentId);
    }

    /**
     * Search categories by criterias
     * @param array $criterias
     *
     * @return ArrayCollection
     */
    public function search($criterias)
    {
        return $this->getEntityRepository()->search($criterias);
    }

    /**
     * Get category by his id
     * @param integer $categoryId
     *
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category
     */
    public function getCategory($categoryId)
    {
        return $this->getEntityRepository()->findOneBy(array('id' => $categoryId));
    }

    /**
     * Persist a category entity
     * @param \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category $category
     */
    public function persist($category)
    {
        $this->manager->persist($category);
        $this->manager->flush();
    }

    /**
     * Remove a category from his id
     * @param integer $categoryId
     */
    public function removeFromId($categoryId)
    {
        $category = $this->getCategory($categoryId);
        $this->remove($category);
    }

    /**
     * Remove a category object
     * @param \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category $category
     */
    protected function remove($category)
    {
        $this->manager->remove($category);
        $this->manager->flush();
    }

    /**
     * Rename a category
     * @param integer $categoryId category id
     * @param string  $title      new title for category
     */
    public function rename($categoryId, $title)
    {
        $category = $this->getCategory($categoryId);
        $category->setTitle($title);
        $this->persist($category);
    }

    /**
     * Move a category to another parent
     * @param integer $categoryId  category to move
     * @param integer $referenceId parent category
     */
    public function move($categoryId, $referenceId)
    {
        // get categories
        $category  = $this->getCategory($categoryId);
        $reference = $this->getCategory($referenceId);

        $category->setParent($reference);

        $this->persist($category);
    }

    /**
     * Copy a category and link it to a parent category
     * @param integer $categoryId  category to copy
     * @param integer $referenceId parent category
     */
    public function copy($categoryId, $referenceId)
    {
        // get categories
        $category  = $this->getCategory($categoryId);
        $reference = $this->getCategory($referenceId);

        // copy all values and create child elements
        $newCategory = $this->copyInstance($category, $reference);

        $this->persist($newCategory);
    }

    /**
     * Recursive copy
     * @param \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category $category category copied
     * @param \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category $parent   parent category
     *
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category
     */
    protected function copyInstance($category, $parent)
    {
        // create a new category instance and copy values
        $newCategory = $this->getNewEntityInstance();
        $newCategory->setTitle($category->getTitle());
        $newCategory->setParent($parent);

        // copy children by recursion
        foreach ($category->getChildren() as $child) {
            $newChild = $this->copyInstance($child, $newCategory);
            $newCategory->addChildren($newChild);

            $this->persist($newCategory);
        }

        return $newCategory;
    }
}