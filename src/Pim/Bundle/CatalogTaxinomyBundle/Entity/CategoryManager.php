<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Entity;

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
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     * @param ObjectManager $objectManager
     */
    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get all children for a parent category id
     * @param integer $parentId
     *
     * @return ArrayCollection
     */
    public function getChildren($parentId)
    {
        return $this->getRepository()->findBy(array('parent' => $parentId));
    }

    /**
     * Get category by his id
     * @param integer $categoryId
     *
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category
     */
    public function getCategory($categoryId)
    {
        return $this->getRepository()->findOneBy(array('id' => $categoryId));
    }

    /**
     * Persist a category entity
     * @param \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category $category
     */
    public function persist($category)
    {
        $this->objectManager->persist($category);
        $this->objectManager->flush();
    }

    /**
     * Get repository
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->objectManager->getRepository('PimCatalogTaxinomyBundle:Category');
    }

    /**
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category
     */
    public function createNewInstance()
    {
        return new Category();
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
    public function remove($category)
    {
        $this->objectManager->remove($category);
        $this->objectManager->flush();
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
}