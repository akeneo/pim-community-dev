<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Converter;

use Pim\Bundle\ImportExportBundle\Converter\ProductValueConverter;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueConverterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $em = $this->getEntityManagerMock();
        $this->repository = $this->getRepositoryMock();
        $em->expects($this->any())
            ->method('getRepository')
            ->with('PimProductBundle:ProductAttribute')
            ->will($this->returnValue($this->repository));

        $this->converter = new ProductValueConverter($em);
    }

    public function testConvertBasicType()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'sku'))
            ->will($this->returnValue($this->getAttributeMock('varchar')));

        $this->assertEquals(
            array('sku' => array('varchar' => 'sku-001')),
            $this->converter->convert(array('sku' => 'sku-001'))
        );
    }

    public function testConvertLocalisedValue()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'name'))
            ->will($this->returnValue($this->getAttributeMock('varchar', true)));

        $this->assertEquals(
            array('name_en_US' => array('varchar' => 'car')),
            $this->converter->convert(array('name-en_US' => 'car'))
        );
    }

    protected function getAttributeMock($backendType, $translatable = false, $scopable = false)
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue($backendType));

        $attribute->expects($this->any())
            ->method('getScopable')
            ->will($this->returnValue($scopable));

        $attribute->expects($this->any())
            ->method('getTranslatable')
            ->will($this->returnValue($translatable));

        return $attribute;
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
}
