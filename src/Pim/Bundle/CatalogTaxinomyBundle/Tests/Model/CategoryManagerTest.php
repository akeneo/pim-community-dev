<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Tests\Model;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Category;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryManagerTest extends KernelAwareTest
{
    /**
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Model\CategoryManager
     */
    protected function getManager()
    {
        return $this->container->get('pim.catalog_taxinomy.category_manager');
    }

    /**
     * Assert entity is a Category entity
     * @param object $entity
     */
    protected function assertInstanceOfCategory($entity)
    {
        $this->assertInstanceOf('\Pim\Bundle\CatalogTaxinomyBundle\Entity\Category', $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        // Create two categories
        $this->category1 = new Category();
        $this->category1->setTitle('Category1');

        $this->category2 = new Category();
        $this->category2->setTitle('Category2');
        $this->category2->setParent($this->category1);

//         $this->entityManager->persist($this->category1);
//         $this->entityManager->persist($this->category2);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        parent::tearDown();
//         $this->entityManager->remove($this->category1);
//         $this->entityManager->remove($this->category2);
    }

    /**
     * test related method
     */
    public function testCopy()
    {
        $this->assertTrue(true);
    }

    /**
     * test related method
     */
    public function testGetNewEntityInstance()
    {
        $category = $this->getManager()->getNewEntityInstance();
        $this->assertInstanceOfCategory($category);
    }

    /**
     * test related method
     */
    public function testGetCategories()
    {
        $categories = $this->getManager()->getCategories();
//         $this->assertCount(20, $categories);
        foreach ($categories as $category) {
            $this->assertInstanceOfCategory($category);
        }
        // TODO : Assert ordering by title
    }

    /**
     * test related method
     */
    public function testGetCategory()
    {
        $category = $this->getManager()->getCategory(1);

        $this->assertInstanceOfCategory($category);
        $this->assertEquals('computers', $category->getTitle());
    }

    /**
     * Test related method
     */
//     public function testGetChildren()
//     {
//         // initialize variables
//         $parentId = 1;
//         $listChildren = array('desktop', 'laptop', 'server', 'tablet');
//         $index = 0;

//         // recover objects
//         $parent = $this->getManager()->getCategory($parentId);
//         $categories = $this->getManager()->getChildren($parentId);

//         // asserts
//         $this->assertCount(count($parent->getChildren()), $categories);
//         foreach ($categories as $category) {
//             $this->assertInstanceOfCategory($category);
//             $this->assertEquals($listChildren[$index++], $category->getTitle());
//         }
//     }

    /**
     * test related method
     */
    public function testMove()
    {

    }

    /**
     * test persist and removeFromId methods
     */
    public function testPersistAndRemoveFromId()
    {
        // count number of categories at start
        $startCategories = $this->getManager()->getCategories();
        $startCount      = count($startCategories);

        // add a category and count categories
        $category = $this->getManager()->getNewEntityInstance();
        $this->getManager()->persist($category);
        $categories = $this->getManager()->getCategories();

        $this->assertCount($startCount+1, $categories);

        // remove the last category inserted and assert count values
        $newId = $category->getId();
        $this->getManager()->removeFromId($newId);
        $endCategories = $this->getManager()->getCategories();
        $this->assertCount($startCount, $endCategories);

        foreach ($endCategories as $category) {
            $this->assertNotEquals($newId, $category->getId());
        }
    }

    /**
     * test related method
     */
    public function testRename()
    {

    }

    /**
     * test related method
     */
    public function testSearch()
    {
        $criterias = array('title' => 'hard drive');

        $categories = $this->getManager()->search($criterias);

        // asserts
        $this->assertCount(2, $categories);
        foreach ($categories as $category) {
            $this->assertInstanceOfCategory($category);
        }

        // TODO : assert the 2 categories contains "hard drive" in their title
    }

}