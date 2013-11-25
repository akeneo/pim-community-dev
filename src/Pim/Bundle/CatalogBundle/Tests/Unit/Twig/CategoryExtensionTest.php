<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Twig;

use Pim\Bundle\CatalogBundle\Model\Category;
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
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->categoryExtension = new CategoryExtension($this->getCategoryManagerMock());
    }

    /**
     * Get category manager mock
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\CategoryManagerCategoryManager
     */
    protected function getCategoryManagerMock()
    {
        $categoryManagerMock = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\CategoryManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $categoryManagerMock;
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

        $this->assertCount(2, $twigFunctions);

        $this->assertFunction('count_products', 'countProducts', $twigFunctions);
        $this->assertFunction('define_state', 'defineState', $twigFunctions);
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
     * Test defineState method without child and without selected node
     */
    public function testDefineStateWithoutChildAndWithoutSelectedNode()
    {
        // test leaf state
        $category = $this->getCategoryMock();

        $state = $this->categoryExtension->defineState($category);
        $this->assertEquals('leaf', $state);

        // test leaf state with root node
        $category = $this->getCategoryMock(array('is_root' => true));

        $state = $this->categoryExtension->defineState($category);
        $this->assertEquals('leaf jstree-root', $state);

        // test closed state
        $category = $this->getCategoryMock(array('has_children' => true));

        $state = $this->categoryExtension->defineState($category);
        $this->assertEquals('closed', $state);

        // test leaf state with root node
        $category = $this->getCategoryMock(array('has_children' => true, 'is_root' => true));

        $state = $this->categoryExtension->defineState($category);
        $this->assertEquals('closed jstree-root', $state);
    }

    /**
     * Test defineState method with child parameter to true
     */
    public function testDefineStateWithChild()
    {
        // test open state
        $category = $this->getCategoryMock();

        $state = $this->categoryExtension->defineState($category, true);
        $this->assertEquals('open', $state);

        // test open state with root node
        $category = $this->getCategoryMock(array('is_root' => true));

        $state = $this->categoryExtension->defineState($category, true);
        $this->assertEquals('open jstree-root', $state);
    }

    /**
     * Test defineState method with selected node
     */
    public function testDefineStateWithSelectedNode()
    {
        $selectedCategory = $this->getCategoryMock(array('id' => 3));

        // test open state with selected node
        $category = $this->getCategoryMock(array('id' => 3));

        $state = $this->categoryExtension->defineState($category, true, $selectedCategory);
        $this->assertEquals('open toselect', $state);

        // test leaf state with selected node
        $state = $this->categoryExtension->defineState($category, false, $selectedCategory);
        $this->assertEquals('leaf toselect', $state);

        // test close state with selected node
        $category = $this->getCategoryMock(array('id' => 3, 'is_root' => true));

        $state = $this->categoryExtension->defineState($category, false, $selectedCategory);
        $this->assertEquals('leaf toselect jstree-root', $state);

        // test close state with different selected node
        $seletedCategory = $this->getCategoryMock(array('id' => 5));

        $state = $this->categoryExtension->defineState($category, false, $seletedCategory);
        $this->assertEquals('leaf jstree-root', $state);
    }

    /**
     * Get category mock
     *
     * @param array $properties
     *
     * @return Category
     */
    protected function getCategoryMock(array $properties = array())
    {
        $category = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Model\Category')
            ->disableOriginalConstructor()
            ->getMock();

        if (isset($properties['id'])) {
            $category
                ->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($properties['id']));
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
}
