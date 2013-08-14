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
class ValidProductCreationProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->em             = $this->getEntityManagerMock();
        $this->formFactory    = $this->getFormFactoryMock();
        $this->productManager = $this->getProductManagerMock();
        $this->channelManager = $this->getChannelManagerMock();

        $this->processor = new ValidProductCreationProcessor(
            $this->em,
            $this->formFactory,
            $this->productManager,
            $this->channelManager
        );
    }

    public function testProcess()
    {
        $attributeRepository = $this->getRepositoryMock();
        $categoryRepository  = $this->getRepositoryMock();
        $familyRepository    = $this->getRepositoryMock();
        $repoMap = array(
            array('PimProductBundle:ProductAttribute', $attributeRepository),
            array('PimProductBundle:Category', $categoryRepository),
            array('PimProductBundle:Family', $familyRepository),
        );

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap($repoMap));

        $attributesMap = array(
            array(array('code' => 'sku'), null, $this->getAttributeMock('sku', 'varchar')),
            array(array('code' => 'name'), null, $this->getAttributeMock('name', 'varchar', true)),
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

        $familyRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($this->getFamilyMock(1)));

        $product = $this->getProductMock();
        $this->productManager
            ->expects($this->any())
            ->method('createFlexible')
            ->will($this->returnValue($product));

        $form = $this->getFormMock();
        $this->formFactory
            ->expects($this->any())
            ->method('create')
            ->with('pim_product', $product, array('csrf_protection' => false, 'import_mode' => true))
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
                        'name_en_US_phpunit' => array(
                            'varchar' => 'car',
                        ),
                        'name_fr_FR_phpunit' => array(
                            'varchar' => 'voiture',
                        ),
                        'description' => array(
                            'longtext' => 'A foo product'
                        )
                    ),
                    'categories' => array(1, 2, 3),
                    'family' => 1
                )
            );

        $this->processor->setChannel('phpunit');
        $this->assertEquals(
            $product,
            $this->processor->process(
                array(
                    'sku'         => 'foo-1',
                    'family'      => 'vehicle',
                    'name-en_US'  => 'car',
                    'name-fr_FR'  => 'voiture',
                    'description' => 'A foo product',
                    'categories'  => 'cat_1,cat_2,cat_3'
                )
            )
        );
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\InvalidObjectException
     */
    public function testInvalidProcess()
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
            array(array('code' => 'name'), null, $this->getAttributeMock('name', 'varchar', true)),
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

        $form = $this->getFormMock(false);
        $this->formFactory
            ->expects($this->any())
            ->method('create')
            ->with('pim_product', $product, array('csrf_protection' => false, 'import_mode' => true))
            ->will($this->returnValue($form));

        $this->processor->setChannel('phpunit');
        $this->processor->process(
            array(
                'sku'         => 'foo-1',
                'name-en_US'  => 'car',
                'name-fr_FR'  => 'voiture',
                'description' => 'A foo product',
                'categories'  => 'cat_1,cat_2,cat_3'
            )
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

        $form->expects($this->any())
            ->method('getErrors')
            ->will($this->returnValue(array()));

        return $form;
    }

    protected function getProductManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\ProductBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getChannelManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\ConfigBundle\Manager\ChannelManager')
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

    protected function getAttributeMock($code, $backendType, $scopable = false)
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue($backendType));

        $attribute->expects($this->any())
            ->method('getScopable')
            ->will($this->returnValue($scopable));

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

    protected function getFamilyMock($id)
    {
        $family = $this->getMock('Pim\Bundle\ProductBundle\Entity\Family');

        $family->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $family;
    }
}
