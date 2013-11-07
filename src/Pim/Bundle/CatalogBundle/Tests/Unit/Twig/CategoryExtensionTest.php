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
        // open
        $category = $this->getCategoryMock();

        $state = $this->categoryExtension->defineState($category, true);
        $this->assertEquals('open', $state);

        // open + toselect
        $category = $this->getCategoryMock(array('is_root' => true));

        $state = $this->categoryExtension->defineState($category, true);
        $this->assertEquals('open jstree-root', $state);

        // open + toselect + root

        // open + root
    }

    public function testDefineStateWithSelectedNode()
    {
        $selectCategory = $this->getCategoryMock(array('id' => 3));

        // leaf + to select + root
        $category = $this->getCategoryMock(array('id' => 3));

        $state = $this->categoryExtension->defineState($category, false, $selectCategory);
        $this->assertEquals('leaf toselect', $state);
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
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Category')
            ->disableOriginalConstructor()
            ->getMock();

        if (isset($properties['id'])) {
            $category
                ->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($properties['id']));
        }

        if (isset($properties['parent'])) {
            $category
                ->expects($this->any())
                ->method('getParent')
                ->will($this->returnValue($properties['parent']));
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
