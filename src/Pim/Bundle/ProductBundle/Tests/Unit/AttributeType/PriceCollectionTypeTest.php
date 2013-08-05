<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\AttributeType;

use Pim\Bundle\ProductBundle\AttributeType\PriceCollectionType;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionTypeTest extends AttributeTypeTest
{
    protected $name = 'pim_product_price_collection';

    public function setUp()
    {
        parent::setUp();

        $currencyManager = $this->getMockBuilder('Pim\Bundle\ConfigBundle\Manager\CurrencyManager')
            ->disableOriginalConstructor()->getMock();
        $this->target = new PriceCollectionType('decimal', 'text', $this->guesser, $currencyManager);
    }

    public function testBuildValueFormType()
    {
        $factory = $this->getFormFactoryMock();
        $value = $this->getFlexibleValueMock(
            array(
                'data'        => 'bar',
                'backendType' => 'foo'
            )
        );

        $factory->expects($this->once())
            ->method('createNamed')
            ->with(
                'foo',
                'text',
                'bar',
                array('options'      => array('constraints' => array('constraints'),),
                'label'        => null,
                'required'     => null,
                'type'         => 'pim_product_price',
                'allow_add'    => false,
                'allow_delete' => false,
                'by_reference' => false,
                'auto_initialize' => false
                )
            );

        $this->target->buildValueFormType($factory, $value);
    }

    public function testGetBackendType()
    {
        $this->assertEquals('decimal', $this->target->getBackendType());
    }

    public function testGetFormType()
    {
        $this->assertEquals('text', $this->target->getFormType());
    }

    public function testBuildAttributeFormTypes()
    {
        $this->assertEquals(
            9,
            count(
                $this->target->buildAttributeFormTypes(
                    $this->getFormFactoryMock(),
                    $this->getAttributeMock(null, null)
                )
            )
        );
    }
}
