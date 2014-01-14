<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Twig;

use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Twig\CategoryExtension;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CategoryExtension
     */
    protected $categoryExtension;

    /**
     * @var CategoryManager
     */
    protected $categoryManager;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->categoryExtension = new CategoryExtension(
            $this->getCategoryManagerMock(),
            $this->getProductManagerMock()
        );
    }

    /**
     * Get category manager mock
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\CategoryManager
     */
    protected function getCategoryManagerMock()
    {
        $this->categoryManager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\CategoryManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->categoryManager;
    }

    /**
     * Get product manager mock
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\ProductManager
     */
    protected function getProductManagerMock()
    {
        $this->productManager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->productManager;
    }

    /**
     * Test related method
     */
    public function testGetName()
    {
        $this->assertEquals('pim_category_extension', $this->categoryExtension->getName());
    }

    /**
     * Test related method
     */
    public function testGetFunctions()
    {
        $twigFunctions = $this->categoryExtension->getFunctions();

        $this->assertFunction('children_response', 'childrenResponse', $twigFunctions);
        $this->assertFunction('children_tree_response', 'childrenTreeResponse', $twigFunctions);
        $this->assertFunction('list_categories_response', 'listCategoriesResponse', $twigFunctions);
        $this->assertFunction('list_trees_response', 'listTreesResponse', $twigFunctions);
    }

    /**
     * Assert twig function
     *
     * @param string $name
     * @param string $methodName
     * @param array  $twigFunctions
     */
    protected function assertFunction($name, $methodName, $twigFunctions)
    {
        $this->assertArrayHasKey($name, $twigFunctions);
        $this->assertTrue(method_exists($this->categoryExtension, $methodName));
        $this->assertInstanceOf('\Twig_Function_Method', $twigFunctions[$name]);
    }

    /**
     * Data provider for listTrees method
     *
     * @return array
     */
    public static function dataProviderListTrees()
    {
        return array(
            array(
                array(
                    array('id' => 1, 'label' => 'Selected tree'),
                    array('id' => 2, 'label' => 'Master catalog')
                ),
                1,
                5,
                array(
                    array('id' => 1, 'label' => 'Selected tree (5)', 'selected' => 'true'),
                    array('id' => 2, 'label' => 'Master catalog (5)', 'selected' => 'false')
                )
            )
        );
    }

    /**
     * Test related method
     *
     * @param array   $trees
     * @param integer $selectedTreeId
     * @param boolean $resultCount
     * @param array   $expectedResult
     *
     * @dataProvider dataProviderListTrees
     */
    public function testListTreesResponse(array $trees, $selectedTreeId, $resultCount, $expectedResult)
    {
        $this->productManager
            ->expects($this->any())
            ->method('getProductsCountInCategory')
            ->will($this->returnValue($resultCount));

        $treeEntities = array();
        foreach ($trees as $tree) {
            $treeEntities[] = $this->getCategoryMock($tree);
        }

        $result = $this->categoryExtension->listTreesResponse($treeEntities, $selectedTreeId);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Define result returned when count products in a category
     *
     * @param integer $resultCount
     */
    protected function defineCategoryCountResult($resultCount)
    {
        $productManager
            ->expects($this->any())
            ->method('getProductsCountInCategory')
            ->will($this->returnValue($resultCount));

        $this->categoryManager
            ->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));
    }

    /**
     * Get category mock
     *
     * @param array $properties
     *
     * @return CategoryInterface
     */
    protected function getCategoryMock(array $properties = array())
    {
        $category = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Category');

        if (isset($properties['id'])) {
            $category
                ->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($properties['id']));
        }

        if (isset($properties['label'])) {
            $category
                ->expects($this->any())
                ->method('getLabel')
                ->will($this->returnValue($properties['label']));
        }

        if (isset($properties['has_children'])) {
            $category
                ->expects($this->any())
                ->method('hasChildren')
                ->will($this->returnValue($properties['has_children']));
        }

        $isRoot = (isset($properties['is_root']) && $properties['is_root']);
        $category
            ->expects($this->any())
            ->method('isRoot')
            ->will($this->returnValue($isRoot));

        return $category;
    }

    /**
     * Get product mock
     *
     * @param array $properties
     *
     * @return ProductInterface
     */
    protected function getProductMock(array $properties = array())
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        if (isset($properties['id'])) {
            $product
                ->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($properties['id']));
        }

        if (isset($properties['identifier'])) {
            $product
                ->expects($this->any())
                ->method('getIdentifier')
                ->will($this->returnValue($properties['identifier']));
        }

        if (isset($properties['label'])) {
            $product
                ->expects($this->any())
                ->method('__toString')
                ->will($this->returnValue($properties['label']));
        }

        return $product;
    }
}
