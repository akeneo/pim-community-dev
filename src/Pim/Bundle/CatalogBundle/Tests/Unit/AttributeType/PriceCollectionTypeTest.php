<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\AttributeType\PriceCollectionType;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_price_collection';
    protected $backendType = AbstractAttributeType::BACKEND_TYPE_DECIMAL;
    protected $formType = 'text';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        $currencyManager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\CurrencyManager')
            ->disableOriginalConstructor()->getMock();

        return new PriceCollectionType($this->backendType, $this->formType, $this->guesser, $currencyManager);
    }

    /**
     * Test related method
     */
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
                array(
                    'constraints'      => array('constraints'),
                    'label'        => null,
                    'required'     => null,
                    'type'         => 'pim_enrich_price',
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'auto_initialize' => false
                )
            );

        $this->target->buildValueFormType($factory, $value);
    }

    /**
     * Test related method
     */
    public function testBuildAttributeFormTypes()
    {
        $this->assertCount(
            8,
            $this->target->buildAttributeFormTypes(
                $this->getFormFactoryMock(),
                $this->getAttributeMock(null, null)
            )
        );
    }
}
