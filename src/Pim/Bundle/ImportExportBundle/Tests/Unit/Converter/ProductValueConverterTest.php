<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Converter;

use Pim\Bundle\ImportExportBundle\Converter\ProductValueConverter;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $em = $this->getEntityManagerMock();
        $this->attributeRepository = $this->getRepositoryMock();
        $this->optionRepository = $this->getRepositoryMock();
        $em->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    array(
                        array('PimCatalogBundle:ProductAttribute', $this->attributeRepository),
                        array('PimCatalogBundle:AttributeOption', $this->optionRepository),
                    )
                )
            );
        $currencyManager = $this->getCurrencyManagerMock();

        $this->converter = new ProductValueConverter($em, $currencyManager);
    }

    /**
     * Test related method
     */
    public function testConvertBasicType()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'sku'))
            ->will($this->returnValue($this->getAttributeMock('varchar')));

        $this->assertEquals(
            array('values' => array('sku' => array('varchar' => 'sku-001'))),
            $this->converter->convert(array('sku' => 'sku-001'))
        );
    }

    /**
     * Test related method
     */
    public function testIgnoreUnknownAttribute()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'foo'))
            ->will($this->returnValue(null));

        $this->assertEquals(
            array(),
            $this->converter->convert(array('foo' => 'bar'))
        );
    }

    /**
     * Test related method
     */
    public function testConvertLocalizedValue()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'name'))
            ->will($this->returnValue($this->getAttributeMock('varchar', true)));

        $this->assertEquals(
            array('values' => array('name_en_US' => array('varchar' => 'car'))),
            $this->converter->convert(array('name-en_US' => 'car'))
        );
    }

    /**
     * Test related method
     */
    public function testConvertUnlocalizedValue()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'name'))
            ->will($this->returnValue($this->getAttributeMock('varchar', false)));

        $this->assertEquals(
            array('values' => array('name' => array('varchar' => 'car'))),
            $this->converter->convert(array('name-en_US' => 'car'))
        );
    }

    /**
     * Test related method
     */
    public function testConvertScopableValue()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'description'))
            ->will($this->returnValue($this->getAttributeMock('varchar', false, true)));

        $this->assertEquals(
            array('values' => array('description_ecommerce' => array('varchar' => 'an awesome vehicle'))),
            $this->converter->convert(array('description' => 'an awesome vehicle', '[scope]' => 'ecommerce'))
        );
    }

    public static function getConvertedPricesValue()
    {
        return array(
            array(
                '99.90 EUR,59.90 USD',
                array(
                    array('data' => '99.90', 'currency' => 'EUR'),
                    array('data' => '59.90', 'currency' => 'USD')
                )
            ),
            array(
                '99.90 EUR, 59.90 USD',
                array(
                    array('data' => '99.90', 'currency' => 'EUR'),
                    array('data' => '59.90', 'currency' => 'USD')
                )
            ),
            array(
                '50 EUR',
                array(
                    array('data' => '50.00', 'currency' => 'EUR'),
                    array('data' => '', 'currency' => 'USD')
                )
            ),
            array(
                '50 EUR, USD',
                array(
                    array('data' => '50.00', 'currency' => 'EUR'),
                    array('data' => '', 'currency' => 'USD')
                )
            ),
        );
    }

    /**
     * @dataProvider getConvertedPricesValue
     */
    public function testConvertPricesValue($data, $prices)
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'public_prices'))
            ->will($this->returnValue($this->getAttributeMock('prices')));

        $this->assertEquals(
            array(
                'values' => array(
                    'public_prices' => array(
                        'prices' => $prices
                    )
                )
            ),
            $this->converter->convert(array('public_prices' => $data))
        );
    }

    /**
     * Test related method
     */
    public function testConvertEmptyPricesValue()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'public_prices'))
            ->will($this->returnValue($this->getAttributeMock('prices')));

        $this->assertEquals(
            array(
                'values' => array(
                    'public_prices' => array(
                        'prices' => array(
                            array(
                                'data'     => '',
                                'currency' => 'EUR',
                            ),
                            array(
                                'data'     => '',
                                'currency' => 'USD',
                            )
                        ),
                    )
                )
            ),
            $this->converter->convert(array('public_prices' => ''))
        );
    }

    /**
     * Test related method
     */
    public function testConvertDateValue()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'release_date'))
            ->will($this->returnValue($this->getAttributeMock('date')));

        $today = new \DateTime();
        $this->assertEquals(
            array(
                'values' => array(
                    'release_date' => array(
                        'date' => $today->format('m/d/Y')
                    )
                )
            ),
            $this->converter->convert(array('release_date' => $today->format('r')))
        );
    }

    /**
     * Test related method
     */
    public function testConvertOptionValue()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'size'))
            ->will($this->returnValue($this->getAttributeMock('option')));

        $this->optionRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'XS'))
            ->will($this->returnValue($this->getAttributeOptionMock(42)));

        $this->assertEquals(
            array(
                'values' => array(
                    'size' => array(
                        'option' => 42
                    )
                )
            ),
            $this->converter->convert(array('size' => 'XS'))
        );
    }

    /**
     * Test related method
     */
    public function testConvertOptionsValue()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'colors'))
            ->will($this->returnValue($this->getAttributeMock('options')));

        $this->optionRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will(
                $this->returnValueMap(
                    array(
                        array(array('code' => 'red'), null, $this->getAttributeOptionMock(4)),
                        array(array('code' => 'green'), null, $this->getAttributeOptionMock(8)),
                        array(array('code' => 'blue'), null, $this->getAttributeOptionMock(15)),
                    )
                )
            );

        $this->assertEquals(
            array(
                'values' => array(
                    'colors' => array(
                        'options' => array(4, 8, 15)
                    )
                )
            ),
            $this->converter->convert(array('colors' => 'red,green,blue'))
        );
    }

    /**
     * Test related method
     */
    public function testConvertMetricValue()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'weight'))
            ->will($this->returnValue($this->getAttributeMock('metric')));

        $this->assertEquals(
            array(
                'values' => array(
                    'weight' => array(
                        'metric' => array(
                            'data' => '60',
                            'unit' => 'KILOGRAM',
                        ),
                    )
                )
            ),
            $this->converter->convert(array('weight' => '60 KILOGRAM'))
        );
    }

    /**
     * Test related method
     */
    public function testConvertEmptyMetricValue()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'weight'))
            ->will($this->returnValue($this->getAttributeMock('metric')));

        $this->assertEquals(
            array(
                'values' => array(
                    'weight' => array(
                        'metric' => array(),
                    )
                )
            ),
            $this->converter->convert(array('weight' => ''))
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConvertMalformedMetric()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'weight'))
            ->will($this->returnValue($this->getAttributeMock('metric')));

        $this->converter->convert(array('weight' => '60KILOGRAM'));
    }

    /**
     * Test related method
     */
    public function testConvertMedia()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'image'))
            ->will($this->returnValue($this->getAttributeMock('media')));

        $this->assertEquals(array(), $this->converter->convert(array('image' => 'akeneo.jpg')));
    }

    /**
     * @param string  $backendType
     * @param boolean $translatable
     * @param boolean $scopable
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute
     */
    protected function getAttributeMock($backendType, $translatable = false, $scopable = false)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

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

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManagerMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\CurrencyManager
     */
    protected function getCurrencyManagerMock()
    {
        $currencyManager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\CurrencyManager')
            ->disableOriginalConstructor()
            ->getMock();

        $currencyManager
            ->expects($this->any())
            ->method('getActiveCodes')
            ->will($this->returnValue(array('EUR', 'USD')));

        return $currencyManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param integer $id
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeOption
     */
    protected function getAttributeOptionMock($id)
    {
        $option = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\AttributeOption')
            ->disableOriginalConstructor()
            ->getMock();

        $option->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $option;
    }
}
