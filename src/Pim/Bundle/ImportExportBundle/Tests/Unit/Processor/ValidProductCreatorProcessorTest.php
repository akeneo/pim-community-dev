<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\ValidProductCreationProcessor;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidProductCreatorProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->em             = $this->getEntityManagerMock();
        $this->formFactory    = $this->getFormFactoryMock();
        $this->productManager = $this->getProductManagerMock();

        $this->processor = new ValidProductCreationProcessor(
            $this->em,
            $this->formFactory,
            $this->productManager
        );
    }

    public function testProcess()
    {
        $attributeRepository = $this->getRepositoryMock();
        $categoryRepository  = $this->getRepositoryMock();
        $repoMap = array(
            array('PimProductBundle:ProductAttribute', $attributeRepository),
            array('PimProductBundle:Category', $categoryRepository),
        );

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap($repoMap));

        $attributesMap = array(
            array(array('code' => 'sku'), null, $this->getAttributeMock('sku', 'varchar')),
            array(array('code' => 'name'), null, $this->getAttributeMock('name', 'varchar')),
            array(array('code' => 'description'), null, $this->getAttributeMock('description', 'longtext')),
        );
        $attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValueMap($attributesMap));

        $categoriesMap = array(
            array(array('code' => 'cat_1'), null, $this->getCategoryMock(1)),
            array(array('code' => 'cat_2'), null, $this->getCategoryMock(2)),
            array(array('code' => 'cat_3'), null, $this->getCategoryMock(3)),
        );
        $categoryRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValueMap($categoriesMap));

        $product = $this->getProductMock();
        $this->productManager
            ->expects($this->any())
            ->method('createFlexible')
            ->will($this->returnValue($product));

        $form = $this->getFormMock();
        $this->formFactory
            ->expects($this->any())
            ->method('create')
            ->with('pim_product', $product, array('csrf_protection' => false, 'withCategories' => true))
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('submit')
            ->with(
                array(
                    'enabled' => '1',
                    'values' => array(
                        'sku' => array(
                            'varchar' => 'foo-1',
                        ),
                        'name' => array(
                            'varchar' => 'foo',
                        ),
                        'description' => array(
                            'longtext' => 'A foo product'
                        )
                    ),
                    'categories' => array(1, 2, 3),
                )
            );

        $this->assertNull($this->processor->process(array('sku', 'name', 'description', 'categories')));
        $this->assertEquals(
            $product,
            $this->processor->process(array('foo-1', 'foo', 'A foo product', 'cat_1,cat_2,cat_3'))
        );
    }

    protected function getFormFactoryMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getFormMock($valid = true)
    {
        $form = $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue($valid));

        return $form;
    }

    protected function getProductManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\ProductBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getEntityManagerMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getAttributeMock($code, $backendType)
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue($backendType));

        return $attribute;
    }

    protected function getProductMock()
    {
        return $this->getMock('Pim\Bundle\ProductBundle\Entity\Product');
    }

    protected function getCategoryMock($id)
    {
        $category = $this->getMock('Pim\Bundle\ProductBundle\Entity\Category');

        $category->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $category;
    }
}
