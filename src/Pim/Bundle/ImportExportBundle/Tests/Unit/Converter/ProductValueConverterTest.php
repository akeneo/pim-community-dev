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

    public function testIgnoreUnknownAttribute()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'foo'))
            ->will($this->returnValue(null));

        $this->assertEquals(
            array(),
            $this->converter->convert(array('foo' => 'bar'))
        );
    }

    public function testConvertLocalizedValue()
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

    public function testConvertUnlocalizedValue()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'name'))
            ->will($this->returnValue($this->getAttributeMock('varchar', false)));

        $this->assertEquals(
            array('name' => array('varchar' => 'car')),
            $this->converter->convert(array('name-en_US' => 'car'))
        );
    }

    public function testConvertScopableValue()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'description'))
            ->will($this->returnValue($this->getAttributeMock('varchar', false, true)));

        $this->assertEquals(
            array('description_ecommerce' => array('varchar' => 'an awesome vehicle')),
            $this->converter->convert(array('description' => 'an awesome vehicle'), array('scope' => 'ecommerce'))
        );
    }

    public function testConvertPricesValue()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'public_prices'))
            ->will($this->returnValue($this->getAttributeMock('prices')));

        $this->assertEquals(
            array(
                'public_prices' => array(
                    'prices' => array(
                        array(
                            'data'     => '99.90',
                            'currency' => 'EUR',
                        ),
                        array(
                            'data'     => '59.90',
                            'currency' => 'USD',
                        )
                    ),
                )
            ),
            $this->converter->convert(array('public_prices' => '99.90 EUR,59.90 USD'))
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
