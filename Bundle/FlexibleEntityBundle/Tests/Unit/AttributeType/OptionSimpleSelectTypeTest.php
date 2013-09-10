<?php

namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\OptionSimpleSelectType;

class OptionSimpleSelectTypeTest extends AttributeTypeTest
{
    protected $name = 'oro_flexibleentity_simpleselect';

    public function setUp()
    {
        parent::setUp();

        $this->target = new OptionSimpleSelectType('text', 'email', $this->guesser);
    }

    public function testBuildValueFormType()
    {
        $factory = $this->getFormFactoryMock();
        $value = $this->getFlexibleValueMock(
            array(
                'data'        => 'bar',
                'backendType' => 'foo',
            )
        );

        $factory->expects($this->once())
            ->method('createNamed')
            ->with(
                'foo',
                'email',
                'bar',
                array_merge(
                    $this->defaultCreateNamedOptions,
                    array(
                        'empty_value' => false,
                        'class'       => 'OroFlexibleEntityBundle:AttributeOption',
                        'expanded'    => false,
                        'multiple'    => false,
                        'query_builder' => function () {
                        }
                    )
                )
            );

        $this->target->buildValueFormType($factory, $value);
    }

    public function testGetBackendType()
    {
        $this->assertEquals('text', $this->target->getBackendType());
    }

    public function testGetFormType()
    {
        $this->assertEquals('email', $this->target->getFormType());
    }

    public function testBuildAttributeFormTypes()
    {
        $this->assertEquals(
            array(),
            $this->target->buildAttributeFormTypes(
                $this->getFormFactoryMock(),
                $this->getAttributeMock(null, null)
            )
        );
    }
}
