<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\ValidProductCreationProcessor;
use Pim\Bundle\ImportExportBundle\Converter\ProductEnabledConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductValueConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductFamilyConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductCategoriesConverter;

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
        $this->formFactory    = $this->getFormFactoryMock();
        $this->productManager = $this->getProductManagerMock();
        $this->channelManager = $this->getChannelManagerMock();

        $this->processor = new ValidProductCreationProcessor(
            $this->formFactory,
            $this->productManager,
            $this->channelManager
        );
    }

    public function testProcess()
    {
        $product = $this->getProductMock();
        $this->productManager
            ->expects($this->any())
            ->method('createFlexible')
            ->will($this->returnValue($product));

        $form = $this->getFormMock(true);
        $this->formFactory
            ->expects($this->any())
            ->method('create')
            ->with('pim_product', $product, array('csrf_protection' => false, 'import_mode' => true))
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('submit')
            ->with(
                array(
                    ProductEnabledConverter::ENABLED_KEY       => true,
                    ProductValueConverter::SCOPE_KEY           => 'ecommerce',
                    ProductFamilyConverter::FAMILY_KEY         => 'vehicle',
                    ProductCategoriesConverter::CATEGORIES_KEY => 'cat_1,cat_2,cat_3',
                    'sku'                                      => 'foo-1',
                    'name-en_US'                               => 'car',
                    'name-fr_FR'                               => 'voiture',
                    'description'                              => 'A foo product',
                )
            );

        $this->processor->setChannel('ecommerce');
        $this->processor->setEnabled(true);
        $this->processor->setFamilyColumn('family');
        $this->processor->setCategoriesColumn('categories');

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

        $this->processor->process(array());
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
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getChannelManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
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
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

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
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product');
    }

    protected function getCategoryMock($id)
    {
        $category = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Category');

        $category->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $category;
    }

    protected function getFamilyMock($id)
    {
        $family = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family');

        $family->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $family;
    }
}
